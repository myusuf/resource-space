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
    if ($uuid_field_id = sql_value('SELECT ref AS value FROM resource_type_field WHERE name = "uuid"', null)) {
        $collection = getval("collection_add", null);
        $uuid       = getval("field_{$uuid_field_id}", null);
        if (null !== $collection && null !== $uuid) {
            $existing_resources = sql_query(
                'SELECT rd.resource FROM resource_data rd, collection_resource cr
                 WHERE
                    (rd.resource = cr.resource) AND
                    cr.collection = ' . $collection . ' AND
                    rd.resource_type_field = ' . $uuid_field_id . ' AND
                    rd.value = "' . $uuid . '"', 1
            );

            if (!empty($existing_resources)) {
                foreach ($existing_resources as $res) {
                    $_SESSION['mediaapi_session_group_resources'][] = (int) $res['resource'];
                }

                $_SESSION['mediaapi_session_group_resources'] = array_unique($_SESSION['mediaapi_session_group_resources']);
            }
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

function HookMediaapiEditReplacesubmitbuttons()
{
    global $multiple, $lang, $ref;

	echo '<div class="QuestionSubmit">';
	echo '<input name="resetform" type="submit" value="' . $lang['clearbutton'] . '" />&nbsp;';
    echo '<input ' . (($multiple) ? 'onclick="return confirm(\'' . $lang["confirmeditall"] .'\');' : "") . 'name="save" type="submit" value="&nbsp;&nbsp;' . (($ref > 0) ? $lang["save"] : $lang["next"]) . '&nbsp;&nbsp;" /><br><br>';
    echo '<div class="clearerleft"> </div>';
	echo '</div>';

	echo '<div class="QuestionSubmit">';
	echo 'Publish/Synchronize with LOC Media Resource:<br /><br />';
	echo '<input name="publish" type="submit" value="Publish" onclick="return confirm(\'Are you sure you want to push the changes to the media resource?\')" />&nbsp;';
    echo '<div class="clearerleft"> </div>';
    echo '</div>';

	return true;
}