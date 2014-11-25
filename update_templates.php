<?php
require("../../../configuration.php");
mysql_connect($db_host,$db_username,$db_password);
mysql_select_db($db_name);
if(!$_POST['si']&&$_POST['submit']==""){

$qr = mysql_query("SELECT name,id FROM tblproducts WHERE servertype='lvmcloud'");
?>
<?=htmlentities("Seleccione que productos desea actualizar los templates (Solo válido con productos asignados al módulo de LVMCLOUD)");?>
<script src="http://code.jquery.com/jquery-latest.min.js"
        type="text/javascript"></script>
<script>
$(document).ready(function() {
    $('#selecctall').click(function(event) {  //on click 
        if(this.checked) { // check select status
            $('.checkbox1').each(function() { //loop through each checkbox
                this.checked = true;  //select all checkboxes with class "checkbox1"               
            });
        }else{
            $('.checkbox1').each(function() { //loop through each checkbox
                this.checked = false; //deselect all checkboxes with class "checkbox1"                       
            });         
        }
    });

});
 </script>
<form action="" method="POST">
Producto: <br>
<input type="checkbox" id="selecctall"/> Select All<br>
<?php
while($as = mysql_fetch_assoc($qr)){
echo '
'.$as['name'].'
<input type="checkbox" class="checkbox1" name="check[]" value="'.$as['id'].'"/><br>
'; 
}
?>
<input type="hidden" name="si" value="ok" />
<input type="submit" name="submit" value="OK" />
</form>
<?php
}
if($_POST['si']=="ok"&&$_POST['submit']!=""){
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,'https://cp.lvmcloud.com/api/template/public/');
curl_setopt($ch, CURLOPT_POST, 0);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0); 
curl_setopt($ch, CURLOPT_HTTPHEADER, 
array('User-Token:IL1t82R3rPJyV7C9MqzsvNDj5fgKp6','User-TokenKey:Be7NwZPK9ELSrRH3jutqczdvhxfVsp0DibnYTkMX4mAJ21o5lIaQ6CU8OFyW'));

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$server_output = curl_exec($ch);
echo curl_error($ch);
curl_close($ch);
$array = json_decode($server_output, true);
foreach($array as $array2){
	$template_txt .= $array2['template']['id']."|".$array2['template']['name'].",";
}
$template_txt = substr($template_txt, 0, -1);

foreach($_POST['check'] as $plan){
echo "Plan: " . $plan. "<br>";
$qrr = mysql_query("UPDATE tblcustomfields SET fieldoptions = '".$template_txt."' WHERE relid='$plan' AND fieldname='Sistema Operativo'");

if($qrr==TRUE){
echo "OK";
}else{
echo "Un error ha ocurrido, contacte con soporte lvmcloud.";
}
}




}


?>