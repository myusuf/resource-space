<?php

Class MediaApiClient {
	private $url;
	private $method;
	private $params;
        private $access_token;

	public function __construct($url, $method,$params) {
            $this->url = $url;
            $this->method = $method;
            $this->params = $params;
		

	}
/**
 * 
 * @param type $page
 * @return type
 * 
 * @param type $page
 * @return \type
 */
 
   /*
    *  public function doRequest($page=1, $uuid=null) {
    
        switch($this->method) {
            case 'getAll':
                return $this->getMedia($page);
		break;
            case 'getOne':
                return $this->getOneMedia($uuid);
            default:
                echo 'not implemented';
		break;
                }
	}
    * 
    */	

        /**
         * 
         * @param type $page
         * @return type
         */
	public function getMedia($params) {
            $access_token = '804b0e0c08abbab361c645594c0763ef6f3f818e';
            if(isset($params['page'])) {
            $url = "$this->url" . "/page/" . $params['page'];
            } elseif(isset($params['uuid'])) {
                $url = $this->url . "/" .$params['uuid'];
            } else {
                $url = $this->url;
            }
            $header = array('Content-Type: application/json', "Authorization: Bearer " .$access_token);
            $process = curl_init();
            //curl_setopt($process, CURLOPT_HEADER, true);
            curl_setopt($process, CURLOPT_HTTPHEADER, $header);
            curl_setopt($process,CURLOPT_URL, $url);
            curl_setopt($process, CURLOPT_RETURNTRANSFER,TRUE);
            $result = curl_exec($process);
            curl_close($process);
            return $result;
            
	
        }
    public function getAccessToken() {
       $config = $this->getConfigData();
       $content = "grant_type=" . $config['grant_type'] . "&client_id=" . $config['client_id'] . "&username=" . $config['user_name'] . "&password=" . $config['password'] . "&client_secret=" . $config['client_secret'] . "&scope=" . $config['scope'];
       
       $request = new Request();
       $request->setUri($config['url']);
       $request->setMethod('POST');
       $request->setContent($content);
       $client = new Client;
       $adapter = new \Zend\Http\Client\Adapter\Curl();
       $adapter->setOptions(array(
            'curloptions' => array(
                CURLOPT_POST => 1,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_SSL_VERIFYPEER => FALSE,
                CURLOPT_SSL_VERIFYHOST => FALSE,
                CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded')  
            )
        ));
        $client->setAdapter($adapter);
        $response = $client->dispatch($request);
        return $response->getContent();
        }
}


