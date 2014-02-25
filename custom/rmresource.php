<?php
include_once(realpath(__DIR__) . "/../include/db.php");
include_once(realpath(__DIR__) . "/../include/general.php");
include_once(realpath(__DIR__) . "/../include/resource_functions.php");
include_once(realpath(__DIR__) . "/../include/image_processing.php");

if ($argc > 1 ) {
  if ( strcasecmp($argv[1],"file")  == 0) {
    if ( $argc > 2) {
      $batchpath = "{$argv[2]}";
      if ( file_exists("{$batchpath}") ) {
        $fp = fopen("$batchpath","r");
        while (! feof($fp) ) {
          $resourceid = trim(fgets($fp));
          if ( delete_resource($resourceid) ) {
            echo "DELETE SUCCESSFUL: $resourceid\n";
            //            
          } else {
            exit(1);
          }
        }
      }
    } else {
      exit(1);
    }
  } else {
    $resourceid=$argv[1]; 
    if ( delete_resource($resourceid) ) {
      exit(0);
    } else {
      exit(1);
    }
  }
} else {
  exit(1);
}
?>
