<?php
if ( class_exists('FieldType') ) {
  //
} else {
  include realpath(__DIR__) . "/FieldType.php";
}

function fieldhelper($field) {
  //
  $field = str_replace(' ','_',strtoupper($field));
  $field = preg_replace('/[^\w]/','',$field);
  if ( defined('FieldType::' . $field) ) {
    return constant('FieldType::' . $field);
  } else {
    return "";
  }
}
//echo "title = " . fieldhelper('title') . PHP_EOL;
//echo "title = " . fieldhelper('is primary') . PHP_EOL;
//echo "title = " . fieldhelper('cc url') . PHP_EOL;
//echo "title = " . fieldhelper('aspect ratio') . PHP_EOL;
