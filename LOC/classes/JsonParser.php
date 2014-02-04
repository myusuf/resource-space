<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class JsonParser {
    private $filename;
    public function __construct($file) {
       $this->filename = $file;
    }
    
    public function getContentArray() {
        
        $string = file_get_contents($this->filename);
        $json_a=json_decode($string,true);
        return $json_a;

    }
}
