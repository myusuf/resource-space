<?php 
//
include("rspaceapi.php");
$results = sql_query("select r.ref, rd.value from resource r LEFT JOIN resource_data rd on r.ref = rd.resource where rd.resource_type_field = 73");
//print_r($results);
$rsid_uuid_xref = array();
foreach ( $results as $result ) {
  $rsid_uuid_xref[$result['value']] = $result['ref'];
}
if ( count($argv) > 1 ) {
  $spoolfile=$argv[1];
  $fp = fopen("{$spoolfile}","w");
  fwrite($fp,'<?php' . "\n");
  $arrexport = var_export($rsid_uuid_xref,true);
  fwrite($fp, '$rsid_uuid_xref=' . $arrexport . ";\n");
  fwrite($fp,'?>' . "\n");
  fclose($fp);
} else {
  echo var_export($rsid_uuid_xref);
}
//
?>
