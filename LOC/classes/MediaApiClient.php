<?php
//include('../plugins/mediaapi/stdlib.php');
//echo "realpath: " .  realpath('../../../plugins/mediaapi/stdlib.php'); die;
Class MediaApiClient {
	private $url;
	
	
        private $access_token =null;
        private $item_count=0;
        private $page_count=0;
        private $item_count_perpage = 0;

	public function __construct($url) {
            $this->url = $url;
           
		

	}
        
        /**
         * 
         * @return type
         */
        public function getItemCount() {
            return $this->item_count;
        }
        /**
         * 
         * @return type
         */
        public function getPageCount() {
            return $this->page_count;
        }
        /**
         * 
         */
        public function getItemCountPerPage() {
            return $this->item_count_perpage;
    
    }
    
    
    public function getHeaders() {
        
    }

        /**
         * 
         * @param type $page
         * @return type
         */
	public function getMedia($params) {
            if(!$this->access_token) {
                $access_token =  mediaapi_get_accesstoken();
                $this->access_token = $access_token;
            } 
            
            if(isset($params['page'])) {
            $url = "$this->url" . "/page/" . $params['page'];
            } elseif(isset($params['uuid'])) {
                $url = $this->url . "/" .$params['uuid'];
            } else {
                $url = $this->url;
            }
            $header = array('Content-Type: application/json', "Authorization: Bearer " .$access_token);
            $process = curl_init();
            curl_setopt($process, CURLOPT_HEADER, true);
            curl_setopt($process, CURLOPT_HTTPHEADER, $header);
            curl_setopt($process,CURLOPT_URL, $url);
            curl_setopt($process, CURLOPT_RETURNTRANSFER,TRUE);
            
            
            $result = curl_exec($process);
            $header_size = curl_getinfo($process, CURLINFO_HEADER_SIZE);
           
            $headers = $this->get_headers_from_curl_response($result);
            $body = substr($result, $header_size);
           
         
            $this->item_count = $headers["item-count"];
            $this->page_count = $headers["page-count"];
            $this->item_count_perpage = $headers['item-count-per-page'];
            curl_close($process);
            return $body;
            
	
        }
        
       
        
function get_headers_from_curl_response($response)
{
    $headers = array();
    $header_text = substr($response, 0, strpos($response, "\r\n\r\n"));

    foreach (explode("\r\n", $header_text) as $i => $line)
        if ($i === 0)
            $headers['http_code'] = $line;
        else
        {
            list ($key, $value) = explode(': ', $line);

            $headers[$key] = $value;
        }

    return $headers;
}
    
}


