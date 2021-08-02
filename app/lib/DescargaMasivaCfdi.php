<?php
/**
 * Librería para descarga masiva de CFDI emitidos y recibidos
 * del servidor del SAT.
 *
 * @author  Novatech Labs asanchez@novatechlabs.com
 * @version 10.0.0
 */

class DescargaMasivaCfdi {
    const URL_CFDIAU = 'https://cfdiau.sat.gob.mx/nidp/app';
    const URL_PORTAL_CFDI = 'https://portalcfdi.facturaelectronica.sat.gob.mx/';
    const HEADER_USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36';


    public function __construct() {
        // ocultar "Warnings" por errores de HTML en las paginas del SAT
        libxml_use_internal_errors(true);
        RespuestaCurl::reset();
    }

    /**
     * Realiza en inicio de sesión en el portal del SAT
     * mediante la FIEL
     * @param UtilCertificado $certificado objeto con informacion
     * del certificado FIEL
     * @return boolean resultado del inicio de sesion
     */
    public function iniciarSesionFiel($cert){
        $url = 'https://cfdiau.sat.gob.mx/nidp/app/login?id=SATx509Custom&sid=0&option=credential&sid=0';
        $headers = array(
            'User-Agent' => self::HEADER_USER_AGENT,
            'Referer' => self::URL_CFDIAU,
        );

        // 1
        $respuesta = RespuestaCurl::request(self::URL_PORTAL_CFDI);
        if($respuesta->getStatusCode() != 200){
            return false;
        }

        // 2
        $respuesta = RespuestaCurl::request($url, null, $headers);
        if($respuesta->getStatusCode() != 200){
            return false;
        }

        // 3
        $document = new DOMDocument();
        $document->loadHTML( $respuesta->getBody() );
        if(!$document) {
            return false;
        }
        $post = array();
        $form = $document->getElementById('certform');
        foreach (array('input','select') as $element) {
            foreach ($form->getElementsByTagName($element) as $val) {
                $name = $val->getAttribute('name');
                if(!empty($name)){
                    $post[$name] = utf8_decode($val->getAttribute('value'));
                }
            }
        }
        if(!$post) {
            return false;
        }
        $guid = $post['guid'];
        $serie = $cert->getNumeroCertificado();
        $rfc = $cert->getRFC();
        $validez = $cert->getRangoValidez();
        $co = $guid . '|' . $rfc . '|' . $serie;
        $laFirma = base64_encode($cert->firmarCadena($co, OPENSSL_ALGO_SHA1));
        $token = base64_encode(base64_encode($co) . '#' . $laFirma);
        $post['token'] = $token;
        $post['fert'] = gmdate('ymdHis', $validez['to']).'Z';
        $respuesta = RespuestaCurl::request($url, $post, $headers);
        if($respuesta->getStatusCode() != 200){
            return false;
        }

        // 4
        $post = $this->getFormData( $respuesta->getBody() );
        if(!$post) {
            return false;
        }
        $respuesta = RespuestaCurl::request(self::URL_PORTAL_CFDI, $post, $headers);
        if($respuesta->getStatusCode() != 200){
            return false;
        }

        // 5
        $post = $this->getFormData( $respuesta->getBody() );
        if(!$post) {
            return false;
        }
        $respuesta = RespuestaCurl::request(self::URL_PORTAL_CFDI, $post, $headers);
        if($respuesta->getStatusCode() != 200){
            return false;
        }

        // 6
        $respuesta = RespuestaCurl::request(self::URL_PORTAL_CFDI);
        if($respuesta->getStatusCode() != 200){
            return false;
        }elseif(strpos($respuesta->getBody(), $rfc) === false){
            return false;
        }else{
            return true;
        }
    }

    /**
     * Realiza en inicio de sesión en el portal del SAT
     * mediante la CIEC con Captcha
     * @param string $rfc RFC
     * @param string $contrasena Contraseña
     * @param string $captcha caracteres del captcha
     * @return boolean resultado del inicio de sesion
     */
    public function iniciarSesionCiecCaptcha($rfc, $contrasena, $captcha){
        $rfc = strtoupper($rfc);

        // 1
        $respuesta = RespuestaCurl::request(
            'https://cfdiau.sat.gob.mx/nidp/wsfed/ep?id=SATUPCFDiCon&sid=0&option=credential&sid=0',
            array(
                'option'=>'credential',
                'Ecom_User_ID'=>$rfc,
                'Ecom_Password'=>$contrasena,
                //'jcaptcha'=>$captcha,
                'userCaptcha'=>strtoupper($captcha),
                'submit'=>'Enviar'
            )
        );
        if($respuesta->getStatusCode() != 200 || !$respuesta->getBody()){
            return false;
        }

        // 2
        $respuesta = RespuestaCurl::request('https://cfdiau.sat.gob.mx/nidp/wsfed/ep?sid=0');
        if($respuesta->getStatusCode() != 200 || !$respuesta->getBody()){
            return false;
        }
        $post = $this->getFormData( $respuesta->getBody() );
        if(!$post) {
            return false;
        }

        // 3
        $respuesta = RespuestaCurl::request(self::URL_PORTAL_CFDI, $post);
        if($respuesta->getStatusCode() != 200){
            return false;
        }
        $post = $this->getFormData( $respuesta->getBody() );
        if(!$post) {
            return false;
        }

        // 4
        $respuesta = RespuestaCurl::request(self::URL_PORTAL_CFDI, $post);
        if($respuesta->getStatusCode() != 200){
            return false;
        }

        // 5
        $respuesta = RespuestaCurl::request(self::URL_PORTAL_CFDI);
        if($respuesta->getStatusCode() != 200){
            return false;
        }elseif(strpos($respuesta->getBody(), $rfc) === false){
            return false;
        }else{
            return true;
        }
    }

