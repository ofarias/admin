<?php
session_start();
require_once('app/controller/controller.php');
$controller = new pegaso_controller;
$target_dir = "C:\\xampp\\htdocs\\ftc\\app\\views\\images\\Logos\\";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$fileName=basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
$ide=$_POST['ide'];
//exit('Se intenta subir el archivo: '.$fileName. ' a la ruta '. $target_dir .' para la empresa '.$ide);
if ($_FILES["fileToUpload"]["size"] > ((1024*1024)*20)) {
    echo "El archivo dede medir menos de 20 MB.";
    $uploadOk = 0;
}else{
    if ((strtoupper($imageFileType) != ("PNG") and strtoupper($imageFileType) != ("JPG")) ){
        echo "<br/><p>El Archivo que intenta cargar, ya existen en el Sistema, se intenta subir un duplicado </p>";
        echo "<p> o el archivo no es valido; solo se pueden subir arvhivos PNG y JPG. </p>";
        $controller->Empresas();
    }else{
        if(move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            echo "El Archivo: ". basename( $_FILES["fileToUpload"]["name"]). " se ha cargado.<p>";
            $tipo = 'ok';
            $cambio=$controller->cargaLogo($fileName, $ide);
        } else {
            echo "Ocurrio un problema al subir su archivo, favor de revisarlo.";
        }
            echo 'Archivo: '.$target_file;
    }
}
?>