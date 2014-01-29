<?php
$mediaroot = dirname(dirname(__FILE__));
include_once $mediaroot . '/stdlib.php';

/**
 * This hook will create the current session upload of resources
 * as a group of related resources automatically.
 *
 * @param string $ref Resource reference ID
 * @return null
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

/**
 * This hook will automatically create the first uploaded resource
 * as an alternative/derivative file in RS. In addition, it will also
 * populate the derivative information automatically based on mediaapi's
 * derivative requirement.
 *
 * @param string $ref
 * @return null
 */
function HookMediaapiAllUploadfilesuccess($ref)
{
    global $storagedir;
    //var_dump(get_resource_files($ref));die;
    $res_data = get_resource_data($ref);
    $res_path = get_resource_path($ref, true, "", false, $res_data['file_extension'], -1, 1, false, "");

    $plfilename = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
    //$copied_res       = copy_resource($copied_res);
    $new_alt_resource = add_alternative_file($ref, $plfilename);

    # Find the path for this resource.
    $alt_file_path = get_resource_path($ref, true, "", true, $res_data['file_extension'], -1, 1, false, "", $new_alt_resource);

    $result = copy($res_path, $alt_file_path);
    $sql = '';
	if ($result==false) {
		exit("File upload error. Please check the size of the file you are trying to upload.");
	} else {
		chmod($alt_file_path, 0777);
		$file_size = @filesize_unlimited($alt_file_path);
		$sql.=",file_name='" . escape_check($plfilename) . "',file_extension='" . escape_check($res_data['file_extension']) . "',file_size='" . $file_size . "',creation_date=now()";
	}

	create_previews($ref, false, $res_data['file_extension'], false, false, $new_alt_resource);

    //resource_log($resource,"b","",$ref . ": " . getvalescaped("name","") . ", " . getvalescaped("description","") . ", " . escape_check($filename));
	# Save data back to the database.
	sql_query("
	   update resource_alt_files set
	   alt_type='".$res_data['resource_type']."' $sql
	   where resource='$ref' and ref='$new_alt_resource'
	");

	# Update disk usage
	update_disk_usage($new_alt_resource);

    // insert the derivative data
    mediaapi_insert_derivative_data($ref, $new_alt_resource);
}

/**
 * This hook will save the alternative metadata to the mediaapi_derivatives
 * table by collecting data to from http global vars in php.
 *
 * @param string $ref
 * @return null
 */
function HookMediaapiAllPost_savealternativefile($ref)
{
    mediaapi_upsert_derivative_resources($ref, mediaapi_collect_derivative_data());
}
