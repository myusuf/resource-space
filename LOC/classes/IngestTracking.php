<?php

class IngestTracking {
    private $date;
    private $id;
    private $total_number_of_records_processed;
    private $last_page_number_processed;
    
    public function __construct() {
        $this->loadData();
    }
    
    public function loadData() {
      $result =   sql_query("SELECT * FROM ingest_tracking ORDER BY id DESC LIMIT 1");
    
      $this->id = $result[0]["ID"];
      $this->date = $result[0]["date"];
      $this->total_number_of_records_processed = $result[0]["total_number_of_records_processed"];
      $this->last_page_number_processed = $result[0]["last_page_number_processed"];
      
    }
    
    public function writeData($data) {
        $str= "total_number_of_records_processed='{$data['total_number_of_records_processed']}', ";
        $str.= "last_page_number_processed='{$data['last_page_number_processed']}'";
   
        
        sql_query("INSERT INTO ingest_tracking SET " . $str . "
               ON DUPLICATE KEY UPDATE " . $str);
 
        
    }
    
    public function updateStatus($data) {
        $str = "status=" . "'" . mysql_escape_string($data['status']) . "'";
        $where = 'ID=' . $data["ID"];
        $sql = "update ingest_tracking set " . $str . "Where " . $where; 
        sql_query($sql);
    }
    /**
     * 
     * @return type
     */
    public function getId() {
        return $this->id;
    }
    /**
     * 
     * @return type
     */
    public function getTotalNumberOfRecordsProcessed() {
        return $this->total_number_of_records_processed;
    }
    /**
     * 
     * @return type
     */
    public function getLastPageNumberProcessed() {
        return $this->last_page_number_processed;
    }
}

