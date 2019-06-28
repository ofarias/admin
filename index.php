<?php
session_start();
date_default_timezone_set('America/Mexico_City');
require_once('app/controller/controller.php');

$controller = new pegaso_controller;

if(isset($_GET['action'])){
$action = $_GET['action'];
}else{
	$action = '';
}
//exit(print_r($_POST));
//print_r($_POST);
if (isset($_POST['usuario'])){
	$controller->InsertaUsuarioN($_POST['usuario'], $_POST['contrasena'], $_POST['email'], $_POST['rol'], $_POST['letra'], $_POST['nombre'], $_POST['numletras'], $_POST['paterno'],$_POST['materno']);	
}elseif (isset($_POST['cambioSenia'])){
	$nuevaSenia=$_POST['nuevaSenia'];
	$actual = $_POST['actualSenia'];
	$usuario=$_POST['u'];
	$controller->cambioSenia($nuevaSenia, $actual, $usuario );
}elseif(isset($_POST['user']) && isset($_POST['contra'])){
	$controller->LoginA(strtolower($_POST['user']), $_POST['contra']);
}elseif(isset($_POST['altaempresa'])){
	$controller->aempresa($_POST['empresa'], $_POST['usuarios'], isset($_POST['coi'])? $_POST['coi']:0, isset($_POST['noi'])? $_POST['noi']:0, isset($_POST['sae'])? $_POST['sae']:0, $_POST['bd_coi'], $_POST['tim'], $_POST['rfc'],$_POST['cveSat'], $_POST['rtaLog'], $_POST['bd'], $_POST['serverCOI']);
}elseif (isset($_POST['asociaUsuario'])) {
	$res=$controller->asociaUsuario($_POST['ide'], $_POST['idu']);
	echo json_encode($res);
	exit();
}elseif (isset($_POST['cambiaFecha'])) {
	$res=$controller->cambiaFecha($_POST['ide'], $_POST['fecha']);
	echo json_encode($res);
	exit();
}elseif (isset($_POST['cambiaModo'])) {
	$res=$controller->cambiaModo($_POST['ide'], $_POST['cambiaModo']);
	echo json_encode($res);
	exit();
}elseif(isset($_POST['cambioUsr'])){
	$res=$controller->cambioUsr($_POST['ide'], $_POST['idu'], $_POST['tipo']);
	echo json_encode($res);
	exit();
}elseif (isset($_POST['acomodar'])){
	$res=$controller->acomodar();
	echo json_encode($res);
	exit();
}
else{switch ($_GET['action']){
	//case 'inicio':
	//	$controller->Login();
	//	break;
	case 'login':
		$controller->Login();
		break;
	case 'salir':
        $controller->salir();
        header('Location: index.php?action=login');
        break;
	case 'loginC':
		$_SESSION['empresa']=$_GET['empresa'];
		$controller->LoginA($_SESSION['usuario'], $_SESSION['contra']);
		break;
	case 'CambiarSenia':
		$controller->CambiarSenia();
		break;
	case 'madmin':
		$controller->MenuAdmin();
		break;
	case 'AltaEmpresa':
		$controller->AltaEmpresa();
		break;
	case 'usXemp':
		$controller->usXemp($_GET['ide']);
		break;
	default: 
	header('Location: index.php?action=login');
	break;
	}

}
?>