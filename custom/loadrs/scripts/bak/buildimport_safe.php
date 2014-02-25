<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$RS_FIELDID_LKP = array ();
$DB_RS_XREF = array ();
function load_lookups () {
    //user defined
    $RS_FIELDID_LKP['EXTERNAL_ID'] = 84;
    $RS_FIELDID_LKP['FILE_PATH'] = 77;    
    $RS_FIELDID_LKP['FILE_EXTENSION'] = 78;        
    $RS_FIELDID_LKP['FILE_NAME'] = 79;            
    $RS_FIELDID_LKP['USE_EXTENSION'] = 80;                
    $RS_FIELDID_LKP['UPDATE_DATE'] = 81;                    
    $RS_FIELDID_LKP['CONTRIBUTOR_ID'] = 82;                        
    $RS_FIELDID_LKP['SITE_ID'] = 83;                            
    $RS_FIELDID_LKP['SHORT_NAME'] = 76;                                
    //system
    $RS_FIELDID_LKP['TITLE'] = 8;                                        
    $RS_FIELDID_LKP['CAPTION'] = 18;                                    
    //
    $DB_RS_XREF['EXTERNAL_ID'] = "MO.MEDIA_OBJECT_SITE_UUID";
    $DB_RS_XREF['FILE_PATH'] = "D.FILE_PATH";    
    $DB_RS_XREF['FILE_EXTENSION'] = "D.FILE_EXTENSION";        
    $DB_RS_XREF['FILE_NAME'] = "D.FILE_NAME";            
    $DB_RS_XREF['USE_EXTENSION'] = "D.USE_EXTENSION";                
    $DB_RS_XREF['UPDATE_DATE'] = "MOS.UPDATE_DT";                    
    $DB_RS_XREF['CONTRIBUTOR_ID'] = "MO.CONTRIBUTOR_ID";                        
    $DB_RS_XREF['SITE_ID'] = "MOS.SITE_ID";                            
    $DB_RS_XREF['SHORT_NAME'] = "MOS.SHORT_NAME";                                
    //
}

function rs_dateformat( $datein ) {
  $monthlkp = array (
      'JAN' => 1, 
      'FEB' => 2, 
      'MAR' => 3, 
      'APR' => 4,
      'MAY' => 5, 
      'JUN' => 6, 
      'JUL' => 7, 
      'AUG' => 8,       
      'SEP' => 9, 
      'OCT' => 10, 
      'NOV' => 11, 
      'DEC' => 12,
  );
  //DB - 03-MAY-11 08.06.44
  $parse = explode(" ", $datein);
  //seg1
  $parse2 = explode("-", $parse[0]);
  $parse3 = explode(".", $parse[1]);  
          
  $raw_dd = $parse2[0];
  $raw_mon = $parse2[1];  
  $raw_yy = $parse2[2];    
  //
  $DATE_YYYY = "20" . $raw_yy;
  $monthdd = $monthlkp[strtoupper($raw_mon)];
  $DATE_MM = sprintf("%02d",$monthdd);
  $DATE_DD = sprintf("%02d",$raw_dd);
  //
  $raw_hh = $parse3[0];
  $raw_mi = $parse3[0];  
  //
  $hh24_adjust = 0;
  if ( preg_match("/PM/", $datein ) ) {
      $hh24_adjust = 12;
  }
  $DATE_HH24 = $raw_hh + $hh24_adjust;
  $DATE_MI = $raw_mi;      
  //RS FORMAT YYYY-MM-DD HH24:MI
  return "{$DATE_YYYY}-{$DATE_MM}-{$DATE_DD} {$DATE_HH24}:{$DATE_MI}";
}

load_lookups();

$homedir = getenv('HOME');
$datadir = "{$homedir}/works/rspace/data";

