<?php
/**
 * 
 * Resource Space Import
 * ...
 * 
 */
include "../include/rspaceapi.php";
$file="{$IMGHOME}/{$argv[1]}";
  
/**
 * 
 * Create Resrouce 
 * arg1=image type 1
 * arg2=active status
 * arg3=user #
 * 
 */
$ref=create_resource(1,0,1);

/**
 * 
 * Add Keyword Mapping 
 * ...
 * 
 */
#add_keyword_mapping($refid,"{$argv[2]}","jpeg");
/**
 * 
 * Add extension in mysql 
 * ...
 * 
 */

/**
 * 
 * Get Resource Path 
 * ...
 * 
 */
$path=get_resource_path($ref, true, "", true,"jpeg");
/**
 * 
 * Copy Image File to Resource Space Filestore 
 * ...
 * 
 */
copy($file,$path);
/**
 * 
 * Extract data from imagefile with EXIF 
 * ...
 * 
 */
extract_exif_comment($ref,"jpeg");
/**
 * 
 * Create Previews 
 * ...
 * 
 */
create_previews($ref,false,"jpeg"); 


?>
