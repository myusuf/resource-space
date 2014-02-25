<?php
$buildctr=0;
$scripttag="part-";
$scriptctr=1;
$xmldata = file('remoteimport_accload02.xml');
$chunks = array_chunk($xmldata,5000);
//print_r($chunks);
system('sudo rm *part*',$rc);
for ( $ox=0;$ox<count($chunks);$ox++ ) {
  $key = $ox;
  if ( $key == ( count($chunks) - 1 ) ) {
    continue;
  }
  $buildctr++;
  $scriptdata = $chunks[$key];
  echo "Building File {$buildctr}...\n";
  //
  for($ix=(count($scriptdata) - 1);$ix>0;$ix--) {
    if ( ( $ix != 99 ) && (preg_match("/^<\/resource>/",$scriptdata[$ix])) ) {
       echo "Found Resource Close Tag at subscript {$ix}\n";
       echo "Copying Data After Tag to next subscript\n";
       // rebuild current array
       $rebuilt_array_current = array_slice($scriptdata,0, ( $ix + 1 ));
       $chunks[$key] = $rebuilt_array_current;
       // rebuld next array
       $nextarr = $chunks[($key + 1)];
       $remdata = array_slice($scriptdata,($ix + 1));
       $junk = array_splice($nextarr,0,0,$remdata);
       $chunks[($key + 1)] = $nextarr;
       //
       break;
    }
  } 
}
$outputctr=0;
$buildctr=0;
foreach ( $chunks as $chunk ) {
  $buildctr++;
  $chunkcopy = $chunk;
  print_r($chunkcopy);
  //sleep(5);
  if ( $outputctr != 0 ) {
    array_unshift($chunkcopy,"<resourceset>\n");    
  }
  if ( $outputctr != ( count($chunks) - 1 ) ) {
    array_push($chunkcopy,"</resourceset>");    
  }
  $outputctr++;
  $buildctr_dsp = sprintf("%04s",$buildctr);
  file_put_contents("remoteimport_{$scripttag}{$buildctr_dsp}.xml",implode($chunkcopy));
}
