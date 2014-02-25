<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

//$tempout_nf = fopen("tempfile_nf","w");
//$tempout_f = fopen("tempfile_f","w");
$watchfiles=array();
$chkdir = dir('../data/source/ammem/AmericaAtWorkAmericaAtLeisure');
while ( false !== ($fileob = $chkdir->read()) ) {
  //
  if ( preg_match("/^[.]*$/",$fileob) ) {
    continue;
  }
  $watchfiles[$fileob] = 'INIT';
  //echo "{$fileob}\n";
}

$CREATEBATCH=true;
$CREATEALTERNATIVEFILES=true;

$batchhome = "/ingest/resourcespace/batchload";
$batchtag = "batch04";
$batchdir = "{$batchhome}/{$batchtag}";

if ( count($argv) > 1 ) {
  if ( $argv[1] == "cleanup" ) {
    if ( file_exists( "{$batchdir}" ) ) {
      system("sudo rm -r {$batchdir}",$rt);  
      echo "Batch Directory Deleted\n";
      //sleep(10);
    }
  }
}

$loaddate = date("Y-m-d");
$loaddate = $loaddate . " 00:00";

$homedir = getenv('HOME');

$localstage="/tmp/rspaceimport";
//$sourcedir="{$homedir}/works/rspace/data/source";
$sourcedir="/apps/resourcespace/rspaceimport";


$IMPORTRECORDS_CREATED=0;
$IMPORTRECORDS_SKIPPED=0;
$IMPORTRECORDS_BUILDPATH_FAILED=0;

$RS_FIELDID_LKP = array ();
$RS_MEDIA_TYPE_LKP = array ();
$DB_RS_XREF = array ();
$load_rs_field_driver = array(
  'TITLE'
);
$load_field_driver = array (
  'DER.FILE_PATH', 
  'DER.FILE_EXTENSION', 
  'DER.FILE_NAME', 
  'DER.USE_EXTENSION', 
  'DER.UPDATE_DT', 
  'DER.MEDIA_SERVER_ID', 
  'DER.IS_STREAMABLE', 
  'MO.CONTRIBUTOR_ID', 
  'MO.THUMBNAIL_URL', 
  'MO.BACKGROUND_URL', 
  'MO.CC_URL', 
  'MO.DURATION', 
  'MO.LANGUAGE', 
  'MO.ASPECT_RATIO', 
  'MO.CAN_EMBED', 
  'MO.CAN_DOWNLOAD', 
  'MO.IS_PUBLISHED', 
  'MO.VIEW_COUNT', 
  'MO.SHARE_COUNT', 
  'MO.ACCESS_TXT', 
  'MO.RIGHTS_TXT', 
  'MO.CREDITS_TXT', 
  'MO.MEDIA_OBJECT_UUID', 
  'MO.CREATE_DT',
  'MOS.SITE_ID',
  'MOS.SHORT_NAME',
  'MOS.LONG_NAME',
  'MOS.SHORT_DESC',
  'MOS.LONG_DESC',
  'MOS.IS_PRIMARY',
  'MOS.DETAIL_URL',
  'MOS.EXTERNAL_ID'
);
$load_field_dates = array (
  'DER.UPDATE_DT','MO.CREATE_DT'
);
$load_field_rules = array (
  'DER.USE_EXTENSION' => 'YesNo'
);
function applyrule ($fieldname,$value) {
  //
  global $load_field_rules;
  
  $rules = explode("|",$load_field_rules[$fieldname]);
  $valuein = trim($value);
  $newvalue = $valuein;
  #
  //echo "VALUE = {$valuein}\n";
  //echo "NEWVALUE = {$newvalue}\n";
  //echo "rules = \n";
  //print_r($rules);
  //sleep(10);
  foreach ( $rules as $rule ) {
    if ( $rule == 'YesNo') {
      if ( preg_match("/^(Y|Yes)$/i", $newvalue) ) {
        $newvalue = 'Yes';
      } elseif ( preg_match("/^(N|No)$/i", $newvalue) ) {
        $newvalue = 'No';
      } else {
        $newvalue = '';
      }
    }
  }
  return $newvalue;
}
function striptable ($aliasedcol) {
  if ( strpos($aliasedcol, ".") !== false ) {
    return substr($aliasedcol,strpos($aliasedcol,".") + 1);
  } else {
    return $aliasedcol;
  }
}

