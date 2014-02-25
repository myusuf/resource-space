<?php 
//
include("rspaceapi.php");
$results = sql_query("select rtf.ref, rtf.name, rtf.title from resource_type_field rtf where resource_type = 0 order by rtf.ref ASC");
print_r($results);
$fp = fopen("FieldType.php","w");
fwrite($fp,'<?php' . PHP_EOL);
fwrite($fp,'class FieldType { ' . PHP_EOL);
//
//for duplicate titles use highest val.
//
$field_types = array();
foreach ( $results as $key => $result ) {
  echo $result['title'] . PHP_EOL;
  $title = $result['title'];
  $ref = $result['ref'];
  $newtitle = preg_replace('/ /','_',$title);
  $newtitle = preg_replace('/[^A-Z0-9_]/','',strtoupper($newtitle));
  $results[$key]['title'] = $newtitle;
  if ( array_key_exists($newtitle,$field_types) ) {
    $prevref = $field_types[$newtitle]; 
    if ($ref > $prevref) {
      $field_types[$newtitle] = $ref;
    }
  } else {
    $field_types[$newtitle] = $ref;
  }
}
//
echo str_repeat("-",50) . PHP_EOL;
foreach ( $results as $result ) {
  $title = $result['title'];
  echo "{$title}\n";
  //sleep(5);
  $ref = $result['ref'];
  if ( $ref == $field_types[$title] ) {
    fwrite($fp,"  const {$title} = {$ref};" . PHP_EOL);
  }
}
fwrite($fp,'} ' . PHP_EOL);
?>
