<?php
/**
 * Created by PhpStorm.
 * User: elliotmoso
 * Date: 23/09/14
 * Time: 17:18
 */

include_once("RESTClient.php");
echo RESTClient::init("http://kumo1.brutalsys.com/api")->params(array('name'=>'elliot'))->post()->getBody();