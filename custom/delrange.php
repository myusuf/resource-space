<?php
include_once(realpath(__DIR__) . "/../include/db.php");
include_once(realpath(__DIR__) . "/../include/general.php");
include_once(realpath(__DIR__) . "/../include/resource_functions.php");
include_once(realpath(__DIR__) . "/../include/image_processing.php");

$urange=getval("urange",-1);
$lrange=getval("lrange",-1);

if ( $urange == -1 || $lrange == -1) {
  echo "<p>Invalid Resource ID Range(s)</p>";
} else {
  if ( ( is_numeric($urange) && is_numeric($lrange) ) && ( $urange == $lrange ) ) {
    echo "Deleting {$urange}<br>";
    delete_resource($urange);
  } else {
    for($ix=$lrange;$ix<=$urange;$ix++) {
      echo "Deleting {$ix}<br>";
      delete_resource($ix);
    }
  }
}

?>
