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

	if (getval('ref', null) !== '-1' && mediaapi_is_resource_published($ref) === false) {
        echo '<div class="QuestionSubmit">';
    	echo 'Send resource to LOC Mediaapi:<br /><br />';
    	echo '<input name="publish" type="submit" value="Send Now!" onclick="return confirm(\'Are you sure you want to push the changes to the media resource database?\')" />&nbsp;';
        echo '<div class="clearerleft"> </div>';
        echo '</div>';
	}

	return true;
}

/**
 * Prepares the resource to be pushed to mediaapi
 * @param void
 * @return null
 */
function HookMediaapiEditAftersaveresourcedata()
{
    global $media_resource, $response;

    if (empty($media_resource)) {
        return;
    }

    if (getval('publish', null)) {
        $resource_ref = getval("ref", null);
        $response = mediaapi_publish_resource($resource_ref, $media_resource);
    }
}

/**
 * Attaches the error retrieved from the mediaapi to the global errors
 * @param void
 * @return null
 */
function HookMediaapiEditAddfieldextras()
{
    global $errors, $response;

    $field_id_mappings = array(
        'uuid'             => 77,
        'shortName'        => 78,
        'longName'         => 79,
        'shortDescription' => 80,
        'longDescription'  => 81,
        'siteId'           => 82,
        'detailUrl'        => 83,
        'externalId'       => 84,
        'mediaType'        => 85,
        'thumbnailUrl'     => 86,
        'backgroundUrl'    => 87,
        'ccUrl'            => 88,
        'duration'         => 89,
        'language'         => 90,
        'aspectRatio'      => 91,
        'canEmbed'         => 92,
        'canDownload'      => 93,
        'isPublished'      => 94,
        'contributorId'    => 95,
    );

    if (!empty($response['Details'])) {
        $errorDetails = (array) $response['Details'];
        foreach ($errorDetails as $errorKey => $errorDetail) {
            if (isset($field_id_mappings[$errorKey])) {
                $errors[$field_id_mappings[$errorKey]] = implode('; ', (array) $errorDetail);
            } elseif (isset($errorKey)) {
                // just append to uuid
                $error = "{$response['Error']}: " . implode('; ', (array) $errorDetail);
                $errors[$field_id_mappings['uuid']] = $error;
            }
        }
    }
}

/**
 * This hook will update the resource table's last_update column
 * @return boolean
 */
function HookMediaapiEditRedirectaftersave()
{
    $ref = getval('ref', null);
    if ($ref !== null) {
        sql_query('UPDATE resource SET last_mediaapi_updated="' . gmdate('Y-m-d H:i:s') . '" WHERE ref="' . $ref . '"');
    }

    return false; // explicitly return false to redirect
}