function loadTable( $fpath , $keypos , $oneormany = "one" ) {
  $data = array();
  $columnHeaders = array();
  $hdrrow=1;
  $keyrow=$keypos;
  $rowctr=0;
  $tagctr=0;
  $fp = fopen( "{$fpath}","r");
  while ( ! feof($fp)) {
      $linein = fgets($fp);
      //echo "{$linein}\n";
      $rowctr++;
      $parsed = split("\t",$linein);
      if (count($parsed) < 2 ) {
          continue;
      }
      if ( $rowctr == 1 ) {
          //echo "found header\n";
          foreach ( $parsed as $parsedcol ) {
              array_push($columnHeaders,$parsedcol);
          }
      } else {
              //echo "found data, count = " . count($parsed) . "\n";        
              $record = array();
              $key="9999" . (++$tagctr);
              for ( $ix=0 ; $ix<count($parsed);$ix++) {
                //echo "ITER = {$ix}\n";
                $valfmt = $parsed[$ix];
                $valfmt = trim($valfmt);
                $valfmt = preg_replace("/^[\"]/","",$valfmt);    
                $valfmt = preg_replace("/[\"]$/","",$valfmt);                    
                if ( $ix == $keyrow) {
                    $key = $valfmt;
                }
                $colfmt = $columnHeaders[$ix];
                $colfmt = trim($colfmt);
                $colfmt = preg_replace("/^[\"]/","",$colfmt);    
                $colfmt = preg_replace("/[\"]$/","",$colfmt);                    
                $record[$colfmt] = $valfmt;
              }
              //print_r($record);
              //echo "<- RECORD\n";            
              if ( $oneormany == "many" ) {
                if ( array_key_exists($key, $data ) ) { 
                    array_push($data[$key],$record);  
                } else {
                    $data[$key] = array( $record );
                }
              } else {
                $data[$key] = $record;
              }  
      }
      //print_r($data);
      //echo "<- DATA\n";
  }
  fclose($fp);
  return $data;
}

//pass in filepath and keypos
$mediaobject = loadTable( "{$datadir}/mediaobject.tsv", 0 );
$derivative = loadTable( "{$datadir}/derivative.tsv", 1 , "many");
$mediaobjectsite = loadTable( "{$datadir}/mediaobjectsite.tsv", 2 );

//process
//exit('thats all folks');
foreach ( array_keys($mediaobject) as $key) {
      print "KEY = $key \n";
      $record = $mediaobject[$key];
      $IMPORTDATA = array();
      //
      $IMPORTDATA['EXTERNAL_ID'] = $record['MEDIA_OBJECT_UUID'];
      $update_dt_fmt = rs_dateformat($mediaobjectsite[$key]['UPDATE_DT']);
      //$IMPORTDATA['FILE_PATH'] = "D.FILE_PATH";    
      //$IMPORTDATA['FILE_EXTENSION'] = "D.FILE_EXTENSION";        
      //$IMPORTDATA['FILE_NAME'] = "D.FILE_NAME";            
      //$IMPORTDATA['USE_EXTENSION'] = "D.USE_EXTENSION";                
      $IMPORTDATA['UPDATE_DATE'] = $update_dt_fmt;
      $IMPORTDATA['CONTRIBUTOR_ID'] = $record['CONTRIBUTOR_ID'];
      $IMPORTDATA['SITE_ID'] = $mediaobjectsite[$key]['SITE_ID'];                            
      $IMPORTDATA['SHORT_NAME'] = $mediaobjectsite[$key]['SHORT_NAME'];          
      //
      if ( isset ($derivative[$key] ) ) {
          $derivatives = $derivative[$key];
          foreach ( $derivatives as $derivativerec ) {
            $IMPORTDATA['FILE_PATH'] = $derivativerec['FILE_PATH'];    
            $IMPORTDATA['FILE_EXTENSION'] = $derivativerec['FILE_EXTENSION'];    
            $IMPORTDATA['FILE_NAME'] = $derivativerec['FILE_NAME'];    
            $IMPORTDATA['USE_EXTENSION'] = $derivativerec['USE_EXTENSION'];    
            break;
          }
      }
      print_r($IMPORTDATA);
      sleep(15);
}

?>
