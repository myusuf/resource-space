<?php
if ( isset($_GET['batch']) ) : 
$BATCHTAG = $_GET['batch'];
$METADATA_DIR = realpath(__DIR__) . "/metadata";
$prior_runs = array();
//
if ( file_exists("{$METADATA_DIR}/loadtracker") ) {
  $prior_loads = file("{$METADATA_DIR}/loadtracker"); 
  $prior_runs = preg_grep("/^\"{$BATCHTAG}\" load initiated/", $prior_loads);
  if ( $prior_runs ) {
    echo "<p><span style='color:red;'>!!! WARNING: Load May Have Been Run Before.  Check History !!!</span></p>";
  }
  $fp = fopen("{$METADATA_DIR}/loadtracker","a");
  //fwrite( $fp, "\"{$BATCHTAG}\" load initiated on " . date("Y-m-d H:i:s") . PHP_EOL);
  //fclose($fp);
} else {
  $fp = fopen("{$METADATA_DIR}/loadtracker","w");
  //fwrite( $fp, "\"{$BATCHTAG}\" load initiated on " . date("Y-m-d H:i:s") . PHP_EOL);
  //fclose($fp);
}
$scramble_key = 'aLaJUpuqyGYM';
$METADATA_FILE = "remoteimport_{$BATCHTAG}.xml";
//$filename = '/home/abenn/works/rspace/data/load/remoteimport_{$BATCHTAG}.xml';
$filename = "{$METADATA_DIR}/{$METADATA_FILE}";
$f = fopen( $filename, 'r') or exit("METADATA SOURCE FILE \"{$METADATA_FILE}\" NOT FOUND");
//
fwrite( $fp, "\"{$BATCHTAG}\" load initiated on " . date("Y-m-d H:i:s") . PHP_EOL);
fclose($fp);
//
$file_check = file("{$METADATA_DIR}/{$METADATA_FILE}");
$resources = preg_grep("/^[\s]*<[\s]*resource [\s]*type=/i", $file_check);
$xml_source = fread($f,filesize($filename));
$md5r = md5($scramble_key . $xml_source);

?>
<h2><span style="color:blue;"><u>IMPORT METADATA</u></span></h2>
<?php echo "-- Load Details --\n"; ?>
<ul>
<li>Batch Name : <?php echo $BATCHTAG ?></li>
<li>Source File : <?php echo $METADATA_FILE ?></li>
<li>File Last Mod. : <?php echo date("Y-m-d H:i:s",filemtime("{$METADATA_DIR}/{$METADATA_FILE}")) ?></li>
<li>Resources To Update : <?php echo count($resources) ?></li>
</ul>
<?php
if ( $prior_runs ) {
  echo "-- Load History For {$BATCHTAG} --\n";
  echo "<p><ul><li>" . join('</li><li>',$prior_runs) . "</li></ul></p>";
}
?>
<FORM action="../plugins/remoteimport/pages/update.php" method="POST">
<input type="hidden" name="xml" value="<?php echo base64_encode($xml_source) ?>"/>
<input type="hidden" name="sign" value="<?php echo $md5r ?>"/>
<input type="submit" name="submit" value="Begin Import"/>
</FORM>
<?php else: ?>
NO BATCH TAG PASSED TO SCRIPT
<?php endif; ?>
