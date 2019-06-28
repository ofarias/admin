<?php
require_once('app/model/pegaso.model.php');
require_once('app/model/model.ftc.php');
require_once('app/fpdf/fpdf.php');
require_once('app/views/unit/commonts/numbertoletter.php');
require_once('app/model/database.xmlTools.php');
require_once('app/model/db.contabilidad.php');
require_once 'app/model/pegasoqr.php';
require_once('app/model/pegaso.model.recoleccion.php');
require_once('app/model/pegaso.model.cxc.php');
require_once('app/model/facturacion.php');

class pegaso_controller{
	var $contexto_local = "http://SERVIDOR:8081/pegasoFTC/app/";
	var $contexto = "http://SERVIDOR:8081/pegasoFTC/app/";
	
	function Login(){
			$pagina = $this->load_templateL('Login');
			$html = $this->load_page('app/views/modules/m.login.php');
			$pagina = $this->replace_content('/\#CONTENIDO\#/ms', $html, $pagina);
			$this->view_page($pagina);
	}

	function Autocomp(){
		$arr = array('prueba1', 'trata2', 'intento3', 'prueba4', 'prueba5');
		echo json_encode($arr);
		exit;
	}

	function salir(){
		$data= new pegaso;
		$salir=$data->salir();
		return;
	}

	function LoginA($user, $pass){
		$data = new ftc; 
		$usuario = $data->loginMysql($user, $pass);
			if(isset($usuario)){
				$this->Empresas();
			}else{
				$e = "Error en acceso 2, favor de revisar usuario y/o contraseña";
					header('Location: index.php?action=login&e='.urlencode($e)); exit;
			}
	}

	function LoginConta($user, $pass){
		$data= new ftc;
		$usuario = $data->loginMysql($user, $pass);
		$_SESSION['usuario']=$user;
		$_SESSION['contra']=$pass;
		exit(print_r($usuario));
		if(!empty($usuario)){
			foreach ($usuario as $key) {
				$u = $key['usuario'];
			}
			$pagina = $this->load_template('Menu Admin');			
			$html = $this->load_page('app/views/modules/m.adminConta.php');
			ob_start();
			$table=ob_get_clean();
			include 'app/views/modules/m.adminConta.php';
			$pagina = $this->replace_content('/\#CONTENIDO\#/ms', $table, $pagina);
			$this-> view_page($pagina);	
		}else{
			$e = "Favor de Revisar sus datos";
			header('Location: index.php?action=login&e='.urlencode($e)); exit;
		}
	}

	/*Obtiene y carga el template*/
	function load_template($title='Sin Titulo'){
		$pagina = $this->load_page('app/views/master.php');
		$header = $this->load_page('app/views/sections/s.header.php');
		$pagina = $this->replace_content('/\#HEADER\#/ms' ,$header , $pagina);
		$pagina = $this->replace_content('/\#TITLE\#/ms' ,$title , $pagina);		
		return $pagina;
	}
	
	/*Header para login*/
	function load_templateL($title='Sin Titulo'){
		$pagina = $this->load_page('app/views/master.php');
		$header = $this->load_page('app/views/sections/header.php');
		$pagina = $this->replace_content('/\#HEADER\#/ms' ,$header , $pagina);
		$pagina = $this->replace_content('/\#TITLE\#/ms' ,$title , $pagina);		
		return $pagina;
	}

	/*Header para los popup?*/
	function load_template_popup($title='Ferretera Pegaso'){
		$pagina = $this->load_page('app/views/master.php');
		$header = $this->load_page('app/views/sections/s.header2.php');
		$pagina = $this->replace_content('/\#HEADER\#/ms' ,$header , $pagina);
		$pagina = $this->replace_content('/\#TITLE\#/ms' ,$title , $pagina);		
		return $pagina;
	}

	/* METODO QUE CARGA UNA PAGINA DE LA SECCION VIEW Y LA MANTIENE EN MEMORIA
		INPUT
		$page | direccion de la pagina 
		OUTPUT
		STRING | devuelve un string con el codigo html cargado
	*/	
   private function load_page($page){
		return file_get_contents($page);
	}
   
   /* METODO QUE ESCRIBE EL CODIGO PARA QUE SEA VISTO POR EL USUARIO
		INPUT
		$html | codigo html
		OUTPUT
		HTML | codigo html		
	*/
   private function view_page($html){
		echo $html;
	}
   
   /* PARSEA LA PAGINA CON LOS NUEVOS DATOS ANTES DE MOSTRARLA AL USUARIO
		INPUT
		$out | es el codigo html con el que sera reemplazada la etiqueta CONTENIDO
		$pagina | es el codigo html de la pagina que contiene la etiqueta CONTENIDO
		OUTPUT
		HTML 	| cuando realiza el reemplazo devuelve el codigo completo de la pagina
	*/
   private function replace_content($in='/\#CONTENIDO\#/ms', $out,$pagina){
		 return preg_replace($in, $out, $pagina);	 	
	}

	function Inicio(){	
		if(isset($_SESSION['user'])){
			$o = $_SESSION['user'];
			switch($o->USER_ROL){
				case 'administrador':
				$this->MenuAdmin();
				break;
				default:
				$e = "Error en acceso 1, favor de revisar usuario y/o contraseña";
				header('Location: index.php?action=login&e='.urlencode($e)); exit;
				break;
				}
		}
	}

	function CambiarSenia(){	
		if(isset($_SESSION['user'])){
			$data= new pegaso;
			$pagina = $this->load_template('Menu Admin');			
			$html = $this->load_page('app/views/pages/p.cambiarSenia.php');
			$pagina = $this->replace_content('/\#CONTENIDO\#/ms', $html, $pagina);
			ob_start();
			$this-> view_page($pagina);
		}else{
			$e = "Favor de Revisar sus datos";
			header('Location: index.php?action=login&e='.urlencode($e)); exit;
		}
	}

