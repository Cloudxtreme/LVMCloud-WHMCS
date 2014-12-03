<?php session_start();
include("css.php");
include("functions.php");

//Miramos si incluye las variables necesarias
    if($_POST["submit"] != "OK"){
        if(is_null($_POST['tokenkey']) || is_null($_POST['tokenuser'])){
            die("You cannot access directly");
        }else{
            $_SESSION['tokenkey'] = mysql_real_escape_string($_POST['tokenkey']);
            $_SESSION['tokenuser'] = mysql_real_escape_string($_POST['tokenuser']);
        }
    }

//Leemos todas las sesiones y las pasamos a variable
foreach($_SESSION as $var=>$value){
    $$var=$value;
}

//Toca relacionar el token con un servidor
$obj = mysql_fetch_object(mysql_query("SELECT * FROM tblservers WHERE username='$tokenuser'"));

if(is_null($obj->id)){
    die("Cannot relate a token with a valid server");
}else{
    //Creamos los headers
    $headers = apiHeaders($obj->username, $tokenkey);
}

//Comprobamos que el token sea correcto
$Call = file_get_contents("https://api.lvmcloud.com/api", false, stream_context_create($headers));
$js = json_decode($Call, true);

if($js['status'] != "OK"){
    die("API Response:<br>" . print_r($js, true));
}

if($_POST['submit'] != "OK"){

$qr = mysql_query("SELECT name,id FROM tblproducts WHERE servertype='lvmcloud'");
?>

<div id = "selectproduct">Seleccione que productos desea actualizar (solo válido con productos asignados al módulo de LVMCloud)</div>
<br>

<div id = "container">
    <form action="" method="POST">
  <div id = "pbox">
 <input type="checkbox" id="selecctall"/> Select All <hr>
<?php
while($as = mysql_fetch_object($qr)){
echo '<div class="cb-row">
      <input type="checkbox" class="checkbox1" name="check[]" value="'.$as->id.'"/> '.$as->name.'
      </div>';
}
?>
<hr>
<br>

        <input type="submit" name="submit" value="OK" />
</div>
    </form>
<?php
}
    if($_POST['submit'] == "OK"){
    updateTemplates();
    updateLocalizations();
    createVmIdField();
    }
?>
    <div id="pBox"><a href="javascript:window.close();">Cerrar ventana</a></div>
</div>