    /**
     * Obtiene la imagen del captcha requerido para
     * el inicio de sesión con CIEC/Captcha
     * @return string contenido de la imagen del captcha en Base 64
     */
    public function obtenerCaptcha() {
        // 1
        $respuesta = RespuestaCurl::request('https://portalcfdi.facturaelectronica.sat.gob.mx');
        if($respuesta->getStatusCode() != 200 || !$respuesta->getBody()){
            return false;
        }
        // 2
        $respuesta = RespuestaCurl::request('https://cfdiau.sat.gob.mx/nidp/wsfed/ep?id=SATUPCFDiCon&sid=0&option=credential&sid=0');
        if($respuesta->getStatusCode() != 200){
            return false;
        }
        // 3
        /*$respuesta = RespuestaCurl::request('https://cfdiau.sat.gob.mx/nidp/jcaptcha.jpg');
        if($respuesta->getStatusCode() != 200){
            return false;
        }
        return base64_encode($respuesta->getBody());
        */
        $document = new DOMDocument();
        $document->loadHTML( $respuesta->getBody() );
        if(!$document) {
            return false;
        }
        $xp = new DOMXPath($document);
        $img = $xp->query('//label[@id="divCaptcha"]/img');
        if(empty($img[0])) {
            return false;
        }
        $src = $img[0]->getAttribute('src');
        return substr($src, strlen('data:image/jpeg;base64,'));
    }

    /**
     * Permite obtener los CFDI emitidos/recibidos utilizando
     * las opciones que ofrece el portal del SAT
     * @param object $filtros configuración de los filtros a utilizar
     * @return array objetos XmlInfo de los XML encontrados
     */
    public function buscar($filtros) {
        if(get_class($filtros) == 'BusquedaEmitidos') {
            $url = 'https://portalcfdi.facturaelectronica.sat.gob.mx/ConsultaEmisor.aspx';
            $modulo = 'emitidos';
        }elseif(get_class($filtros) == 'BusquedaRecibidos') {
            $url = 'https://portalcfdi.facturaelectronica.sat.gob.mx/ConsultaReceptor.aspx';
            $modulo = 'recibidos';
        }else{
            return false;
        }

        $respuesta = RespuestaCurl::request($url);
        $html = $respuesta->getBody();
        $post = $this->obtenerDatosFormHtml($html);
        if(!$post){ // si no hay elementos devuelve un falso
            return false;
        }

        $encabezados = array(
            'User-Agent' => self::HEADER_USER_AGENT,
            'Referer' => self::URL_PORTAL_CFDI,
            'X-MicrosoftAjax' => 'Delta=true',
            'X-Requested-With' => 'XMLHttpRequest',
        );
        $respuesta = RespuestaCurl::request($url, $post, $encabezados);
        $html = $respuesta->getBody();
        $post = $filtros->obtenerFormularioAjax($post, $html);
        $respuesta = RespuestaCurl::request($url, $post, $encabezados);
        $html = $respuesta->getBody();
        $objects = $this->getXmlObjects($html, $modulo);

        return empty($objects)
            ? null
            : $objects;
    }


    /**
     * Devuelve el XML del CFDI como string
     * @param string $url del XML
     * @return string datos del XML, o NULL
     */
    public function obtenerXml($url){
        if(!empty($url)) {
            $xml = $this->obtenerArchivoString($url);
            if(!empty($xml)) {
                return $xml;
            }
        }

        return null;
    }

    /**
     * Guarda el XML del CFDI en la ruta especificada
     * @param string $url del XML
     * @param string $dir ubicación del archivo
     * @param string $nombre nombre del archivo (sin extensión)
     */
    public function guardarXml($url, $dir, $nombre){
        if(empty($url)) {
            return false;
        }

        $resource = fopen($dir.DIRECTORY_SEPARATOR.$nombre.'.xml', 'w');

        $saved = false;
        $str = $this->obtenerArchivoString($url);
        if(!empty($str)) {
            $bytes = fwrite($resource, $str);
            $saved = ($bytes !== false);
            fclose($resource);
        }

        return $saved;
    }

    /**
     * Guarda el acuse de cancelación de un XML en la ruta especificada
     * @param string $url del acuse
     * @param string $dir ubicación del archivo
     * @param string $nombre nombre del archivo sin, incluir extensión
     */
    public function guardarAcuse($url, $dir, $nombre){
        if(empty($url)) {
            return false;
        }

        $resource = fopen($dir.DIRECTORY_SEPARATOR.$nombre.'.pdf', 'w');

        $saved = false;
        $str = $this->obtenerArchivoString($url);
        if(!empty($str)) {
            $bytes = fwrite($resource, $str);
            $saved = ($bytes !== false);
            fclose($resource);
        }

        return $saved;
    }

    /**
     * Obtiene los datos de la sesión actual
     * @return string datos de la sesion actual
     */
    public function obtenerSesion(){
        return base64_encode(
            json_encode(RespuestaCurl::getCookie())
        );
    }

    /**
     * Restaura una sesion previa
     * @param string $sesion datos de una sesion anterior
     */
    public function restaurarSesion($sesion){
        if(!empty($sesion)) {
            return RespuestaCurl::setCookie(
                json_decode(base64_decode($sesion), true)
            );
        }
        return false;
    }

