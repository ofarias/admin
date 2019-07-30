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
require_once('app/lib/DescargaMasivaCfdi.php');

class pegaso_controller {
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
		if ($_SESSION['bd'] && $_SESSION['bd']!=''){
			$data= new pegaso;
			$salir=$data->salir();
		}
		$CookieInfo = session_get_cookie_params();
		if ( (empty($CookieInfo['domain'])) && (empty($CookieInfo['secure'])) ) {
			setcookie(session_name(), '', time()-3600, $CookieInfo['path']);
		} elseif (empty($CookieInfo['secure'])) {
			setcookie(session_name(), '', time()-3600, $CookieInfo['path'], $CookieInfo['domain']);
		} else {
			setcookie(session_name(), '', time()-3600, $CookieInfo['path'], $CookieInfo['domain'], $CookieInfo['secure']);
		}
		//session_unset();
		session_destroy();		
		$pagina = $this->load_templateL('Login');
		$html = $this->load_page('app/views/modules/m.logoff.php');
		$pagina = $this->replace_content('/\#CONTENIDO\#/ms', $html, $pagina);
		$this->view_page($pagina);
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

	function iniciaProcesoDescargaEmpresa($rfc) {
		if ($_SESSION['user']) {
			$data= new ftc;			
			$emp = $data->datosDescarga($rfc);			
			if ($emp){
				//print_r($emp);								
				$fecha = $emp[0];					
				$nombre = $emp[1];
				$rfc = $emp[2];
				$clave = $emp[3];				
				//$credencial = $emp[4];
				
				if ($clave){
					//TODO here comes the code to gain the captcha element
					$descargaCfdi = new DescargaMasivaCfdi();
					$imagenBase64 = $descargaCfdi->obtenerCaptcha();
					$imgStr = '<img src="data:image/jpeg;base64,'.$imagenBase64.'" />';					
				} else {
					$e = "Al paracer la empresa $emp no ha registrado sus credenciales de consulta.";
					header('Location: index.php?action=login&e='.urlencode($e)); 
					return;
					}
			} else {
				$e = "La empresa $emp no se ha localizado o algo fue mal con la consulta.";				
				header('Location: index.php?action=login&e='.urlencode($e)); 
				return;
			}
		} else {
			$e = "Favor de verificar que no haya expirado su sesión.";
			header('Location: index.php?action=login&e='.urlencode($e)); exit;
		}
		
		// TODO: at the end, if there isn't error, set the view 
		$pagina = $this->load_templateL('Descarga SAT');
		$table = ob_get_clean();
		include 'app/views/pages/empresas/p.descargasat.php';
		$pagina = $this->replace_content('/\#CONTENIDO\#/ms', $table, $pagina);
		$this-> view_page($pagina);
		return;		
	}

	function descargaSAT($empresa, $rfc, $clave, $captcha) {
		$rutaDescarga = 'app/media/';
		$maxDescargasSimultaneas = 3;
		echo "Ruta de descarga: ".$rutaDescarga;
		// Instanciar clase principal
		$descargaCfdi = new DescargaMasivaCfdi();

		$ok = $descargaCfdi->iniciarSesionCiecCaptcha($rfc, $clave, $captcha);
      	if($ok) {
			  /*
        	echo json_response(array(
				'mensaje' => 'Se ha iniciado la sesión',
				'sesion' => $descargaCfdi->obtenerSesion()
			));
			*/
			echo "Se ha iniciado la sesión";
			$filtros = new BusquedaRecibidos();			
			$filtros->establecerFecha(date("y"), date("m"), day('d'));
			echo "Preparando llamada a SAT: ".$filtros;
			$xmlInfoArr = $descargaCfdi->buscar($filtros);
			echo "Retorno del SAT: ".$xmlInfoArr;
			if($xmlInfoArr){
				echo "Response OK";
			}else{
				echo "No se han localizado CFDIs para ".$descargaCfdi->obtenerSesion();
				/*
				echo json_response(array(
					'mensaje' => 'No se han encontrado CFDIs',
					'sesion' => $descargaCfdi->obtenerSesion()
				));
				*/
			}
		}else{
			echo "Ha ocurrido un error al iniciar sesión. Intente nuevamente";
			/*
        	echo json_response(array(
          		'mensaje' => 'Ha ocurrido un error al iniciar sesión. Intente nuevamente',
			));
			*/
		}
		$pagina = $this->load_templateL('Descarga SAT');
		$table = ob_get_clean();
		include 'app/views/pages/empresas/p.descargasat.response.php';
		$pagina = $this->replace_content('/\#CONTENIDO\#/ms', $table, $pagina);
		$this-> view_page($pagina);
return;
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

	function json_response($data, $success=true) {
		header('Cache-Control: no-transform,public,max-age=300,s-maxage=900');
		header('Content-Type: application/json');
		return json_encode(array(
		  'success' => $success,
		  'data' => $data
		));
	  }

}?>