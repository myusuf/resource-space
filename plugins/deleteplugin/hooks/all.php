<?php



/**
 * This hook will will make sure when resource is deleted, derivative metadata is also deleted
 *
 * @param none
 * @return null
 */
function HookDeletePluginAllBeforedeltealternatefiles()  
{   
    $resource = getval('ref', null);
    $alternatives=get_alternative_files($resource);
    foreach ($alternatives as $alt) {
        $ref = $alt["ref"];
        sql_query("delete FROM mediaapi_derivatives WHERE alt_file_id='" . $ref . "'");
   }
}
