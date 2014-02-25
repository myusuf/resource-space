<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$homedir = getenv('HOME');

$localstage="/tmp/rspaceimport";
$sourcedir="{$homedir}/works/rspace/data/source";


$IMPORTRECORDS_CREATED=0;
$IMPORTRECORDS_SKIPPED=0;
$IMPORTRECORDS_BUILDPATH_FAILED=0;

$RS_FIELDID_LKP = array ();
$RS_MEDIA_TYPE_LKP = array ();
$DB_RS_XREF = array ();
$load_field_driver = array (
  'FILE_PATH', 
  'FILE_EXTENSION', 
  'FILE_NAME', 
  'USE_EXTENSION', 
  'UPDATE_DATE', 
  'CONTRIBUTOR_ID',
  'SITE_ID',
  'SHORT_NAME',
  'DATE'
);
function load_lookups () {
    //user defined
    global $RS_FIELDID_LKP;
    global $RS_MEDIA_TYPE_LKP;
    global $DB_RS_XREF;
    //
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
    $RS_FIELDID_LKP['DATE'] = 12;                                        
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
    $DB_RS_XREF['DATE'] = "MOS.CREATE_DT";                                
    $DB_RS_XREF['TITLE'] = "";                                
    //
    $RS_MEDIA_TYPE_LKP['MP3'] = 4;
    $RS_MEDIA_TYPE_LKP['WAV'] = 4;
    //
    $RS_MEDIA_TYPE_LKP['MP4'] = 3;    
    $RS_MEDIA_TYPE_LKP['MOV'] = 3;        
    $RS_MEDIA_TYPE_LKP['AVI'] = 3;        
    $RS_MEDIA_TYPE_LKP['MPG'] = 3;        
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
  
  if ( ( count($parse) < 2) || ( count($parse2) < 2) || ( count($parse3) < 2)) {
      echo "INVALID DATE INPUT {$datein}\n";
      //sleep(10);
      return "";
  }
  
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

$datadir = "{$homedir}/works/rspace/data";
$dbdump = "{$homedir}/works/rspace/data/dbdump";
$datasource = "{$homedir}/works/rspace/data/source";
$dataload = "{$homedir}/works/rspace/data/load";

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
$mediaobject = loadTable( "{$dbdump}/mediaobject.tsv", 0 );
$derivative = loadTable( "{$dbdump}/derivative.tsv", 1 , "many");
$mediaobjectsite = loadTable( "{$dbdump}/mediaobjectsite.tsv", 2 );

//process
//exit('thats all folks');
$found=0;
$notfound=0;
$fn = fopen("{$dataload}/remoteimport_test.xml","w");
$rsep = "\n";
#$limit=200;
fwrite($fn, "<resourceset>{$rsep}");
foreach ( array_keys($mediaobject) as $key) {
      print "KEY = $key \n";
      if (! isset($mediaobjectsite[$key]) ) {
          $IMPORTRECORDS_SKIPPED++;
          continue;
      }
      if (! isset($derivative[$key]) ) {
          $IMPORTRECORDS_SKIPPED++;
          continue;
      }
      $record = $mediaobject[$key];
      $IMPORTDATA = array();
      //
      $IMPORTDATA['EXTERNAL_ID'] = $record['MEDIA_OBJECT_UUID'];
      $update_dt_fmt = rs_dateformat($mediaobjectsite[$key]['UPDATE_DT']);
      $create_dt_fmt = rs_dateformat($mediaobjectsite[$key]['CREATE_DT']);
     
      
      //$IMPORTDATA['FILE_PATH'] = "D.FILE_PATH";    
      //$IMPORTDATA['FILE_EXTENSION'] = "D.FILE_EXTENSION";        
      //$IMPORTDATA['FILE_NAME'] = "D.FILE_NAME";            
      //$IMPORTDATA['USE_EXTENSION'] = "D.USE_EXTENSION";                
      $IMPORTDATA['UPDATE_DATE'] = $update_dt_fmt;
      $IMPORTDATA['DATE'] = $create_dt_fmt;
    
      $IMPORTDATA['CONTRIBUTOR_ID'] = $record['CONTRIBUTOR_ID'];
      $IMPORTDATA['SITE_ID'] = $mediaobjectsite[$key]['SITE_ID'];                            
      $IMPORTDATA['SHORT_NAME'] = $mediaobjectsite[$key]['SHORT_NAME'];          
      //
      //$IMPORTDATA['XML_FILENAME'] = $record['THUMBNAIL_URL'];
      //$IMPORTDATA['XML_FILENAME'] = "{$localstage}/$record['THUMBNAIL_URL']";
      $IMPORTDATA['XML_KEYFIELD_REF'] = $RS_FIELDID_LKP['EXTERNAL_ID'];
      $IMPORTDATA['XML_KEYFIELD'] = $IMPORTDATA['EXTERNAL_ID'];
    
      // DATE == 12
      $derivative_found = 0;
      if ( isset ($derivative[$key] ) ) {
          $derivative_found = 1;
          $derivatives = $derivative[$key];
          foreach ( $derivatives as $derivativerec ) {
            $IMPORTDATA['FILE_PATH'] = $derivativerec['FILE_PATH'];    
            $IMPORTDATA['FILE_EXTENSION'] = $derivativerec['FILE_EXTENSION'];    
            $IMPORTDATA['FILE_NAME'] = $derivativerec['FILE_NAME'];    
            $IMPORTDATA['USE_EXTENSION'] = $derivativerec['USE_EXTENSION'];    
            $IMPORTDATA['XML_FILENAME'] = "{$localstage}/{$derivativerec['FILE_PATH']}/{$derivativerec['FILE_NAME']}.{$derivativerec['FILE_EXTENSION']}";
            $media_type=1;
            if ( array_key_exists(strtoupper($IMPORTDATA['FILE_EXTENSION']),$RS_MEDIA_TYPE_LKP ) ) {
              echo "FOUND!!\n";
              $media_type = $RS_MEDIA_TYPE_LKP[strtoupper($IMPORTDATA['FILE_EXTENSION'])];
            }
            //echo "MEDIA TYPE = {$media_type}\n";
            //echo "EXTENSION = {$IMPORTDATA['FILE_EXTENSION']}\n";
            //print_r($RS_MEDIA_TYPE_LKP);
            // sleep(10);
            $IMPORTDATA['RS_MEDIA_TYPE'] = $media_type; 
            $IMPORTDATA['TITLE'] = "{$IMPORTDATA['FILE_NAME']}.{$IMPORTDATA['FILE_EXTENSION']}"; 
            break;
          }
      }
      if ($derivative_found == 0) {
          print_r($IMPORTDATA);
          echo "!!! NOT FOUND !!!\n";
          $notfound++;
          } else {
          print_r($IMPORTDATA);
          $found++;
          echo "!!! FOUND !!!\n";
      }
      echo "FOUND = {$found}\n";
      echo "NOT FOUND = {$notfound}\n";
      //sleep(2);
      //
      //print_r($IMPORTDATA);
      //print_r($RS_FIELDID_LKP);
      //sleep(10);
      if ( $derivative_found ) {
        $localfilepath="";
        $newpath="";
        //if ( preg_match("/^.*\.gov.*?\/(.*)$/",$IMPORTDATA['XML_FILENAME'], $matches) ) {
        //    echo "Rest of the path is " . $matches[1] . "\n";
        //    $localpath = $matches[1];
        //    $localpath_full = "{$localstage}/{$localpath}";
        //    $newpath=$localpath_full;
        //    if ( file_exists($localpath_full) ) {
        //        //
        //    } else {
        //       $dirpath = dirname($localpath_full);
        //       $APPID = getenv("USER");
        //       system("sudo mkdir -p {$dirpath}",$rtn); 
        //       system("sudo chown {$APPID}:{$APPID} {$localstage}",$rtn);                
        //       system("chmod 777 {$localstage}",$rtn);                               
        //       system("sudo wget -O {$localpath_full} {$IMPORTDATA['XML_FILENAME']} ", $rtn);
        //    }
        //};
        $localfilepath="{$localstage}/{$IMPORTDATA['FILE_PATH']}/{$IMPORTDATA['FILE_NAME']}.{$IMPORTDATA['FILE_EXTENSION']}";
        $sourcefilepath="{$datasource}/{$IMPORTDATA['FILE_PATH']}/{$IMPORTDATA['FILE_NAME']}.{$IMPORTDATA['FILE_EXTENSION']}";
        if ( file_exists($sourcefilepath) ) {
          if ( file_exists($localfilepath) ) {
            //
          } else {
            $newdir = dirname($localfilepath);
            if (! file_exists($newdir) ) {
              system("sudo mkdir -p {$newdir}", $rc);
            }
            system("sudo cp {$sourcefilepath} {$localfilepath}", $rc);
          }
        } else {
          $IMPORTRECORDS_SKIPPED++;
          continue;
        }        
                
        fwrite($fn, "<resource type=\"{$IMPORTDATA['RS_MEDIA_TYPE']}\">{$rsep}");
        fwrite($fn, "<keyfield ref=\"{$IMPORTDATA['XML_KEYFIELD_REF']}\">{$IMPORTDATA['XML_KEYFIELD']}</keyfield>{$rsep}");
        foreach ( $load_field_driver as $load_field) {
          echo "{$RS_FIELDID_LKP[$load_field]}\n";  
          echo "{$IMPORTDATA[$load_field]}\n"; 
          fwrite($fn, "<field ref=\"{$RS_FIELDID_LKP[$load_field]}\">{$IMPORTDATA[$load_field]}</field>{$rsep}");
        }
        //sleep(5);
        //collection can also be set.
        //fwrite($fn, "<filename>{$IMPORTDATA['XML_FILENAME']}</filename>{$rsep}");
        fwrite($fn, "<filename>{$localfilepath}</filename>{$rsep}");        
        fwrite($fn,"</resource>{$rsep}");
        $IMPORTRECORDS_CREATED++;
        //if ( $IMPORTRECORDS_CREATED == $limit ) {
        //  break;
        //}
      }
      //sleep(15);
}
fwrite($fn,"</resourceset>");
fclose($fn);
echo "IMPORT RECORDS CREATED : " . $IMPORTRECORDS_CREATED . "\n";
echo "IMPORT RECORDS SKIPPED : " . $IMPORTRECORDS_SKIPPED . "\n";
echo "IMPORT RECORDS BUILDPATH FAILED : " . $IMPORTRECORDS_BUILDPATH_FAILED . "\n";
?>
