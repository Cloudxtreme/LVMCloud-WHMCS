<?php
include("functions.php");

function lvmcloud_ConfigOptions() {
    global $params;
	$g = file_get_contents("https://cp.lvmcloud.com/api/plans");
	$array = json_decode($g, true);
	$texto = "<b>Selecciona un plan</b>";
	foreach($array as $array2){
	$texto .= ",".$array2['description'];
	}
    $configarray = array(
	 "Plan: " => array( "Type" => "dropdown", "Options" => $texto ),
	 );
	return $configarray;
}


function lvmcloud_CreateAccount($params) {
    //Definimos variables de API
    $url = "https://api.lvmcloud.com/api/instance";
    $method = "POST";


    # ** The variables listed below are passed into all module functions **

    $serviceid = $params["serviceid"]; # Unique ID of the product/service in the WHMCS Database
    $pid = $params["pid"]; # Product/Service ID
    $producttype = $params["producttype"]; # Product Type: hostingaccount, reselleraccount, server or other
    $domain = $params["domain"];
	$username = $params["username"];
	$password = $params["password"];
    $clientsdetails = $params["clientsdetails"]; # Array of clients details - firstname, lastname, email, country, etc...
    $customfields = $params["customfields"]; # Array of custom field values for the product
    $configoptions = $params["configoptions"]; # Array of configurable option values for the product

    # Product module option settings from ConfigOptions array above
    $configoption1 = $params["configoption1"];
    $configoption2 = $params["configoption2"];
    $configoption3 = $params["configoption3"];
    $configoption4 = $params["configoption4"];

    # Additional variables if the product/service is linked to a server
    $server = $params["server"]; # True if linked to a server
    $serverid = $params["serverid"];
    $serverip = $params["serverip"];
    $serverusername = $params["serverusername"];
    $serverpassword = $params["serverpassword"];
    $serveraccesshash = $params["serveraccesshash"];
    $serversecure = $params["serversecure"]; # If set, SSL Mode is enabled in the server config
	$plan = $configoption1;
    $template = $params["customfields"]["Sistema Operativo"];
    $localization = $params["customfields"]["Localizacion"];

    $data = array("localization_id" => $localization, "plan_id" => $plan, "template_id"=>$template, "extra_ram"=>0, "extra_cpu"=>0);


	$result = apiCall($url, $data, $method, $serverusername, $serverpassword);

    if($result['instance']['status'] == "CREATING"){

        $vmid = $result['instance']['id'];
        $ipaddr = $result['instance']['ips']['0']['ip'];
        $vmpasswd = $result['instance']['password'];


        //Get admin list
        $AdminsQuery = mysql_query("SELECT * FROM tbladmins WHERE roleid='1'");
        $Admin = mysql_fetch_object($AdminsQuery);

        $command = "modulechangepw";
        $adminuser = $Admin->username;
        $values["serviceid"] = $serviceid;
        $values["servicepassword"] = $vmpasswd;
        $results = localAPI($command,$values,$adminuser);

        if($results['result'] != "success"){
            return "Error writing password:  " . print_r($results, true);
        }

        $table = "tblhosting";
        $array = array("dedicatedip"=>$ipaddr, "domain"=>$result['instance']['name']);
        $where = array("id"=>$serviceid);
        update_query($table,$array,$where);

        $SQL = mysql_query("SELECT * FROM tblcustomfields WHERE relid='".$pid."'");

        //Ahora buscamos los optionalfields de este pid
        while($fields = mysql_fetch_object($SQL)){
            if($fields->fieldname == "VmId"){
                $vmid_field = $fields->id;
            }
        }

        $result = "success";

        //Insertamos la VmId en la MySQL
           if(mysql_num_rows(mysql_query("SELECT * FROM tblcustomfieldsvalues WHERE fieldid='$vmid_field' and relid='$serviceid'")) == 0){
               $vmidQ = mysql_query("INSERT into tblcustomfieldsvalues (fieldid, relid, value) VALUES('$vmid_field','$serviceid','$vmid')");
           }else{
               $vmidQ = mysql_query("UPDATE tblcustomfieldsvalues SET fieldid='$vmid_field', relid='$serviceid', value='$vmid'  WHERE fieldid='$vmid_field' and relid='$serviceid'");
           }

        }else{
            if($result['code'] != null){
                $result = $result['code'] . ": " . $result['message'];
            }
        }

       return $result;
}

