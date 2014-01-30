<?php
$mediaroot = dirname(dirname(__FILE__));
include_once $mediaroot . '/stdlib.php';

/**
 * Delete the resource from related resources on collection remove
 *
 * @param string $resource
 * @param string $collection_id
 */
function HookMediaapiCollectionsRemovefromcollectionsuccess($resource, $collection_id)
{
    $collection_resources = sql_array('SELECT resource AS value FROM collection_resource WHERE collection="' . $collection_id . '"');

    // delete the target resource from attached collection
    sql_query('DELETE FROM resource_related WHERE resource="' . $resource . '" AND related IN (' . implode(', ', $collection_resources) . ')');

    //loop through each of the related collection and delete the target resource
    foreach ($collection_resources as $cres) {
       if ($cres !== $resource) {
           sql_query("DELETE FROM resource_related WHERE resource={$cres} AND related={$resource}");
       }
    }
}