    private function getXmlObjects($html, $modulo){
        $document = new DOMDocument();
        $document->loadHTML($html);
        if(!$document) return null;
        $xp = new DOMXPath($document);
        $trs = $xp->query('//table[@id="ctl00_MainContent_tblResult"]/tr');
        if(!$trs) return null;
        $xmls = array();
        foreach ($trs as $i => $trElement) {
            if($i == 0) continue;
            if($xml = XmlInfo::fromHtmlElement($xp, $trElement, $modulo)){
                $xmls[] = $xml;
            }
        }
        return $xmls;
    }

    private function getFormData($html){
        $document = new DOMDocument();
        $document->loadHTML($html);
        if(!$document) return null;
        $form = $document->getElementsByTagName('form')->item(0);
        if(!$form) return null;
        $post = array();
        foreach (array('input','select') as $element) {
            foreach ($form->getElementsByTagName($element) as $val) {
                $name = $val->getAttribute('name');
                if(!empty($name)){
                    $post[$name] = utf8_decode($val->getAttribute('value'));
                }
            }
        }
        return $post;
    }

    private function obtenerDatosFormHtml($html){
        $post = $this->getFormData($html); // Obtiene los elementos input select de la pagina del SAT.
        if(!empty($post)) { /// se finaliza cuando no encuentra nada en el resultado. 
            unset(
                $post['linea'],
                $post['seleccionador'],
                $post['ctl00$MainContent$BtnDescargar'],
                $post['ctl00$MainContent$BtnCancelar'],
                $post['ctl00$MainContent$BtnImprimir'],
                $post['ctl00$MainContent$BtnMetadata'],
                $post['ctl00$MainContent$Captcha$btnCaptcha'],
                $post['ctl00$MainContent$Captcha$btnRefrescar'],
                $post['ctl00$MainContent$Captcha$Cancela'],
                $post['ctl00$MainContent$BtnMetadataComprobante']
            );
            return $post;
        }
        return null;
    }

    private function obtenerArchivoString($url){
        if(empty($url)) return false;

        $respuesta = RespuestaCurl::request($url, null, null);
        if($respuesta->getStatusCode() == 200) {
            return $respuesta->getBody();
        }else{
            return null;
        }
    }
}

class BusquedaRecibidos {
    const ESTADO_TODOS          = 1; // Todos
    const ESTADO_VIGENTE        = 2; // Todos los vigentes
    const ESTADO_CANCELADO      = 6; // Todos los cancelados
    const ESTADO_VIGENTE_EP     = 7; // Vigentes (en proceso)
    const ESTADO_VIGENTE_R      = 8; // Vigentes (rechazados)
    const ESTADO_VIGENTE_V      = 9; // Vigentes (vencidos)
    const ESTADO_VIGENTE_SP     = 10; // Vigentes (sin proceso)
    const ESTADO_CANCELADO_CA   = 3; // Cancelado (con aceptación)
    const ESTADO_CANCELADO_SA   = 4; // Cancelado (sin aceptación)
    const ESTADO_CANCELADO_PV   = 5; // Cancelado (plazo vencido)

    private $anio;
    private $mes;
    private $dia             =  '0';
    private $hora_inicial    =  '0';
    private $minuto_inicial  =  '0';
    private $segundo_inicial =  '0';
    private $hora_final      = '23';
    private $minuto_final    = '59';
    private $segundo_final   = '59';
    private $rfc_emisor      = '';
    private $folio_fiscal    = '';
    private $tipo            = '-1';
    private $tipoBusqueda    = 'fecha';
    private $estado          = self::ESTADO_TODOS;


    public function __construct() {
        $this->anio = date('Y');
        $this->mes = date('n');
    }

    /**
     * Permite indicar el estado de los CFDI a buscar
     * @param string $estado
     */
    public function establecerEstado($estado){
        $this->estado = (int)$estado;
        $this->setTipoBusqueda('fecha');
    }

    /**
     * Permite indicar la fecha de búsqueda
     * @param int $anio año a 4 dígitos
     * @param int $mes mes del 1 a 12
     * @param int $dia día del 1 al 31. Si no se especifica,
     * no se tomará en cuenta el día al hacer la búsqueda
     */
    public function establecerFecha($anio, $mes, $dia=null){
        $this->anio = (string)$anio;
        $this->mes = ltrim((string)$mes, '0');
        if($dia == null) {
            $this->dia = '0';
        }else{
            $this->dia = str_pad($dia, 2, '0', STR_PAD_LEFT);
        }
        $this->setTipoBusqueda('fecha');
    }

    /**
     * Permite indicar la hora inicial de búsqueda
     * @param int $hora hora en formato de 24 horas (0-23)
     * @param int $minuto minuto del 0 al 59
     * @param int $segundo segundo del 0 al 59
     */
    public function establecerHoraInicial($hora='0', $minuto='0', $segundo='0'){
        $this->hora_inicial = (string)$hora;
        $this->minuto_inicial = (string)$minuto;
        $this->segundo_inicial = (string)$segundo;
        $this->setTipoBusqueda('fecha');
    }

    /**
     * Permite indicar la hora final de búsqueda
     * @param int $hora hora en formato de 24 horas (0-23)
     * @param int $minuto minuto del 0 al 59
     * @param int $segundo segundo del 0 al 59
     */
    public function establecerHoraFinal($hora='23', $minuto='59', $segundo='59'){
        $this->hora_final = (string)$hora;
        $this->minuto_final = (string)$minuto;
        $this->segundo_final = (string)$segundo;
        $this->setTipoBusqueda('fecha');
    }