function lvmcloud_checkVmStatus($vmid, $tokenuser, $tokenkey){
    $url = "https://api.lvmcloud.com/api/instance/".$vmid;
    $result = apiCall($url, null, "GET", $tokenuser, $tokenkey);
    return $result['instance']['status'];
}

function lvmcloud_TerminateAccount($params) {
    $s=0;
    lvmcloud_shutdown($params);
    $vmid = $params['customfields']['VmId'];

    while(true){
        $s++;
        if(lvmcloud_checkVmStatus($vmid, $params['serverusername'], $params['serverpassword']) == "STOPPED"){
            break;
        }else{
            if($s>=25) return "Cannot shutdown VM ".lvmcloud_checkVmStatus($vmid, $params['serverusername'], $params['serverpassword']) ;
        }
        sleep(1);
    }

    $url = "https://api.lvmcloud.com/api/instance/".$vmid;
    $method = "DELETE";
    $result = apiCall($url, null, $method, $params['serverusername'], $params['serverpassword']);
    return (lvmcloud_checkVmStatus($vmid, $params['serverusername'], $params['serverpassword']) == "BUSY" ? "success" : "fail");
}

function lvmcloud_SuspendAccount($params) {
    $s=0;
    lvmcloud_shutdown($params);
    $vmid = $params['customfields']['VmId'];

    while(true){
        $s++;
        if(lvmcloud_checkVmStatus($vmid, $params['serverusername'], $params['serverpassword']) == "STOPPED"){
            break;
        }else{
            if($s>=25) return "Cannot shutdown VM ".lvmcloud_checkVmStatus($vmid, $params['serverusername'], $params['serverpassword']) ;
        }
        sleep(1);
    }

    $url = "https://api.lvmcloud.com/api/instance/archivate/".$vmid;
    $method = "PUT";
    $result = apiCall($url, null, $method, $params['serverusername'], $params['serverpassword']);
    return (lvmcloud_checkVmStatus($vmid, $params['serverusername'], $params['serverpassword']) == "BUSY" ? "success" : "fail");
}

function lvmcloud_UnsuspendAccount($params) {
    $s=0;
    $vmid = $params['customfields']['VmId'];

     if(lvmcloud_checkVmStatus($vmid, $params['serverusername'], $params['serverpassword']) != "ARCHIVED"){
         return "This VM is not archivated, currrent status is " . lvmcloud_checkVmStatus($vmid, $params['serverusername'], $params['serverpassword']);
     }


    $url = "https://api.lvmcloud.com/api/instance/unarchive/".$vmid;
    $method = "PUT";
    $result = apiCall($url, null, $method, $params['serverusername'], $params['serverpassword']);
    return (lvmcloud_checkVmStatus($vmid, $params['serverusername'], $params['serverpassword']) == "BUSY" ? "success" : "fail");
}

function lvmcloud_ChangePassword($params) {
   /* $s=0;
    lvmcloud_shutdown($params);
    $vmid = $params['customfields']['VmId'];

    while(true){
        $s++;
        if(lvmcloud_checkVmStatus($vmid, $params['serverusername'], $params['serverpassword']) == "STOPPED"){
            break;
        }else{
            if($s>=25) return "Cannot shutdown VM ".lvmcloud_checkVmStatus($vmid, $params['serverusername'], $params['serverpassword']) ;
        }
        sleep(1);
    }*/

    return "success";
}

