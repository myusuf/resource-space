<?php 
//
$dropdown_fields = array(80,88,95,96,98,105);
//
include("rspaceapi.php");
$results = sql_query("select r.ref,rd.resource_type_field, rd.value from resource r LEFT JOIN resource_data rd on r.ref = rd.resource where rd.resource_type_field in (80,88,95,96,98,105)" );
print_r($results);
foreach ( $results as $result ) {
  //
  $valin = $result['value'];
  $rsid = $result['ref'];
  $rsfieldtype = $result['resource_type_field'];
  //
  $newval = null;
  if ( (! empty($valin) ) && ( preg_match("/^YES|NO|N|Y$/i",$valin) ) ) {
    if ( preg_match("/^YES|Y$/i",$valin) ) {
      $newval="Yes";
    }    
    if ( preg_match("/^NO|N$/i",$valin) ) {
      $newval="No";
    }    
  } else {
    //
  }
  update_field($rsid,$rsfieldtype,$newval);    
}
//
?>
