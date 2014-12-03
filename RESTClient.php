<?php
/**
 * Created by PhpStorm.
 * User: elliotmoso
 * Date: 23/09/14
 * Time: 12:32
 */

class RESTClient {

    private $href=null;
    private $params=null;
    private $headers=null;
    private $curlCH=null;
    private $ssl_verify_host=false;
    private $ssl_verify_peer=false;

    private function __construct($href,$timeout=20,$headers=null,$params=null){
        $this->href=$href;
        $this->headers=$headers;
        $this->params=$params;
        $this->curlCH=curl_init();
        curl_setopt($this->curlCH, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($this->curlCH, CURLOPT_URL,$this->href);
        curl_setopt($this->curlCH, CURLOPT_SSL_VERIFYHOST, $this->ssl_verify_host);
        curl_setopt($this->curlCH, CURLOPT_SSL_VERIFYPEER, $this->ssl_verify_peer);
    }

    public static function init($href){
        return new RESTClient($href);
    }
    public function headers($headers){
        $this->headers=$headers;
        if(!is_null($this->headers)){
            curl_setopt($this->curlCH, CURLOPT_HTTPHEADER, $this->headers);
        }
        return $this;
    }
    public function params($params){
        $this->params=$params;
        if(!is_null($this->params)){
            if(!is_array($this->params)){
                $this->params=array($this->params);
            }
            curl_setopt($this->curlCH, CURLOPT_POSTFIELDS,http_build_query($this->params));
        }
        return $this;
    }


    public function get(){
        if(is_null($this->href)){
            throw new BadMethodCallException();
        }
        return new RESTResponse($this->curlCH);
    }
    public function put(){
        if(is_null($this->href)){
            throw new BadMethodCallException();
        }
        curl_setopt($this->curlCH, CURLOPT_CUSTOMREQUEST, "PUT");
        return new RESTResponse($this->curlCH);
    }
    public function post(){
        if(is_null($this->href)){
            throw new BadMethodCallException();
        }
        curl_setopt($this->curlCH, CURLOPT_CUSTOMREQUEST, "POST");
        return new RESTResponse($this->curlCH);

    }
    public function delete(){
        if(is_null($this->href)){
            throw new BadMethodCallException();
        }
        curl_setopt($this->curlCH, CURLOPT_CUSTOMREQUEST, "DELETE");
        return new RESTResponse($this->curlCH);
    }
}

class RESTResponse{
    private $curlCH;
    private $contentType;
    private $code;
    private $body;
    private $rawbody;

    function __construct($curlCH){
        $this->curlCH=$curlCH;
        curl_setopt($this->curlCH,CURLOPT_HEADER, 0);
        curl_setopt($this->curlCH, CURLOPT_RETURNTRANSFER, true);
        $body=curl_exec($this->curlCH);
        $this->rawbody=$body;
        $this->setCode(curl_getinfo($this->curlCH, CURLINFO_HTTP_CODE));
        $this->contentType = curl_getinfo($this->curlCH, CURLINFO_CONTENT_TYPE);
        curl_close($this->curlCH);
        if(strpos($this->contentType,'json') !== false){
            $this->setBody(json_decode($body,true));
        }elseif(strpos($this->contentType,'xml')!==false){
            $this->setBody($body);

        }else{
            $this->setBody($body);
        }
    }

    public function __toString(){
        return $this->getRawBody();
    }

    /**
     * @param mixed $body
     */
    private function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    public function getRawBody(){
        return $this->rawbody;
    }
    /**
     * @param mixed $code
     */
    private function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }


}