    /**
     * Permite establecer el RFC del emisor
     * @param string $rfc RFC del emisor
     */
    public function establecerRfcEmisor($rfc){
        $this->rfc_emisor = $rfc;
        $this->setTipoBusqueda('fecha');
    }

    /**
     * Permite establecer el UUID
     * @param string $uuid el UUID
     */
    public function establecerFolioFiscal($uuid){
        $this->folio_fiscal = $uuid;
        $this->setTipoBusqueda('folio');
    }

    public function obtenerFormulario(){
        $estadoStr = '1';
        $vigenteStr = '0';
        $canceladoStr = '0';
        switch ($this->estado) {
            case self::ESTADO_VIGENTE:
                break;
            case self::ESTADO_VIGENTE_EP:
                $vigenteStr = '1';
                break;
            case self::ESTADO_VIGENTE_R:
                $vigenteStr = '4';
                break;
            case self::ESTADO_VIGENTE_SP:
                $vigenteStr = '-1';
                break;
            case self::ESTADO_VIGENTE_V:
                $vigenteStr = '5';
                break;
            case self::ESTADO_CANCELADO:
                $estadoStr = '0';
                break;
            case self::ESTADO_CANCELADO_SA:
                $estadoStr = '0';
                $canceladoStr = '3';
                break;
            case self::ESTADO_CANCELADO_CA:
                $estadoStr = '0';
                $canceladoStr = '2';
                break;
            case self::ESTADO_CANCELADO_PV:
                $estadoStr = '0';
                $canceladoStr = '5';
                break;
            case self::ESTADO_TODOS:
            default:
                $estadoStr = '-1';
                break;
        }

        return array(
            '__ASYNCPOST' =>'true',
            'ctl00$MainContent$BtnBusqueda' => 'Buscar CFDI',
            'ctl00$MainContent$CldFecha$DdlAnio' => $this->anio,
            'ctl00$MainContent$CldFecha$DdlDia' => $this->dia,
            'ctl00$MainContent$CldFecha$DdlHora' => $this->hora_inicial,
            'ctl00$MainContent$CldFecha$DdlHoraFin' => $this->hora_final,
            'ctl00$MainContent$CldFecha$DdlMes' => $this->mes,
            'ctl00$MainContent$CldFecha$DdlMinuto' => $this->minuto_inicial,
            'ctl00$MainContent$CldFecha$DdlMinutoFin' => $this->minuto_final,
            'ctl00$MainContent$CldFecha$DdlSegundo' => $this->segundo_inicial,
            'ctl00$MainContent$CldFecha$DdlSegundoFin' => $this->segundo_final,
            'ctl00$MainContent$DdlEstadoComprobante' => $estadoStr,
            'ctl00$MainContent$ddlVigente' => $vigenteStr,
            'ctl00$MainContent$ddlCancelado' => $canceladoStr,
            'ctl00$MainContent$TxtRfcReceptor' => $this->rfc_emisor,
            'ctl00$MainContent$TxtUUID' => $this->folio_fiscal,
            'ctl00$MainContent$ddlComplementos' => $this->tipo,
            'ctl00$MainContent$hfInicialBool' => 'false',
            'ctl00$MainContent$FiltroCentral' =>
                ($this->tipoBusqueda == 'fecha') ? 'RdoFechas' : 'RdoFolioFiscal'
        );
    }

    public function obtenerFormularioAjax($post, $fuente){
        $valores = explode('|', $fuente);
        $validos = array(
            '__EVENTTARGET',
            '__EVENTARGUMENT',
            '__LASTFOCUS',
            '__VIEWSTATE',
            '__CSRFTOKEN',
            'ctl00$ScriptManager1',
        );
        $valCount = count($valores);
        $items = array();
        for ($i=0; $i < $valCount; $i++) { 
            $item = $valores[$i];
            if(in_array($item, $validos)) {
                $items[$item] = $valores[$i+1];
            }
        }

        return array_merge(
            array_merge($post, $this->obtenerFormulario()),
            $items
        );
    }

    private function setTipoBusqueda($tipo){
        $this->tipoBusqueda = $tipo;
        if($tipo == 'fecha'){
            $this->folio_fiscal = '';
        }
    }
}

class BusquedaEmitidos {
    const ESTADO_TODOS          = 1;  // Todos
    const ESTADO_VIGENTE        = 2;  // Todos los vigentes
    const ESTADO_CANCELADO      = 6;  // Todos los cancelados
    const ESTADO_VIGENTE_EP     = 7;  // Vigentes (en proceso)
    const ESTADO_VIGENTE_R      = 8;  // Vigentes (rechazados)
    const ESTADO_VIGENTE_V      = 9;  // Vigentes (vencidos)
    const ESTADO_VIGENTE_SP     = 10; // Vigentes (sin proceso)
    const ESTADO_CANCELADO_CA   = 3;  // Cancelado (con aceptación)
    const ESTADO_CANCELADO_SA   = 4;  // Cancelado (sin aceptación)
    const ESTADO_CANCELADO_PV   = 5;  // Cancelado (plazo vencido)

    private $fecha_inicial;
    private $hora_inicial    =  '0';
    private $minuto_inicial  =  '0';
    private $segundo_inicial =  '0';
    private $fecha_final;
    private $hora_final      = '23';
    private $minuto_final    = '59';
    private $segundo_final   = '59';
    private $rfc_receptor    = '';
    private $folio_fiscal    = '';
    private $tipo            = '-1';
    private $tipoBusqueda    = 'fecha';
    private $estado          = self::ESTADO_TODOS;


    public function __construct() {
        $this->hfInicial = date('Y');
        $this->hfFinal = date('n');
    }