	function cambioSenia($nuevaSenia, $actual, $usuario){
		if(isset($_SESSION['user'])){
			$data=new pegaso;
			$pagina = $this->load_template('Menu Admin');			
			$html = $this->load_page('app/views/pages/p.cambiarSenia.php');
			ob_start();
			$cambio=$data->cambioSenia($nuevaSenia, $actual, $usuario);
			$this->CerrarVentana();
		}else{
			$e = "Favor de Revisar sus datos";
			header('Location: index.php?action=login&e='.urlencode($e)); exit;
		}	
	}
	/*nuevos menus*/
	function Empresas(){
		if($_SESSION['user'][0][10]==99999){
			$data = new ftc;
			$pagina = $this->load_template('Menu Admin');			
			$html = $this->load_page('app/views/modules/m.mad.php');
			ob_start();
			$table = ob_get_clean();
			$usuario=$_SESSION['user'][0][2].' '.$_SESSION['user'][0][3].' '.$_SESSION['user'][0][4];
			$empresas = $data->traeEmpresas();
			include 'app/views/modules/m.mad.php';
			$pagina = $this->replace_content('/\#CONTENIDO\#/ms', $table, $pagina);
			$this-> view_page($pagina);
		}else{
			$e = "Favor de Revisar sus datos";
			header('Location: index.php?action=login&e='.urlencode($e)); exit;
		}
	}

	function altaunidades(){
		if(isset($_SESSION['user'])){
		$pagina = $this->load_templateL('Alta Unidad');
			$html = $this->load_page('app/views/pages/p.altaunidad.php');
			$pagina = $this->replace_content('/\#CONTENIDO\#/ms', $html, $pagina);
			$this->view_page($pagina);
			}else{
			$e = "Favor de Iniciar Sesión";
			header('Location: index.php?action=login&e='.urlencode($e)); exit;
		}
	}

	function AltaEmpresa(){
		if(isset($_SESSION['user'])){
			$pagina = $this->load_templateL('Alta Unidad');
			$html = $this->load_page('app/views/pages/empresas/p.AltaEmpresa.php');
			$pagina = $this->replace_content('/\#CONTENIDO\#/ms', $html, $pagina);
			$this->view_page($pagina);
		}else{
			$e = "Favor de Iniciar Sesión";
			header('Location: index.php?action=login&e='.urlencode($e)); 
			exit;	
		}
	}

	function aempresa($emp, $usrs, $coi, $noi, $sae, $bd_coi, $tim, $rfc, $cveSat, $rtaLog, $bd, $serverCOI){
		if(isset($_SESSION['user'])){
			$data= new ftc;
			$exec=$data->aempresa($emp, $usrs, $coi, $noi, $sae, $bd_coi, $tim, $rfc, $cveSat,  $rtaLog, $bd, $serverCOI);
			$this->Empresas();
		}else{
			$e = "Favor de Iniciar Sesión";
			header('Location: index.php?action=login&e='.urlencode($e)); 
			exit;	
		}
	}

	function usXemp($ide){
		if($_SESSION['user'][0][10]==99999){
			$data = new ftc;
			$pagina = $this->load_template('Menu Admin');			
			$html = $this->load_page('app/views/pages/empresas/p.usXemp.php');
			ob_start();
			$table = ob_get_clean();
			$usuario=$_SESSION['user'][0][2].' '.$_SESSION['user'][0][3].' '.$_SESSION['user'][0][4];
			$usr = $data->usXemp($ide);
			$nusr = $data->usXempNo($usr);
			include 'app/views/pages/empresas/p.usXemp.php';
			$pagina = $this->replace_content('/\#CONTENIDO\#/ms', $table, $pagina);
			$this-> view_page($pagina);
		}else{
			$e = "Favor de Revisar sus datos";
			header('Location: index.php?action=login&e='.urlencode($e)); exit;
		}
	}

	function asociaUsuario($ide, $idu){
		if($_SESSION['user']){
			$data = new ftc;
			$data2 = new pegaso;
			$usuario=$data->traeUsuario($ide, $idu);
			$empresa = $data->traeEmpresa($ide);
			$_SESSION['bd']=$empresa[12];
			if($usuario['status']='ok'){
				$asignaFB=$data2->asignaUsuario($ide, $idu, $usuario);	
			}
			return $usuario;
		}
	}

	function cargaLogo($fileName, $ide){
		if ($_SESSION['user']) {
			$data= new ftc;
			$data2 = new pegaso;
			$carga=$data->cargarLogo($fileName, $ide);
			$emp = $data->traeEmpresa($ide);
			if($carga['status'] == 'ok'){
				$actLogo =$data2->cargarLogo($fileName, $emp);
			}
			$this->Empresas();
		}
	}

	function cambiaFecha($ide, $fecha){
		if($_SESSION['user']){
			$data = new ftc;
			$cambia=$data->cambiaFecha($ide, $fecha);
			return $cambia;
		}
	}

	function cambiaModo($ide, $modo){
		if($_SESSION['user']){
			$data = new ftc;
			$cambia=$data->cambiaModo($ide, $modo);
			return $cambia;
		}
	}

	function cambioUsr($ide, $idu, $tipo){
		if($_SESSION['user']){
			$data = new ftc;
			$cambio=$data->cambioUsr($ide, $idu, $tipo);
			return $cambio;
		}
	}

	function acomodar(){
		if($_SESSION['user']){
			$data= new ftc;
			$acomodar=$data->acomodar();
			return $acomodar;
		}
	}

}?>