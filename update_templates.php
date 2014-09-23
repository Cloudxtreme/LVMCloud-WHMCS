<?php
/**
 * Created by PhpStorm.
 * User: elliotmoso
 * Date: 23/09/14
 * Time: 15:15
 */

include_once("RESTClient.php");

$templates=RESTClient::init("https://cp.lvmcloud.com/api/template")
    ->headers(array(
        'User-Token:'.$params['serverusername'],
        'User-TokenKey:'.$params['serverpassword']))
    ->get()
    ->getBody();
var_dump($templates);