    /**
     * Permite indicar el estado de los CFDI a buscar
     * @param string $estado
     */
    public function establecerEstado($estado){
        $this->estado = (int)$estado;
        $this->setTipoBusqueda('fecha');
    }

    /**
     * Permite indicar la fecha inicial de búsqueda
     * @param int $anio año a 4 dígitos
     * @param int $mes mes del 1 a 12
     * @param int $dia día del 1 al 31
     */
    public function establecerFechaInicial($anio, $mes, $dia){
        $this->hfInicial = (string)$anio;
        $this->fecha_inicial = 
            str_pad($dia, 2, '0', STR_PAD_LEFT) . '/' .
            str_pad($mes, 2, '0', STR_PAD_LEFT) . '/' .
            (string)$anio;
        $this->setTipoBusqueda('fecha');
    }

    /**
     * Permite indicar la fecha final de búsqueda
     * @param int $anio año a 4 dígitos
     * @param int $mes mes del 1 a 12
     * @param int $dia día del 1 al 31
     */
    public function establecerFechaFinal($anio, $mes, $dia){
        $this->hfFinal = (string)$anio;
        $this->fecha_final =
            str_pad($dia, 2, '0', STR_PAD_LEFT) . '/' .
            str_pad($mes, 2, '0', STR_PAD_LEFT) . '/' .
            (string)$anio;
        $this->setTipoBusqueda('fecha');
    }

    /**
     * Permite indicar la hora inicial de búsqueda
     * @param int $hora hora en formato de 24 horas (0-23)
     * @param int $minuto minuto del 0 al 59
     * @param int $segundo segundo del 0 al 59
     */
    public function establecerHoraInicial($hora='0', $minuto='0', $segundo='0'){
        $this->hora_inicial = (string)$hora;
        $this->minuto_inicial = (string)$minuto;
        $this->segundo_inicial = (string)$segundo;
        $this->setTipoBusqueda('fecha');
    }

    /**
     * Permite indicar la hora final de búsqueda
     * @param int $hora hora en formato de 24 horas (0-23)
     * @param int $minuto minuto del 0 al 59
     * @param int $segundo segundo del 0 al 59
     */
    public function establecerHoraFinal($hora='23', $minuto='59', $segundo='59'){
        $this->hora_final = (string)$hora;
        $this->minuto_final = (string)$minuto;
        $this->segundo_final = (string)$segundo;
        $this->setTipoBusqueda('fecha');
    }

    /**
     * Permite establecer el RFC del receptor
     * @param string $rfc RFC del emisor
     */
    public function establecerRfcReceptor($rfc){
        $this->rfc_receptor = $rfc;
        $this->setTipoBusqueda('fecha');
    }

    /**
     * Permite establecer el UUID
     * @param string $uuid el UUID
     */
    public function establecerFolioFiscal($uuid){
        $this->folio_fiscal = $uuid;
        $this->setTipoBusqueda('folio');
    }

    public function obtenerFormulario(){
        $estadoStr = '1';
        $vigenteStr = '0';
        $canceladoStr = '0';
        switch ($this->estado) {
            case self::ESTADO_VIGENTE:
                break;
            case self::ESTADO_VIGENTE_EP:
                $vigenteStr = '1';
                break;
            case self::ESTADO_VIGENTE_R:
                $vigenteStr = '4';
                break;
            case self::ESTADO_VIGENTE_SP:
                $vigenteStr = '-1';
                break;
            case self::ESTADO_VIGENTE_V:
                $vigenteStr = '5';
                break;
            case self::ESTADO_CANCELADO:
                $estadoStr = '0';
                break;
            case self::ESTADO_CANCELADO_SA:
                $estadoStr = '0';
                $canceladoStr = '3';
                break;
            case self::ESTADO_CANCELADO_CA:
                $estadoStr = '0';
                $canceladoStr = '2';
                break;
            case self::ESTADO_CANCELADO_PV:
                $estadoStr = '0';
                $canceladoStr = '5';
                break;
            case self::ESTADO_TODOS:
            default:
                $estadoStr = '-1';
                break;
        }

        return array(
            '__ASYNCPOST' =>'true',
            // '__EVENTARGUMENT' => '',
            // '__EVENTTARGET' => '',
            // '__LASTFOCUS' => '',
            'ctl00$MainContent$BtnBusqueda' => 'Buscar CFDI',
            'ctl00$MainContent$hfInicial' => $this->hfInicial,
            'ctl00$MainContent$hfInicialBool' => 'false',
            'ctl00$MainContent$CldFechaInicial2$Calendario_text' => $this->fecha_inicial,
            'ctl00$MainContent$CldFechaInicial2$DdlHora' => $this->hora_inicial,
            'ctl00$MainContent$CldFechaInicial2$DdlMinuto' => $this->minuto_inicial,
            'ctl00$MainContent$CldFechaInicial2$DdlSegundo' => $this->segundo_inicial,
            'ctl00$MainContent$hfFinal' => $this->hfFinal,
            'ctl00$MainContent$hfFinalBool' => 'false',
            'ctl00$MainContent$CldFechaFinal2$Calendario_text' => $this->fecha_final,
            'ctl00$MainContent$CldFechaFinal2$DdlHora' => $this->hora_final,
            'ctl00$MainContent$CldFechaFinal2$DdlMinuto' => $this->minuto_final,
            'ctl00$MainContent$CldFechaFinal2$DdlSegundo' => $this->segundo_final,
            'ctl00$MainContent$DdlEstadoComprobante' => $estadoStr,
            'ctl00$MainContent$ddlVigente' => $vigenteStr,
            'ctl00$MainContent$ddlCancelado' => $canceladoStr,
            'ctl00$MainContent$TxtRfcReceptor' => $this->rfc_receptor,
            'ctl00$MainContent$TxtUUID' => $this->folio_fiscal,
            'ctl00$MainContent$ddlComplementos' => $this->tipo,
            'ctl00$MainContent$FiltroCentral' =>
                ($this->tipoBusqueda == 'fecha') ? 'RdoFechas' : 'RdoFolioFiscal'
        );
    }

