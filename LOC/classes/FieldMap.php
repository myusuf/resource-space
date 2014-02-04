<?php

Class FieldMap{
    private $fieldMap = array(
        'uuid'=>77,
        'externalId'=>84,
        'contributorId'=>84,
        'siteId'=> 82,
        'contributorId'=> 95,
        'longDescription'=>81,
        'shortDescription'=>80,
        'longName'=>79,
        'shortName'=>78,
        'thumbnailUrl'=>86,
        'backgroundUrl'=>87,
        'ccUrl'=> 88,
        'duration'=>89,
        'language'=>90,
        'aspectRatio'=>91,
        'canEmbed'=>92,
        'canDownload'=>93,
        'isPubished'=>94,
        'mediaType'=>85,
        'createDate'=>12,
        'date'=>12
        
        
    );
    private $mediaTypeMap = array(
        'A'=>4,
        'V'=>3,
       
    );
    
    /**
     * 
     * @param string $field
     * @return int or null
     */
    public function getRsFieldId($field) {
        $fieldMap = $this->fieldMap;
        if(isset($fieldMap[$field])) {
            return $fieldMap[$field];
        }
        return null;
    }
    /**
     * 
     * @return array FieldMap
     */
    public function  getFieldMap() {
        return $this->fieldMap;
       
    }
    
    /**
     * 
     * @return array MediaTypeMap
     */
    public function getMediaTypeMap() {
         return $this->mediaTypeMap;
    }
    /**
     * 
     * @param type $mediaType
     * @return null
     */
    public function getMediaTypeId($mediaType) {
        $mediaTypeMap = $this->getMediaTypeMap();
        echo var_dump($mediaTypeMap);
        if(isset($mediaTypeMap[$mediaType])) {
            //echo "HHERE I AM\n";
            return $mediaTypeMap[$mediaType];
        }
        return null;
    }
    
}