function gettable ($aliasedcol) {
  if ( strpos($aliasedcol, ".") !== false ) {
    return substr($aliasedcol,0,strpos($aliasedcol,"."));
  } else {
    return $aliasedcol;
  }
}

function load_lookups () {
    //user defined
    global $RS_FIELDID_LKP;
    global $RS_MEDIA_TYPE_LKP;
    global $DB_RS_XREF;
    //

    $RS_FIELDID_LKP['MO.MEDIA_OBJECT_UUID'] = 73;
    $RS_FIELDID_LKP['MOS.EXTERNAL_ID'] = 84;
    $RS_FIELDID_LKP['DER.FILE_PATH'] = 77;    
    $RS_FIELDID_LKP['DER.FILE_EXTENSION'] = 78;        
    $RS_FIELDID_LKP['DER.FILE_NAME'] = 79;            
    $RS_FIELDID_LKP['DER.USE_EXTENSION'] = 80;                
    $RS_FIELDID_LKP['DER.UPDATE_DT'] = 81;                    
    $RS_FIELDID_LKP['MO.CONTRIBUTOR_ID'] = 82;                        
    $RS_FIELDID_LKP['MOS.SITE_ID'] = 83;                            
    $RS_FIELDID_LKP['MOS.SHORT_NAME'] = 76;                                
    //more fields
    $RS_FIELDID_LKP['MOS.IS_PRIMARY'] = 88;                                
    $RS_FIELDID_LKP['MOS.LONG_DESC'] = 87;                                
    $RS_FIELDID_LKP['MOS.SHORT_DESC'] = 86;                                
    $RS_FIELDID_LKP['MOS.LONG_NAME'] = 85;                                
    $RS_FIELDID_LKP['MOS.SHORT_NAME'] = 76;                                
    $RS_FIELDID_LKP['MO.THUMBNAIL_URL'] = 89;                                
    $RS_FIELDID_LKP['MO.BACKGROUND_URL'] = 90;                                
    $RS_FIELDID_LKP['MO.CC_URL'] = 91;                                
    $RS_FIELDID_LKP['MO.DURATION'] = 92;                                
    $RS_FIELDID_LKP['MO.LANGUAGE'] = 93;                                
    $RS_FIELDID_LKP['MO.ASPECT_RATIO'] = 94;                                
    $RS_FIELDID_LKP['MO.CAN_EMBED'] = 95;                                
    $RS_FIELDID_LKP['MO.CAN_DOWNLOAD'] = 96;                                
    $RS_FIELDID_LKP['MO.VIEW_COUNT'] = 97;                                
    $RS_FIELDID_LKP['MO.IS_PUBLISHED'] = 98;                                
    $RS_FIELDID_LKP['MO.SHARE_COUNT'] = 99;                                
    $RS_FIELDID_LKP['MO.ACCESS_TXT'] = 100;                                
    $RS_FIELDID_LKP['MO.RIGHTS_TXT'] = 101;                                
    $RS_FIELDID_LKP['MO.CREDITS_TXT'] = 102;                                
    $RS_FIELDID_LKP['MOS.DETAIL_URL'] = 103;                                
    $RS_FIELDID_LKP['DER.MEDIA_SERVER_ID'] = 104;                                
    $RS_FIELDID_LKP['DER.IS_STREAMABLE'] = 105;                                
    $RS_FIELDID_LKP['MO.CREATE_DT'] = 12;                                
    //system
    $RS_FIELDID_LKP['TITLE'] = 8;
    $RS_FIELDID_LKP['DATE'] = 12;                                        
    $RS_FIELDID_LKP['CAPTION'] = 18;              
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
#$datasource = "{$homedir}/works/rspace/data/source";
$datasource = "/apps/resourcespace/rspaceimport";
$dataload = "{$homedir}/works/rspace/data/load";

function loadTable( $fpath , $keypos , $oneormany = "one" ) {
  $data = array();
  $columnHeaders = array();
  $hdrrow=1;
  $keyrow=$keypos;
  $rowctr=0;
  $tagctr=0;
  $fp = fopen( "{$fpath}","r");
  //echo "{$fpath}\n";
  //sleep(10);
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
      $hasderivative=false;
      $hassite=false;
      $derivativerecs=array();
      if (! isset($mediaobjectsite[$key]) ) {
          $IMPORTRECORDS_SKIPPED++;
          continue;
      } else {
        $hassite=true;
      }
      if (! isset($derivative[$key]) ) {
          $IMPORTRECORDS_SKIPPED++;
          continue;
      } else {
        $hasderivative=true;
        $derivativerecs = $derivative[$key];
        print_r($derivativerecs);
        //sleep(10);
      }
      $record = $mediaobject[$key];
      $IMPORTDATA = array();
      $ALTERNATIVES = array();
      //


      foreach ( $load_field_driver as $fieldin ) {
         #echo "BEFORE      : " . $fieldin . "\n"; 
         #echo "TABLEREMOVED: " . striptable($fieldin) . "\n"; 
         #echo "JUSTTHETABLE: " . gettable($fieldin) . "\n"; 
         $tablealias=gettable($fieldin);
         $colname=striptable($fieldin);
         if ( $tablealias == 'MOS' && ($hassite) ) {
            if ( in_array( $fieldin, $load_field_dates ) ) {
              $IMPORTDATA[$fieldin] = rs_dateformat($mediaobjectsite[$key][$colname]);  
            } else {
              $IMPORTDATA[$fieldin] = $mediaobjectsite[$key][$colname];  
            }
         } elseif ( $tablealias == 'MO' ) {
            if ( in_array( $fieldin, $load_field_dates ) ) {
              $IMPORTDATA[$fieldin] = rs_dateformat($record[$colname]);  
            } else {
              $IMPORTDATA[$fieldin] = $record[$colname];  
            }
         } elseif ( $tablealias == 'DER' && ($hasderivative) ) {
           foreach ( $derivativerecs as $derivativerec ) {
             $altkey = 'ALTKEY-' . $derivativerec['DERIVATIVE_ID'];
             if ( in_array( $fieldin, $load_field_dates ) ) {
               if ($derivativerec['IS_PRIMARY'] == 'Y' ) {
                 $IMPORTDATA[$fieldin] = rs_dateformat($derivativerec[$colname]);  
               } else {
                 if (! isset( $ALTERNATIVES[$altkey] ) ) {
                   $ALTERNATIVES[$altkey] = array();
                 } 
                 $ALTERNATIVES[$altkey][$fieldin] = rs_dateformat($derivativerec[$colname]);
               }
             } else {
               if ($derivativerec['IS_PRIMARY'] == 'Y' ) {
                 $IMPORTDATA[$fieldin] = $derivativerec[$colname];
               } else {
                 if (! isset( $ALTERNATIVES[$altkey] ) ) {
                   $ALTERNATIVES[$altkey] = array();
                 } 
                 $ALTERNATIVES[$altkey][$fieldin] = $derivativerec[$colname];
               }
             }
             //sleep(5);
           }
         } else {
           //put placeholder
           $IMPORTDATA[$fieldin] = "NOT-FOUND";
         }
      }
      // ADDITIONAL PROCESSING
      //$IMPORTDATA['EXTERNAL_ID'] = $record['MEDIA_OBJECT_UUID'];
      //$update_dt_fmt = rs_dateformat($mediaobjectsite[$key]['UPDATE_DT']);
      //$create_dt_fmt = rs_dateformat($mediaobjectsite[$key]['CREATE_DT']);
      //$IMPORTDATA['FILE_PATH'] = "D.FILE_PATH";    
      //$IMPORTDATA['FILE_EXTENSION'] = "D.FILE_EXTENSION";        
      //$IMPORTDATA['FILE_NAME'] = "D.FILE_NAME";            
      //$IMPORTDATA['USE_EXTENSION'] = "D.USE_EXTENSION";                
      //$IMPORTDATA['UPDATE_DATE'] = $update_dt_fmt;
      //$IMPORTDATA['DATE'] = $create_dt_fmt;
      //
      //$IMPORTDATA['CONTRIBUTOR_ID'] = $record['CONTRIBUTOR_ID'];
      //$IMPORTDATA['SITE_ID'] = $mediaobjectsite[$key]['SITE_ID'];                            
      //$IMPORTDATA['SHORT_NAME'] = $mediaobjectsite[$key]['SHORT_NAME'];          
      //
      //$IMPORTDATA['XML_FILENAME'] = $record['THUMBNAIL_URL'];
      //$IMPORTDATA['XML_FILENAME'] = "{$localstage}/$record['THUMBNAIL_URL']";
      $IMPORTDATA['XML_KEYFIELD_REF'] = $RS_FIELDID_LKP['MO.MEDIA_OBJECT_UUID'];
      $IMPORTDATA['XML_KEYFIELD'] = $IMPORTDATA['MO.MEDIA_OBJECT_UUID'];
      //
      // DATE == 12
      // DERIVED FIELDS
      $media_type=1;
      if ( array_key_exists(strtoupper($IMPORTDATA['DER.FILE_EXTENSION']),$RS_MEDIA_TYPE_LKP ) ) {
         echo "FOUND!!\n";
         $media_type = $RS_MEDIA_TYPE_LKP[strtoupper($IMPORTDATA['DER.FILE_EXTENSION'])];
       }
       $IMPORTDATA['RS_MEDIA_TYPE'] = $media_type; 
       // sleep(10);
       //$IMPORTDATA['TITLE'] = "{$IMPORTDATA['DER.FILE_NAME']}.{$IMPORTDATA['DER.FILE_EXTENSION']}"; 
       $IMPORTDATA['TITLE'] = "{$IMPORTDATA['MOS.SHORT_NAME']}"; 
      
      //APPLY RULES
      foreach (array_keys($IMPORTDATA) as $keyin) {
        if ( array_key_exists($keyin, $load_field_rules) ) {
           echo "Applying Rule...for {$keyin}, {$load_field_rules[$keyin]}\n";
           //sleep(6);
           $IMPORTDATA[$keyin] = applyrule($keyin,$IMPORTDATA[$keyin]);
        }
      }
      //if ($derivative_found == 0) {
      //    print_r($IMPORTDATA);
      //    echo "!!! NOT FOUND !!!\n";
      //    $notfound++;
      //    } else {
      //    print_r($IMPORTDATA);
      //    $found++;
      //    echo "!!! FOUND !!!\n";
      //}
      //echo "FOUND = {$found}\n";
      //echo "NOT FOUND = {$notfound}\n";
      //sleep(2);
      //
      //print_r($IMPORTDATA);
      //print_r($RS_FIELDID_LKP);
      //sleep(10);
      if ( $hasderivative ) {
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
        $rskey="{$IMPORTDATA['MO.MEDIA_OBJECT_UUID']}";
        $localfilepath="{$localstage}/{$IMPORTDATA['DER.FILE_PATH']}/{$IMPORTDATA['DER.FILE_NAME']}.{$IMPORTDATA['DER.FILE_EXTENSION']}";
        $sourcefilepath="{$datasource}/{$IMPORTDATA['DER.FILE_PATH']}/{$IMPORTDATA['DER.FILE_NAME']}.{$IMPORTDATA['DER.FILE_EXTENSION']}";
        $batchpath="{$batchdir}/media/$rskey";
        $resfile="{$IMPORTDATA['DER.FILE_NAME']}.{$IMPORTDATA['DER.FILE_EXTENSION']}";
        $altfolder="{$resfile}_alternatives";
        //
        if ( array_key_exists($resfile,$watchfiles) ) {
          $watchfiles[$resfile] = "FILENAME BUILT";
        }
        if ( file_exists($sourcefilepath) ) {
          //if ( preg_match("/jukebox/",$sourcefilepath) ) {
          //  echo "NOT FOUND IN PATH: {$sourcefilepath} \n";
          //  //sleep(5);
          //  fwrite($tempout_f,"{$sourcefilepath} FOUND\r\n");
          // }
          if ( file_exists($localfilepath) ) {
            //
          } else {
            $newdir = dirname($localfilepath);
            if (! file_exists($newdir) ) {
              system("sudo mkdir -p {$newdir}", $rc);
            }
            system("sudo cp {$sourcefilepath} {$localfilepath}", $rc);
          }
          //copy to batch
          if ( $CREATEBATCH ) {
            if ( file_exists("{$batchpath}/{$resfile}") ) {
              //
            } else {
              system("sudo mkdir -p {$batchpath}", $rc);
              system("sudo cp {$sourcefilepath} {$batchpath}/{$resfile}", $rc);
            } 
            //PROCESS ALTERNATIVES FOR STATIC SYNC
            if ( ($CREATEALTERNATIVEFILES) && (count($ALTERNATIVES)) ) {
              echo "alternative...\n";
              //sleep(2);
              foreach ( $ALTERNATIVES as $ALTERNATIVE ) {
                $altsourcefilepath="{$datasource}/{$ALTERNATIVE['DER.FILE_PATH']}/{$ALTERNATIVE['DER.FILE_NAME']}.{$ALTERNATIVE['DER.FILE_EXTENSION']}";
                $altfile="{$ALTERNATIVE['DER.FILE_NAME']}.{$ALTERNATIVE['DER.FILE_EXTENSION']}";
                echo "{$altsourcefilepath}\n";
                if ( file_exists($altsourcefilepath) ) {
                  if (! file_exists("{$batchpath}/{$altfolder}") ) {
                    system("sudo mkdir -p {$batchpath}/{$altfolder}", $rc);
                  }
                  if ( file_exists("{$batchpath}/{$altfolder}/{$altfile}") ) {
                    //
                  } else {
                    system("sudo cp {$altsourcefilepath} {$batchpath}/{$altfolder}/{$altfile}", $rc);
                  }
                } 
              }
              //sleep(8);
            }
          }
        } else {
          if ( array_key_exists($resfile,$watchfiles) ) {
            $watchfiles[$resfile] = "NOT FOUND IN SOURCE";
          }
          //if ( preg_match("/jukebox/",$sourcefilepath) ) {
          //  echo "NOT FOUND IN PATH: {$sourcefilepath} \n";
          //  //sleep(5);
          //  fwrite($tempout_nf,"{$sourcefilepath} NOT FOUND\r\n");
          //}
          $IMPORTRECORDS_SKIPPED++;
          continue;
        }        
                
        fwrite($fn, "<resource type=\"{$IMPORTDATA['RS_MEDIA_TYPE']}\">{$rsep}");
        fwrite($fn, "<keyfield ref=\"{$IMPORTDATA['XML_KEYFIELD_REF']}\">{$IMPORTDATA['XML_KEYFIELD']}</keyfield>{$rsep}");
        foreach ( $load_rs_field_driver as $load_rs_field ) {
          if ( preg_match("/^[\s\t]*$/",$IMPORTDATA[$load_rs_field]) ) {
            continue;
          }
          fwrite($fn, "<field ref=\"{$RS_FIELDID_LKP[$load_rs_field]}\">{$IMPORTDATA[$load_rs_field]}</field>{$rsep}");
        }
        //
        foreach ( $load_field_driver as $load_field) {
          if ( preg_match("/^[\s\t]*$/",$IMPORTDATA[$load_field]) ) {
            continue;
          }
          echo "{$RS_FIELDID_LKP[$load_field]}\n";  
          echo "{$IMPORTDATA[$load_field]}\n"; 
          fwrite($fn, "<field ref=\"{$RS_FIELDID_LKP[$load_field]}\">{$IMPORTDATA[$load_field]}</field>{$rsep}");
        }
        //sleep(5);
        //collection can also be set.
        //fwrite($fn, "<filename>{$IMPORTDATA['XML_FILENAME']}</filename>{$rsep}");
        //fwrite($fn, "<filename>{$localfilepath}</filename>{$rsep}");        
        fwrite($fn, "<collection>batchload_test1</collection>{$rsep}");        
        fwrite($fn,"</resource>{$rsep}");
        $IMPORTRECORDS_CREATED++;
        //if ( $IMPORTRECORDS_CREATED == $limit ) {
        //  break;
        //}
        if ( array_key_exists($resfile,$watchfiles) ) {
          $watchfiles[$resfile] = "ADDED";
        }
      }
      //sleep(15);
}
fwrite($fn,"</resourceset>");
fclose($fn);
echo "IMPORT RECORDS CREATED : " . $IMPORTRECORDS_CREATED . "\n";
echo "IMPORT RECORDS SKIPPED : " . $IMPORTRECORDS_SKIPPED . "\n";
echo "IMPORT RECORDS BUILDPATH FAILED : " . $IMPORTRECORDS_BUILDPATH_FAILED . "\n";
foreach ( $watchfiles as $key => $val) {
  echo "FILE={$key}, VAL={$val}\n";
}
?>