    public function obtenerFormularioAjax($post, $fuente){
        $valores = explode('|', $fuente);
        $validos = array(
            'EVENTTARGET',
            '__EVENTARGUMENT',
            '__LASTFOCUS',
            '__VIEWSTATE'
        );
        $valCount = count($valores);
        $items = array();
        for ($i=0; $i < $valCount; $i++) { 
            $item = $valores[$i];
            if(in_array($item, $validos)){
                $items[$item] = $valores[$i+1];
            }
        }

        return array_merge(
            array_merge(
                $post,
                $this->obtenerFormulario()
            ),
            $items
        );
    }

    private function setTipoBusqueda($tipo){
        $this->tipoBusqueda = $tipo;
        if($tipo == 'fecha'){
            $this->folio_fiscal = '';
        }
    }
}

class XmlInfo {
    public $urlDescargaXml;
    public $urlDescargaAcuse;
    public $urlDescargaRI;
    public $folioFiscal;
    public $emisorRfc;
    public $emisorNombre;
    public $receptorRfc;
    public $receptorNombre;
    public $fechaEmision;
    public $fechaCertificacion;
    public $pacCertifico;
    public $total;
    public $efecto;
    public $estado;
    public $estadoCancelacion;
    public $estadoProcesoCancelacion;
    public $fechaCancelacion;

    /**
     * @deprecated 3.0.0 Utilice la variable $urlDescargaAcuse
     */
    public $urlAcuseXml;


    public function esVigente(){
        return $this->estado === 'Vigente';
    }
    public function esCancelado(){
        return $this->estado === 'Cancelado';
    }

    public static function fromHtmlElement($xpath, $trElement, $modulo){
        if($trElement && $trElement->childNodes->length == 0) {
            return null;
        }

        $xml = new self;

        $index = 0;
        foreach ($trElement->childNodes as $node) {
            if($node->nodeName != 'td') {
                continue;
            }
            if($index == 0) {
                if($nodeSpan = $xpath->query('*//span[@id="BtnDescarga"]', $node)->item(0)) {
                    $xml->urlDescargaXml = DescargaMasivaCfdi::URL_PORTAL_CFDI . str_replace(
                        array('return AccionCfdi(\'','\',\'Recuperacion\');'),
                        '',
                        $nodeSpan->getAttribute('onclick')
                    );
                }
                if($nodeSpan = $xpath->query('*//span[@id="BtnRecuperaAcuseFinal"]', $node)->item(0)) {
                    $xml->urlDescargaAcuse = DescargaMasivaCfdi::URL_PORTAL_CFDI . str_replace(
                        array('javascript:window.location.href=\'','\';'),
                        '',
                        $nodeSpan->getAttribute('onclick')
                    );
                }
                if($nodeSpan = $xpath->query('*//span[@id="BtnRI"]', $node)->item(0)) {
                    $xml->urlDescargaRI = DescargaMasivaCfdi::URL_PORTAL_CFDI
                        . 'RepresentacionImpresa.aspx?Datos=' . str_replace(
                        array('recuperaRepresentacionImpresa(\'','\');'),
                        '',
                        $nodeSpan->getAttribute('onclick')
                    );
                }
            }else{
                $value = $node->nodeValue;
                $value = html_entity_decode($value);
                $value = utf8_decode($value);
                $value = str_replace(chr(160), chr(32), $value);
                switch ($index) {
                    case  1: $xml->folioFiscal = $value; break;
                    case  2: $xml->emisorRfc = $value; break;
                    case  3: $xml->emisorNombre = $value; break;
                    case  4: $xml->receptorRfc = $value; break;
                    case  5: $xml->receptorNombre = $value; break;
                    case  6: $xml->fechaEmision = $value; break;
                    case  7: $xml->fechaCertificacion = $value; break;
                    case  8: $xml->pacCertifico = $value; break;
                    case  9: $xml->total = $value; break;
                    case 10: $xml->efecto = $value; break;
                    case 11: $xml->estadoCancelacion = $value; break;
                    case 12: $xml->estado = $value; break;
                    case 13: $xml->estadoProcesoCancelacion = $value; break;
                    case 14: $xml->fechaCancelacion = $value; break;
                }
            }
            $index++;
        }

        return $xml;
    }
}

class RespuestaCurl {
    protected $respuesta;
    private static $cookie = array();
    public static $defaultOptions = array(
        CURLOPT_ENCODING       => "UTF-8",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_AUTOREFERER    => true,
        CURLOPT_CONNECTTIMEOUT => 120,
        CURLOPT_TIMEOUT        => 120,
        CURLOPT_MAXREDIRS      => 10,
        CURLINFO_HEADER_OUT    => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1
    );


