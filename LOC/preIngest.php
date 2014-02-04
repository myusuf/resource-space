<?php
/**
 * preprocessing script for RS Static sync
 * it reads content from mediaServices schema using mediaApi
 * and puts physical mediafiles with ther metada in a certain directory structure
 * that staticsync script expects
 * 
 * @author Muhibo Yusuf <myus@loc.gov>
 * 
 */
include("rspaceapi.php");

include ("classes/FieldMap.php");
include("classes/MediaApiClient.php");



/**
 * queries the RS schema to fetch the media already ingested.
 * @return array
 */
function getIngestedResources()  {
    $results = sql_query("select r.ref, rd.value from resource r LEFT JOIN resource_data rd on r.ref = rd.resource where rd.resource_type_field = 77");

    $map = array();
    foreach ( $results as $result ) {
        $map[$result['value']] = $result['ref'];
    }
    return $map;
}
/**
 * 
 * @param array Media
 * @return boolean
 */
function isValid($media) {
    if(!empty($media["derivatives"]) && isset($media['uuid'])) {
        return true;
    }
    return false;
}
/**
 * 
 * @param int $page
 * @return array
 */

function getMedia($page) {
    $url ="http://mediaapi.local/api/media";
    $method = 'get';
    $params = array('page'=>$page);
    $mediaApiClient = new MediaApiClient($url, $method, $params);
   

    return json_decode($mediaApiClient->getMedia($params), true);  
}
/**
 * 
 * @param type $arr
 * @param type $absoluteFileName
 * Writes json metadata to a file
 */
function writeMetadata($arr, $absoluteFileName) {
    $metaHandle = fopen($absoluteFileName, "w");
    fwrite($metaHandle, json_encode($arr));
    fclose($metaHandle);
}





/**
 * set up directory paths for data transfer
 */
$dataDir = "/storage/www/resourcespace/LOC/data";
$SourcemediaFileBaseDir = '/ingest';
$syncDirectory = '/ingest/resourcespace/mediaFiles';
$logfile = fopen("$dataDir/preSync.log", "wb");
$existingResources =  getIngestedResources();

$mediaArray = getMedia(1);
//we will not be loading records found in this hash

$now = date("Y-m-d H:i:s");
$logfile = fopen("$dataDir/preSync.log", "wb");
fwrite($logfile,"Starting preIngest");

$fn = fopen("{$dataDir}/status.txt","w");


$count =0;
foreach ($mediaArray as $media) {
   if(isValid($media)) {
     $uuid = $media['uuid'];
     if(isset($existingResources[$uuid])) {
         echo "$uuid already exists in RS skipping\n";
         continue;
     }
     $count++;
     $firstDerivativeFileName =$media["derivatives"][0]["fileName"] . ".". $media["derivatives"][0]["fileExtension"];
     $fdFullPathName = $SourcemediaFileBaseDir . "/" . $media['derivatives'][0]['filePath'] . "/" . $media["derivatives"][0]["fileName"] . ".". $media["derivatives"][0]["fileExtension"];
     
     $destinationPath = "$syncDirectory/$uuid";
    //make directory for this media
     if (!is_dir($destinationPath)) {
        mkdir($destinationPath, 0777, TRUE);

    } 
    $metaDataFile = $destinationPath . "/" . $uuid . ".json";
    //write metadata
    writeMetadata($media, $metaDataFile);
    
    
     
    //copy main derivative
     if(file_exists($fdFullPathName)) {
         copy($fdFullPathName, $destinationPath . "/$firstDerivativeFileName"); 
     } else {
         echo "file: $fdFullPathName doesn't exist";
     }
     
     //copy all derivatives to the alternativeFile directory
     $derivativeDir = $destinationPath . "/" . $firstDerivativeFileName . "_derivatives";
      if (!is_dir($derivativeDir)) {
        mkdir($derivativeDir, 0777, TRUE);

    } 
    foreach($media["derivatives"] as $derivative) {
        $sourceDerivative = $SourcemediaFileBaseDir . "/" .$derivative["filePath"] . "/" . $derivative["fileName"] . "." . $derivative["fileExtension"];
        $destinationDerivative = $derivativeDir . "/" . $derivative['fileName'] . "." . $derivative['fileExtension'];
        
        if(file_exists($sourceDerivative)) {
            $derivativeMetataFile = $destinationDerivative . ".json";
            copy($sourceDerivative, $destinationDerivative);
            writeMetadata($derivative, $derivativeMetataFile);
            
        } else {
            echo "Derivative: $sourceDerivative doesn't exist";
        }
    }
   }
}
    
        