function lvmcloud_ClientArea($params) {

    # Output can be returned like this, or defined via a clientarea.tpl template file (see docs for more info)

	$code = '<form action="lvmcloudvnc.php" method="post" target="_blank">
<input type="hidden" name="server_id" value="'.$params["customfields"]["server_id"].'" />
<input type="hidden" name="client_id" value="'.$params["clientsdetails"]["id"].":".$params["serviceid"].'" />
<input type="submit" value="Abrir VNC"/>
</form>';
	return $code;

}



function lvmcloud_AdminLink($params) {
	$code = '<form action="/modules/servers/lvmcloud/update_resources.php" method="post" target="_blank">
<input type="hidden" name="tokenuser" value="'.$params['serverusername'].'">
<input type="hidden" name="tokenkey" value="'.$params['serverpassword'].'">

<input type="submit" value="Update internal information" />
</form>';
	return $code;

}

function lvmcloud_LoginLink($params) {

	//echo '<a href="http://cp.lvmcloud.com/" target=\"_blank\" style=\"color:#cc0000\">Entrar al panel</a>';

}

function lvmcloud_reboot($params) {
    $vmid = $params['customfields']['VmId'];
    if(lvmcloud_checkVmStatus($vmid, $params['serverusername'], $params['serverpassword']) == "STOPPED") return "You can't restart this instance because it's stopped.";

    lvmcloud_shutdown($params);
    sleep(3);
    lvmcloud_start($params);
}

function lvmcloud_start($params) {
    $vmid = $params['customfields']['VmId'];
    if(lvmcloud_checkVmStatus($vmid, $params['serverusername'], $params['serverpassword']) == "STARTED") return "This instance is already started.";

    $url = "https://api.lvmcloud.com/api/instance/start/".$vmid;
    $method = "PUT";
    $result = apiCall($url, null, $method, $params['serverusername'], $params['serverpassword']);
    return (lvmcloud_checkVmStatus($vmid, $params['serverusername'], $params['serverpassword']) == "STARTED" ? "success" : "An unknown error has occurred, please contact support. Thank you.");
}

function lvmcloud_shutdown($params) {
    $vmid = $params['customfields']['VmId'];
    if(lvmcloud_checkVmStatus($vmid, $params['serverusername'], $params['serverpassword']) == "STOPPED") return "This instance is already stopped.";

    $url = "https://api.lvmcloud.com/api/instance/stop/".$vmid;
    $method = "PUT";
    $result = apiCall($url, null, $method, $params['serverusername'], $params['serverpassword']);
    return (lvmcloud_checkVmStatus($vmid, $params['serverusername'], $params['serverpassword']) == "BUSY" ? "success" : "An unknown error has occurred, please contact support. Thank you. Current status is: " . lvmcloud_checkVmStatus($vmid, $params['serverusername'], $params['serverpassword']) );

	return $result;

}

function lvmcloud_ClientAreaCustomButtonArray() {
    $buttonarray = array(
	 "Start Instance" => "start",
     "Stop Instance" => "shutdown",
     "Restart Instance" => "reboot",
	 "Open VNC" => "encendervnc",
	);
	return $buttonarray;
}

function lvmcloud_AdminCustomButtonArray() {
return lvmcloud_ClientAreaCustomButtonArray();
}

function lvmcloud_encendervnc($params) {
    $vmid = $params['customfields']['VmId'];
    $url = "https://api.lvmcloud.com/api/instance/vnc/".$vmid;
    $method = "PUT";
    $result = apiCall($url, null, $method, $params['serverusername'], $params['serverpassword']);

	$hostvnc = $result['noVNC']['host'];
	$portvnc = $result['noVNC']['port'];
	$passwordvnc = $result['noVNC']['password'];
	$pathvnc = $result['noVNC']['uri'];
    $pagearray = array(
     'templatefile' => 'vnc',
     'breadcrumb' => ' > <a href="#">VNC</a>',
     'vars' => array(
        'hostvnc' => $hostvnc,
        'portvnc' => $portvnc,
		'passwordvnc' => $passwordvnc,
		'pathvnc' => $pathvnc
     ),
    );
	return $pagearray;
}

?>