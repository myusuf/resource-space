<?php

class CurlClient
{
    protected $curl;
    protected $body;
    protected $url;
    protected $oauthAccessToken;

    public function __construct($url = '')
    {
        $this->curl = curl_init();
        $this->url  = $url;
    }

    public function send()
    {
        $this->build();

        $response = curl_exec($this->curl);

        curl_close($this->curl);

        return $response;
    }

    public function build()
    {
        // set URL and other appropriate options
        curl_setopt($this->curl, CURLOPT_URL, $this->url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array();
        $headers[] = 'Content-Type: application/json';

        if (!empty($this->oauthAccessToken)) {
            $headers[] = 'Authorization: Bearer ' . $this->oauthAccessToken;
        }

        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->body);
    }

    public function reset()
    {
        unset($this->curl, $this->body);
    }

    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    public function setRequestType($request = 'GET')
    {
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $request);
        return $this;
    }

    public function setOauthAccessToken($token)
    {
        $this->oauthAccessToken = $token;
        return $this;
    }
}
