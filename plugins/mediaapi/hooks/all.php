<?php
include_once dirname(dirname(__FILE__)) . '/stdlib.php';

/**
 * Hook to group all resources
 * @param string $ref Resource reference ID
 */
function HookMediaapiAllAfternewresource($ref)
{
    mediaapi_session_start();
    $reset_group_resources = true;

    if (!empty($_SESSION['mediaapi_groupinsert_session'])) {
        // figure if we should reset the group insert session
        if (!empty($_SESSION['mediaapi_newresource_groupinsert_session'])
            && ($_SESSION['mediaapi_newresource_groupinsert_session'] === $_SESSION['mediaapi_groupinsert_session'])
        ) {
            $reset_group_resources = false;
        }

        $_SESSION['mediaapi_newresource_groupinsert_session'] = $_SESSION['mediaapi_groupinsert_session'];

        if ($reset_group_resources === true) {
            $_SESSION['mediaapi_session_group_resources'] = array();
    	}

        // populate the resources
        $_SESSION['mediaapi_session_group_resources'][] = (int) $ref;

        // do the insert on the last resource que
        if (getval("lastqueued","")) {
            $group_resources = array_unique($_SESSION['mediaapi_session_group_resources']);
            sql_query('DELETE FROM resource_related WHERE resource IN (' . implode(', ', $group_resources) . ')');
            foreach ($group_resources as $resource) {
                // remove the $resource instance from the db insert
                $to_insert_resources = array_filter($group_resources, function ($val) use ($resource) {
                    return ($val !== $resource);
                });
                if (!empty($to_insert_resources)) {
                    sql_query("insert into resource_related(resource, related) values ($resource," . implode("), (" . $resource . ",", $to_insert_resources) . ")");
                }
            }
    	}
    }
}

function HookMediaapiAllUploadfilesuccess($ref)
{

    //var_dump(get_resource_files($ref));die;
    //todo: do an actual hard copy of resource files from above
    //$path=get_resource_path($resource, true, "", true, $extension, -1, 1, false, "", $ref);

    $ref     = copy_resource($ref);
    $plfilename = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
    $newfile = add_alternative_file($ref, $plfilename);
    save_alternative_file($ref, $newfile);
}
