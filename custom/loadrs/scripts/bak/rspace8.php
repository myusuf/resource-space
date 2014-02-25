<?php
$RSPACE_HOME="/var/www/resourcespace";
$RSPACE_CONFIG="{$RSPACE_HOME}/include";
$IMGHOME="/home/aben/images";
include "{$RSPACE_CONFIG}/db.php";
include "{$RSPACE_CONFIG}/general.php";
include "{$RSPACE_CONFIG}/resource_functions.php";
include "{$RSPACE_CONFIG}/image_processing.php";
//get your file, whatever it is
$file="{$IMGHOME}/{$argv[1]}";
//create a resource (image type 1, active status, user #1)
$ref=create_resource(1,0,1);
//get the path for the original file (ref, get actual path, original
//file, generate folders, extension)
#add_keyword_mapping($refid,"{$argv[2]}","jpeg");


$path=get_resource_path($ref, true, "", true,"jpeg");
// move it to filestore
copy($file,$path);
// extract metadata
$exout = extract_exif_comment($ref,"jpeg");
print_r($exout);
sleep(15);
//create previews
create_previews($ref,false,"jpeg"); 




?>
