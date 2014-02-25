<?php 
//
include("rsid_uuid_xref.php");
foreach ( $rsid_uuid_xref as $key => $val ) {
  //if CC file
  //get related resources.
  //check if root is related.
  //if not add.
  if ( substr($key,-3) == '-CC' ) {
    echo "FILE should be assocaited with main resource.  {$key}\n";
    $related_resources = get_related_resources($val);
    $related_resource = substr($key,0,-3);
    //
    if ( isset($rsid_uuid_xref[$related_resource]) ) {
      $relid = $rsid_uuid_xref[$related_resource];
      if ( is_numeric( $relid ) ) {
         if ( in_array($relid,$related_resources ) ) {
           //no need to add
         } else {
           array_push($related_resources,$relid);
           relate_to_array($val,$related_resources);
         }
      }
    }
  } else {
    //echo "NOT APPLICABLE: {$key}\n";
  }
}
?>
