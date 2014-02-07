<?php
include_once dirname(dirname(__FILE__)) . '/stdlib.php';

/**
 * This hook will seed the mediaapi session data
 * and prep it for upload of new resources. The hook will
 * enable RS to have the ability for having resource uploads as
 * related resources since the actuall upload process is done in a
 * separate ajax instance hence losing the ability to create related resources.
 *
 * @param  void
 * @return null
 */
function HookMediaapiEditEditbeforesave()
{
    $save = getval("save", null);
    if (null === $save) {
        return;
    }

    mediaapi_session_start();

    $_SESSION['mediaapi_session_group_resources'] = array();

    // prepopulate the group resources with existing resources
    $collection = getval("collection_add", null);

    if (null !== $collection) {
        $existing_resources = sql_query(
            "SELECT DISTINCT rd.resource FROM resource_data rd, collection_resource cr
             WHERE
                (rd.resource = cr.resource) AND
                cr.collection = {$collection}", 1
        );

        if (!empty($existing_resources)) {
            foreach ($existing_resources as $res) {
                $_SESSION['mediaapi_session_group_resources'][] = (int) $res['resource'];
            }

            $_SESSION['mediaapi_session_group_resources'] = array_unique($_SESSION['mediaapi_session_group_resources']);
        }
    }

    $uniqid = uniqid();
    if (!empty($_SESSION['mediaapi_session_group_resources'])) {
        $_SESSION['mediaapi_groupinsert_session']             = $uniqid;
        $_SESSION['mediaapi_newresource_groupinsert_session'] = $uniqid;
    } else {
        $_SESSION['mediaapi_groupinsert_session']             = $uniqid;
        $_SESSION['mediaapi_newresource_groupinsert_session'] = null;
    }
}

/**
 * Create a publish button on existing resources
 *
 * @return boolean
 */
function HookMediaapiEditReplacesubmitbuttons()
{
    global $multiple, $lang, $ref;

	echo '<div class="QuestionSubmit">';
	echo '<input name="resetform" type="submit" value="' . $lang['clearbutton'] . '" />&nbsp;';
    echo '<input ' . (($multiple) ? 'onclick="return confirm(\'' . $lang["confirmeditall"] .'\');' : "") . 'name="save" type="submit" value="&nbsp;&nbsp;' . (($ref > 0) ? $lang["save"] : $lang["next"]) . '&nbsp;&nbsp;" /><br><br>';
    echo '<div class="clearerleft"> </div>';
	echo '</div>';

	if (getval('ref', null) !== '-1') {
        echo '<div class="QuestionSubmit">';
    	echo 'Publish/Synchronize with LOC Media Resource:<br /><br />';
    	echo '<input name="publish" type="submit" value="Publish" onclick="return confirm(\'Are you sure you want to push the changes to the media resource?\')" />&nbsp;';
        echo '<div class="clearerleft"> </div>';
        echo '</div>';
	}

	return true;
}

/**
 * Prepares the resource to be pushed to mediaapi
 */
function HookMediaapiEditAftersaveresourcedata()
{
    global $media_resource;

    if (empty($media_resource)) {
        return;
    }

    if (getval('publish', null)) {
        $resource_ref = getval("ref", null);

        // filter the resource. apply some rule on some fields
        if (!empty($media_resource)) {
            array_walk($media_resource, function (&$item, $key) {
                switch ($key) {
                    case 'canEmbed':
                    case 'canDownload':
                    case 'isPublished':
                        $item = strtolower(trim($item, ', '));
                        if ($item === 'yes' || $item === 'y') {
                            $item = 'Y';
                        } elseif ($item === 'no' || $item === 'n') {
                            $item = 'N';
                        }
                        return;
                }
            });
            if (empty($media_resource['uuid'])) {
                unset($media_resource['uuid']);
            }
        }

        // get the derivatives
        $derivatives = array();
        foreach (get_alternative_files($resource_ref) as $derivative) {
            $derivative = mediaapi_get_derivative_resources((int) $derivative['ref']);
            if (!empty($derivative)) {
                $derivatives[] = array_pop($derivative);
            }
        }

        // loop through each valid derivative keys and apply the camelcase filter
        $valid_keys = array();
        $derivatives_2 = array();
        $derivatives_for_create = array();
        foreach ($derivatives as $key => $derivative) {
            foreach ($derivative as $derivative_key => $derivative_val) {
                if ($derivative_key !== 'alt_file_id') {
                    if ('use_extension' === $derivative_key
                        || 'is_downloadable' === $derivative_key
                        || 'is_streamable' === $derivative_key
                        || 'is_primary' === $derivative_key
                    ) {
                        $derivative_val = strtoupper($derivative_val);
                    }
                    $derivatives_2[$key][mediaapi_filter_underscoretocamelcase($derivative_key)] = $derivative_val;
                }
            }

            // also remove derivatives with derivativeId. This needs to be in a separate update request
            if (!empty($media_resource['uuid']) && empty($derivatives_2[$key]['derivativeId'])) {
                unset($derivatives_2[$key]['derivativeId']);
                $derivatives_for_create[] = $derivatives_2[$key];
                unset($derivatives_2[$key]);
            }
        }
        $derivatives = $derivatives_2;
        unset($derivatives_2);

        // get the access token
        $access_token = mediaapi_get_accesstoken();

        // add the derivative to the resource
        if (!empty($derivatives)) {
            $media_resource['derivatives'] = $derivatives;
        }

        if (!empty($media_resource['uuid'])) {
            $response = mediaapi_update_media($media_resource['uuid'], $media_resource, $access_token);
            if (isset($response->mediaObjectId)) {
                foreach ($derivatives_for_create as $derivative) {
                    $derivative['mediaObjectId'] = $response->mediaObjectId;
                    $response->derivatives[] = mediaapi_create_media($derivative, $access_token, true);
                }
            }
        } else {
            $response = mediaapi_create_media($media_resource, $access_token);
        }

        // actual save in the local db
        if (is_object($response) && isset($response->uuid)) {
            // inject the new uuid
            if (empty($media_resource['uuid'])) {
                $uuid_field_id = sql_value('SELECT ref AS value FROM resource_type_field WHERE name = "uuid"', null);
                sql_query('UPDATE resource_data SET value="' . $response->uuid . '" WHERE resource="' . $resource_ref . '" AND resource_type_field="' . $uuid_field_id . '"');
            }

            // inject the new derivatives
            if (!empty($response->derivatives)) {
                foreach ($response->derivatives as $derivative) {
                    sql_query('
                        UPDATE mediaapi_derivatives
                        SET
                            derivative_id ="' . $derivative->derivativeId . '",
                            derivative_url="' . $derivative->derivativeUrl . '",
                            last_published="' . gmdate('Y-m-d H:i:s') . '"' .
                            (isset($derivative->mediaObjectId) ? ', media_object_id="' . $derivative->mediaObjectId . '"' : '') .
                        'WHERE file_name="' . $derivative->fileName . '"'
                    );
                }
            }
        }
    }
}