    public static function request($url, $post=null, $headers=null){
        $options = self::$defaultOptions;
        $options[CURLOPT_URL] = $url;

        if($cookie = self::getCookieString()){
            $options[CURLOPT_COOKIE] = $cookie;
        }

        if($post){
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = http_build_query($post);
            if(empty($headers)) $headers = array();
            $headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
        }else{
            $options[CURLOPT_CUSTOMREQUEST] = 'GET';
        }

        if(!empty($headers)){
            $options[CURLOPT_HTTPHEADER] = array();
            foreach ($headers as $key => $value) {
                $options[CURLOPT_HTTPHEADER][] = $key.': '.$value;
            }
        }

        $ch = curl_init();
        curl_setopt_array( $ch, $options );

        $rawContent = curl_exec( $ch );
        $err        = curl_errno( $ch );
        $errmsg     = curl_error( $ch );
        $data       = curl_getinfo( $ch );
        $multi      = curl_multi_getcontent( $ch );
        curl_close( $ch );

        $headerContent = substr($rawContent, 0, $data['header_size']);
        $content = trim(str_replace($headerContent, '', $rawContent));

        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $headerContent, $matches);
        $cookies = array();
        foreach($matches[1] as $item) {
            $pos = strpos($item, '=');
            $cookies[ substr($item, 0, $pos) ] = substr($item, $pos+1);
        }
        self::$cookie = array_merge(self::$cookie, $cookies);

        // $data['errno']   = $err;
        // $data['errmsg']  = $errmsg;
        // $data['headers'] = $headerContent;
        $data['content'] = $content;
        $data['cookies'] = $cookies;

        $o = new self();
        $o->respuesta = $data;
        return $o;
    }

    public static function setCookie($cookie){
        self::$cookie = $cookie;
        return true;
    }

    public static function getCookie(){
        return self::$cookie;
    }

    public function getStatusCode(){
        return $this->respuesta['http_code'];
    }

    public function getBody(){
        return $this->respuesta['content'];
    }

    public static function getCookieString(){
        if(!empty(self::$cookie)){
            $str = '';
            foreach (self::$cookie as $key => $value) {
                $str .= $key.'='.$value.'; ';
            }
            $str = rtrim($str, '; ');
            return $str;
        }
        return '';
    }

    public static function reset() {
        self::$cookie = array();
    }
}

class MultiCurl {
    private $_curl_version;
    private $_maxConcurrent = 3;    //max. number of simultaneous connections allowed
    private $_options       = array();   //shared cURL options
    private $_headers       = array();   //shared cURL request headers
    private $_callback      = null; //default callback
    private $_timeout       = 5000; //all requests must be completed by this time
    public $requests        = array();   //request_queue


    function __construct($max_concurrent = 3) {
        $this->setMaxConcurrent($max_concurrent);
        $v = curl_version();
        $this->_curl_version = $v['version'];
    }

    public function setMaxConcurrent($max_requests) {
        if($max_requests > 0) {
            $this->_maxConcurrent = $max_requests;
        }
    }

    public function setOptions(array $options) {
        $this->_options = $options;
    }

    public function setHeaders(array $headers) {
        if(is_array($headers) && count($headers)) {
            $this->_headers = $headers;
        }
    }

    public function setCallback(callable $callback) {
        $this->_callback = $callback;
    }

    public function setTimeout($timeout) { //in milliseconds
        if($timeout > 0) {
            $this->_timeout = $timeout;
        }
    }

    //Add a request to the request queue
    public function addRequest($url, $user_data = null) { //Add to request queue
        $this->requests[] = array(
            'url' => $url,
            'user_data' => $user_data
        );
        return count($this->requests) - 1; //return request number/index
    }

    //Reset request queue
    public function reset() {
        $this->requests = array();
    }

    //Execute the request queue
    public function execute() {
        //the request map that maps the request queue to request curl handles
        $requests_map = array();
        $multi_handle = curl_multi_init();
        $num_outstanding = 0;
        //start processing the initial request queue
        $num_initial_requests = min($this->_maxConcurrent, count($this->requests));
        for($i = 0; $i < $num_initial_requests; $i++) {
            $this->initRequest($i, $multi_handle, $requests_map);
            $num_outstanding++;
        }
        do{
            do{
                $mh_status = curl_multi_exec($multi_handle, $active);
            } while($mh_status == CURLM_CALL_MULTI_PERFORM);
            if($mh_status != CURLM_OK) {
                break;
            }
            //a request is just completed, find out which one
            while($completed = curl_multi_info_read($multi_handle)) {
                $this->processRequest($completed, $multi_handle, $requests_map);
                $num_outstanding--;
                //try to add/start a new requests to the request queue
                while(
                    $num_outstanding < $this->_maxConcurrent && //under the limit
                    $i < count($this->requests) && isset($this->requests[$i]) // requests left
                ) {
                    $this->initRequest($i, $multi_handle, $requests_map);
                    $num_outstanding++;
                    $i++;
                }
            }
            usleep(15); //save CPU cycles, prevent continuous checking
        } while ($active || count($requests_map)); //End do-while
        $this->reset();
        curl_multi_close($multi_handle);
    }

    //Build individual cURL options for a request
    private function buildOptions(array $request) {
        $url = $request['url'];
        $options = $this->_options;
        $headers = $this->_headers;
        //the below will overide the corresponding default or individual options
        $options[CURLOPT_RETURNTRANSFER] = true;
        $options[CURLOPT_NOSIGNAL] = 1;
        if(version_compare($this->_curl_version, '7.16.2') >= 0) {
            $options[CURLOPT_CONNECTTIMEOUT_MS] = $this->_timeout;
            $options[CURLOPT_TIMEOUT_MS] = $this->_timeout;
            unset($options[CURLOPT_CONNECTTIMEOUT]);
            unset($options[CURLOPT_TIMEOUT]);
        } else {
            $options[CURLOPT_CONNECTTIMEOUT] = round($this->_timeout / 1000);
            $options[CURLOPT_TIMEOUT] = round($this->_timeout / 1000);
            unset($options[CURLOPT_CONNECTTIMEOUT_MS]);
            unset($options[CURLOPT_TIMEOUT_MS]);
        }
        if($url) {
            $options[CURLOPT_URL] = $url;
        }
        if($headers) {
            $options[CURLOPT_HTTPHEADER] = $headers;
        }
        return $options;
    }
    
