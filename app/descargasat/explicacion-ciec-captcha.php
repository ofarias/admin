<?php
/*
* Explicación breve sobre el funcionamiento
* del inicio de sesión con CIEC/Captcha.
*/
// Instanciar librería
// TODO verificar que esta ruta es correcta, al parecer debe ser ../lib/DescargaMasivaCfdi.php
require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'DescargaMasivaCfdi.php';
$descargaCfdi = new DescargaMasivaCfdi();
// PASO 1
// a) Obtener datos del captcha que se mostrará al usuario
$imagenBase64 = $descargaCfdi->obtenerCaptcha();
// b) Obtener sesión actual.
//    Es necesario guardarla de alguna forma (ej. base de datos,
//    sesión PHP, enviar/recibir en formulario, etc.)
//    para continuar el proceso más adelante.
$sesionStr = $descargaCfdi->obtenerSesion();
// c) Mostrar imagen al usuario
$imgStr = '<img src="data:image/jpeg;base64,'.$imagenBase64.'" />';
// PASO 2
// a) Recuperar RFC, contraseña y captcha introducidos por el usuario
$rfcStr = $_POST['rfc']; // ejemplo
$contrasenaStr = $_POST['contrasena']; // ejemplo
$captchaStr = $_POST['captcha']; // ejemplo
// b) Restaurar sesión guardada previamente
$descargaCfdi->restaurarSesion($sesionStr);
// c) Iniciar sesión en el SAT
$inicioSesionOk = $descargaCfdi->iniciarSesionCiecCaptcha(
    $rfcStr, $contrasenaStr, $captchaStr
);
// PASO 3// Preparar datos para busqueda de recibidos
//$busqueda = new BusquedaRecibidos();
//$busqueda->establecerFecha(2017, 11); // $anio, $mes, $dia=null
// Continuar con la búsqueda y descarga de forma normal.
// $busqueda->establecerHoraInicial($hora=0, $minuto=0, $segundo=0);
// $busqueda->establecerHoraFinal($hora='23', $minuto='59', $segundo='59');
// $busqueda->establecerRfcEmisor($rfc);
// $busqueda->establecerEstado($estado); // Ejemplo: BusquedaRecibidos::ESTADO_VIGENTE
// $busqueda->establecerFolioFiscal($uuid);
// Preparar datos para busqueda de emitidos
// $busqueda = new BusquedaEmitidos();
// $busqueda->establecerFechaInicial(2017, 10, 1); // $anio, $mes, $dia
// $busqueda->establecerFechaFinal(2017, 10, 15); // $anio, $mes, $dia
// $busqueda->establecerHoraInicial($hora='0', $minuto='0', $segundo='0');
// $busqueda->establecerHoraFinal($hora='0', $minuto='0', $segundo='0');
// $busqueda->establecerRfcReceptor($rfc);
// $busqueda->establecerEstado($estado); // Ejemplo: BusquedaEmitidos::ESTADO_VIGENTE
// $busqueda->establecerFolioFiscal($uuid);
