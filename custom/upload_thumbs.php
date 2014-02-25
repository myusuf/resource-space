<?php 
//
include("rspaceapi.php");
$results = sql_query("select r.ref, rd.value from resource r LEFT JOIN resource_data rd on r.ref = rd.resource where r.resource_type = 4 and rd.resource_type_field = 89");
print_r($results);
foreach ( $results as $result ) {
  $newdir = "/tmp/rstemp/{$result['ref']}";
  system("sudo mkdir -p {$newdir}",$rtn);
  $fname = basename($result['value']);
  system("sudo wget -O {$newdir}/{$fname} {$result['value']}",$rtn);
  system("sudo chmod 744 {$newdir}/{$fname}",$rtn);
  //
  $url = "http://dev-jukebox1.loctest.gov/resourcespace/custom/load_preview.php";
  $ch = curl_init($url);
  $params = array('userfile' => '@' . "{$newdir}/$fname",'id' => $result['ref']);
  //
  curl_setopt($ch, CURLOPT_TIMEOUT, 10);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $ret = curl_exec($ch);
  //
  echo "REF = {$result['ref']}\n";
  sleep(10);
  //exit();
}
//
?>