    private function initRequest($request_num, $multi_handle, &$requests_map) {
        $request =& $this->requests[$request_num];
        $ch = curl_init();
        $options = $this->buildOptions($request);
        $request['options_set'] = $options; //merged options
        $opts_set = curl_setopt_array($ch, $options);
        if(!$opts_set) {
            echo 'options not set';
            exit;
        }
        curl_multi_add_handle($multi_handle, $ch);
        //add curl handle of a new request to the request map
        $ch_hash = (string) $ch;
        $requests_map[$ch_hash] = $request_num;
    }
    
    private function processRequest($completed, $multi_handle, array &$requests_map) {
        $ch = $completed['handle'];
        $ch_hash = (string) $ch;
        $request =& $this->requests[$requests_map[$ch_hash]]; //map handler to request index to get request info
        $request_info = curl_getinfo($ch);
        $url = $request['url'];
        $user_data = $request['user_data'];
        
        if(curl_errno($ch) !== 0 || intval($request_info['http_code']) !== 200) { //if server responded with http error
            $response = false;
        } else { //sucessful response
            $response = curl_multi_getcontent($ch);
        }
        //get request info
        $options = $request['options_set'];
        if($response && !empty($options[CURLOPT_HEADER])) {
            $k = intval($request_info['header_size']);
            $response = substr($response, $k);
        }
        //remove completed request and its curl handle
        unset($requests_map[$ch_hash]);
        curl_multi_remove_handle($multi_handle, $ch);
        //call the callback function and pass request info and user data to it
        if($this->_callback) {
            call_user_func($this->_callback, $url, $response, $user_data);
        }
        $request = null; //free up memory now just incase response was large
    }
}

class DescargaAsincrona {
    private $resultados;
    private $totalOk;
    private $totalErr;
    private $timeSec;
    private $mc;

    public function __construct($maxSimultaneos=10) {
        $this->mc = new MultiCurl($maxSimultaneos);
        $opts = RespuestaCurl::$defaultOptions;
        $opts[CURLOPT_COOKIE] = RespuestaCurl::getCookieString();
        $opts[CURLOPT_CUSTOMREQUEST] = 'GET';
        $this->mc->setOptions($opts);

        $this->mc->setCallback(function($url, $response, $user_data) {
            $ok = $this->guardarArchivo(
                $response,
                $user_data['dir'],
                $user_data['fn'],
                $user_data['ext'], 
                $user_data['accion'],
                $user_data['rfc']
            );
            $this->resultados[] = array(
                'uuid' => $user_data['uuid'],
                'guardado' => $ok
            );
            if($ok) {
                $this->totalOk++;
            }else{
                $this->totalErr++;
            }
        });
    }

    public function agregarXml($url, $dir, $uuid, $accion, $r, $nombreArchivo=null) {
        $this->mc->addRequest($url, array(
            'ext'=>'xml',
            'dir'=>$dir,
            'uuid'=>$uuid,
            'fn'=>$nombreArchivo ? $nombreArchivo : $uuid, 
            'accion'=>$accion,
            'rfc'=>$r

        ));
    }

    public function agregarAcuse($url, $dir, $uuid, $nombreArchivo=null) {
        $this->mc->addRequest($url, array(
            'ext'=>'pdf',
            'dir'=>$dir,
            'uuid'=>$uuid,
            'fn'=>$nombreArchivo ? $nombreArchivo : $uuid
        ));
    }

    public function agregarRepImpr($url, $dir, $uuid, $nombreArchivo=null) {
        $this->mc->addRequest($url, array(
            'ext'=>'pdf',
            'dir'=>$dir,
            'uuid'=>$uuid,
            'fn'=>$nombreArchivo ? $nombreArchivo : $uuid
        ));
    }

    public function procesar() {
        // restaurar valores
        $this->resultados = array();
        $this->totalOk = 0;
        $this->totalErr = 0;
        $this->timeSec = 0;

        $time = microtime(true);
        $this->mc->execute();
        $this->timeSec = microtime(true) - $time;
        $this->mc = null;

        return true;
    }

    public function totalDescargados() {
        return $this->totalOk;
    }

    public function totalErrores() {
        return $this->totalErr;
    }

    public function segundosTranscurridos() {
        return round($this->timeSec, 3);
    }

    public function resultado() {
        return $this->resultados;
    }

    private function guardarArchivo($str, $dir, $nombre, $ext, $accion, $rfc) {
        if(file_exists($dir.$rfc)){
            //echo 'Si existe la carpete'.$dir.$rfc;
        }else{
            //echo 'No existe y se tiene que crear';
            mkdir($dir.$rfc);
        }
        //exit();
        $resource = fopen($dir.DIRECTORY_SEPARATOR.$rfc.DIRECTORY_SEPARATOR.substr($accion,10).'-'.$rfc.'-'.$nombre.'.'.$ext, 'w');
        $saved = false;
        if(!empty($str)) {
            $bytes = fwrite($resource, $str);
            $saved = ($bytes !== false);
            fclose($resource);
        }
        return $saved;
    }
}