<?php 
require_once('app/model/model.ftc.php'); //importamos el archivo de conexiÃ³n
	$ide=$_GET['ide'];
	$data = new ftc;
	$imagen = $data->fObtenerMime($ide);
	$mime = $imagen['mime'];
	$contenido = $imagen['contenido'];
	header("Content-type:$mime");
	print $contenido; 
