<?php
/**
 * Created by PhpStorm.
 * User: elliotmoso
 * Date: 23/09/14
 * Time: 12:14
 */

function lvmcloud_ConfigOptions() {

    # Should return an array of the module options for each product - maximum of 24
    $g = file_get_contents("https://cp.lvmcloud.com/api/plans");
    $array = json_decode($g, true);
    $texto .= "ESCOJE PLAN !!:";
    foreach($array as $array2){
        $texto .= ",".$array2['id']."-".$array2['name'];
    }
    $configarray = array(
        "PLAN" => array( "Type" => "dropdown", "Options" => $texto ),
    );

    return $configarray;

}
function lvmcloud_CreateAccount($params) {

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
    $plan = explode("|", $params["configoptions"]["plan_id"]);
    $plan_id = $plan[0];
    $template = explode("|", $params["configoptions"]["template_id"]);
    $template_id = $template[0];
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL,"https://cp.lvmcloud.com/api/instance");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Token:'.$params['serverusername'],
            'User-TokenKey:'.$params['serverpassword']));
    curl_setopt($ch, CURLOPT_POSTFIELDS,
        "localization_id=1&plan_id=".$plan_id."&template_id=".$template_id."");

    // in real life you should use something like:
    // curl_setopt($ch, CURLOPT_POSTFIELDS,
    //          http_build_query(array('postvar1' => 'value1')));

    // receive server response ...
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec ($ch);

    curl_close ($ch);
    $array = json_decode($server_output, true);

    $successful=false;
    if(isset($array['id'])){
        $successful=true;
    }
    // further processing ....

    if ($successful) {
        $result = "success, vmid=".$array['id'];
    } else {
        $result = $array['message'];
    }
    return $result;

}

function lvmcloud_TerminateAccount($params) {

    # Code to perform action goes here...

    if ($successful) {
        $result = "success";
    } else {
        $result = "Error Message Goes Here...";
    }
    return $result;

}

function lvmcloud_SuspendAccount($params) {

    # Code to perform action goes here...

    if ($successful) {
        $result = "success";
    } else {
        $result = "Error Message Goes Here...";
    }
    return $result;

}

function lvmcloud_UnsuspendAccount($params) {

    # Code to perform action goes here...

    if ($successful) {
        $result = "success";
    } else {
        $result = "Error Message Goes Here...";
    }
    return $result;

}

function lvmcloud_ChangePassword($params) {

    # Code to perform action goes here...

    if ($successful) {
        $result = "success";
    } else {
        $result = "Error Message Goes Here...";
    }
    return $result;

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

    $code = '<form action="http://cp.lvmcloud.com/" method="post" target="_blank">
<input type="submit" value="Entrar al CP" />
</form><form action="/modules/servers/lvmcloud/update_templates.php" method="post" target="_blank">
<input type="submit" value="Actualizar Templates" />
</form>';
    return $code;

}

function lvmcloud_LoginLink($params) {

    echo '<a href="http://cp.lvmcloud.com/" target=\"_blank\" style=\"color:#cc0000\">Entrar al panel</a>';

}

function lvmcloud_reboot($params) {

    # Code to perform reboot action goes here...

    if ($successful) {
        $result = "success";
    } else {
        $result = "Error Message Goes Here...";
    }
    return $result;

}

function lvmcloud_start($params) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,'https://cp.lvmcloud.com/api/instance/start/'.$params['customfields']['server_id']);
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Token:'.$params['serverusername'],
            'User-TokenKey:'.$params['serverpassword']
        ));
    // in real life you should use something like:
    // curl_setopt($ch, CURLOPT_POSTFIELDS,
    //          http_build_query(array('postvar1' => 'value1')));

    // receive server response ...
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $server_output = curl_exec($ch);
    $array = json_decode($server_output, true);
    curl_close($ch);
    $successful = true;
    if ($array['instance']['status']=="STARTED") {
        $result = "success";
    } else {
        $result = "Ha ocurrido un error desconocido, contacte con soporte por favor. Gracias.";
    }
    return $result;
}

function lvmcloud_shutdown($params) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,'https://cp.lvmcloud.com/api/instance/stop/'.$params['customfields']['server_id']);
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Token:'.$params['serverusername'],
            'User-TokenKey:'.$params['serverpassword']
        ));
    // in real life you should use something like:
    // curl_setopt($ch, CURLOPT_POSTFIELDS,
    //          http_build_query(array('postvar1' => 'value1')));

    // receive server response ...
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $server_output = curl_exec($ch);
    $array = json_decode($server_output, true);
    curl_close($ch);
    if ($array['instance']['status']=="BUSY") {
        $result = "success";
    } elseif($array['instance']['status']=="STOPPED") {
        $result = "El servidor está parado actualmente.";
    }else{
        $result = "Ha ocurrido un error desconocido, contacte con soporte por favor. Gracias.";
    }
    return $result;

}

function lvmcloud_ClientAreaCustomButtonArray() {
    $buttonarray = array(
        "Encender servidor" => "start",
        "Reiniciar servidor" => "reboot",
        "Apagar servidor" => "shutdown",
        "Aumentar recursos" => "extrapage",
    );
    return $buttonarray;
}

function lvmcloud_AdminCustomButtonArray() {
    $buttonarray = array(
        "Abrir VNC" => "extrapage",
    );
    return $buttonarray;
}

function lvmcloud_extrapage($params) {
    $pagearray = array(
        'templatefile' => 'vnc',
        'breadcrumb' => ' > <a href="#">Example Page</a>',
        'vars' => array(
            'var1' => 'demo1',
            'var2' => 'demo2',
        ),
    );
    return $pagearray;
}

?>