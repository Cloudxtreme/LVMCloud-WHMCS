<?php
@include_once("../../../configuration.php");
include_once("RESTClient.php");

mysql_connect($db_host,$db_username,$db_password);
mysql_select_db($db_name);

$MsgOK = "You have successfully updated the information for the selected plans.";
$MsgKO = "An error when trying to update the data has occurred. <br>MySQL says: <br> ". $err;

function apiCall($url, $data, $method, $usertoken, $tokenkey){

    $rtn=RESTClient::init($url)
        ->params($data)
        ->headers(array(
            'User-Token:'.$usertoken,
            'User-TokenKey:'.$tokenkey))
        ->$method();

    $response = json_decode($rtn, true);
    unset($rtn);

    return $response;
}

function apiHeaders($usertoken, $tokenkey){
    return array("http"=>array("ignore_errors"=>true,"header"=>array("User-Token:".$usertoken, "User-TokenKey:" . $tokenkey)));
}

function doing($what){
    echo '<div id="pBox"><i class="fa fa-info-circle"></i> Updating: ' . $what . "</div>";
}

function updateTemplates(){
    global $MsgOK;
    global $MsgKO;

    doing("templates");
    global $headers;
    $template_txt="";
    $APICall = file_get_contents("https://cp.lvmcloud.com/api/template/public/", false, stream_context_create($headers));
    $js = json_decode($APICall, true);

    if(count($js) <= 0){
        die("API Response:<br>" . print_r($js, true));
    }

    foreach($js as $array2){
        $template_txt .= $array2['template']['id']."|".$array2['template']['name'].",";
    }

    $template_txt = substr($template_txt, 0, -1);

    foreach($_POST['check'] as $plan){
        //Miramos si existe el custom field
        $cf = mysql_num_rows(
            mysql_query("SELECT * FROM tblcustomfields WHERE relid='$plan' AND fieldname='Sistema Operativo'")
        );

        if($cf == 0){
            $qrr = mysql_query("INSERT INTO tblcustomfields (type, fieldoptions, relid, fieldname, showorder, fieldtype, required) VALUES('product', '$template_txt', '$plan', 'Sistema Operativo', 'on', 'dropdown', 'on')");
        }else{
            $qrr = mysql_query("UPDATE tblcustomfields SET fieldoptions = '".$template_txt."', required='on', showorder='on' WHERE relid='$plan' AND fieldname='Sistema Operativo'");
        }

        $err = mysql_error();

        if(empty($err)){
            echo "<div id = 'pBox'>".'<i class="fa fa-check-circle"></i> '.$MsgOK.'<hr></div>';
        }else{
            echo "<div id = 'pBox'>".'<i class="fa fa-exclamation-triangle"></i> '.$MsgKO.'<hr></div>';
        }

    }
}

function updateLocalizations(){
    global $MsgOK;
    global $MsgKO;

    doing("localizations");
    global $headers;
    $localisation_txt="";
    $APICall = file_get_contents("https://cp.lvmcloud.com/api/localization/", false, stream_context_create($headers));
    $js = json_decode($APICall, true);

    if(count($js) <= 0){
        die("API Response:<br>" . print_r($js, true));
    }

    foreach($js as $array2){
        $array2['name'] = str_replace(",","-",$array2['name']);
        $localisation_txt .= $array2['id']."|".$array2['name'].",";
    }

    $localisation_txt = substr($localisation_txt, 0, -1);

    foreach($_POST['check'] as $plan){
        //Miramos si existe el custom field
        $cf = mysql_num_rows(
            mysql_query("SELECT * FROM tblcustomfields WHERE relid='$plan' AND fieldname='Localizacion'")
        );

        if($cf == 0){
            $qrr = mysql_query("INSERT INTO tblcustomfields (type, fieldoptions, relid, fieldname, showorder, fieldtype, required) VALUES('product', '$localisation_txt', '$plan', 'Localizacion', 'on', 'dropdown', 'on')");
        }else{
            $qrr = mysql_query("UPDATE tblcustomfields SET fieldoptions = '".$localisation_txt."', required='on', showorder='on' WHERE relid='$plan' AND fieldname='Localizacion'");
        }

        $err = mysql_error();
        if(empty($err)){
            echo "<div id = 'pBox'>".'<i class="fa fa-check-circle"></i> '.$MsgOK.'<hr></div>';
        }else{
            echo "<div id = 'pBox'>".'<i class="fa fa-exclamation-triangle"></i> '.$MsgKO.'<hr></div>';
        }

    }
}

function createVmIdField(){
    global $MsgOK;
    global $MsgKO;
    
    doing("VmId");
    foreach($_POST['check'] as $plan){
        //Miramos si existe el custom field
        $cf = mysql_num_rows(
            mysql_query("SELECT * FROM tblcustomfields WHERE relid='$plan' AND fieldname='VmId'")
        );

        if($cf == 0){
            $qrr = mysql_query("INSERT INTO tblcustomfields (type, fieldoptions, relid, fieldname, fieldtype) VALUES('product', '', '$plan', 'VmId', 'text')");
        }else{
            $qrr = mysql_query("UPDATE tblcustomfields SET required='', showorder='', fieldtype='text' WHERE relid='$plan' AND fieldname='VmId'");
        }

        $err = mysql_error();
        if(empty($err)){
            echo "<div id = 'pBox'>".'<i class="fa fa-check-circle"></i> '.$MsgOK.'<hr></div>';
        }else{
            echo "<div id = 'pBox'>".'<i class="fa fa-exclamation-triangle"></i> '.$MsgKO.'<hr></div>';
        }

    }
}