<?php
$mediaroot = dirname(dirname(__FILE__));
include_once $mediaroot . '/stdlib.php';

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
 * table by collecting data from http global vars in php.
 *
 * @param string $alt_ref
 * @return null
 */
function HookMediaapiAllPost_savealternativefile($alt_ref, $parent_resource)
{
    $mediaapi_derivatives = mediaapi_get_derivative_resources($alt_ref);
    if (empty($mediaapi_derivatives)) {
        $ordinal = mediaapi_get_max_ordinal($parent_resource);
        mediaapi_insert_derivative_data($parent_resource, $alt_ref, $ordinal);
    } else {
        mediaapi_upsert_derivative_resources($alt_ref, mediaapi_collect_derivative_data());
    }
}

/**
 * This hooks will map the naming of the mediaapi fields to camelcased
 * and preserve it.
 *
 * @param array $fields
 * @param array $field
 * @return boolean
 */
function HookMediaapiAllAdditionalvalcheck($fields, $field)
{
    global $media_resource;

    $mediaapi_fields = array(
        'uuid'      => 'uuid',
        'shortName' => 'shortname',
        'longName'  => 'longname',
        'shortDescription' => 'shortdescription',
        'longDescription'  => 'longdescription',
        'siteId'    => 'siteid',
        'detailUrl' => 'detailurl',
        'externalId' => 'externalid',
        'mediaType'  => 'mediatype',
        'thumbnailUrl'  => 'thumbnailurl',
        'backgroundUrl' => 'backgroundurl',
        'ccUrl'    => 'ccurl',
        'duration' => 'duration',
        'language' => 'language',
        'aspectRatio' => 'aspectratio',
        'canEmbed' => 'canembed',
        'canDownload' => 'candownload',
        'isPublished' => 'ispublished',
        'contributorId' => 'contributorid',
    );

    if (($media_resource_key = array_search($field['name'], $mediaapi_fields)) !== false) {
        $media_resource[$media_resource_key] = $field['value'];
    }

    return false;
}
