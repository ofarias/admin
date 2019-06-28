<?php

class DescargaAsincrona {
    private $resultados;
    private $totalOk;
    private $totalErr;
    private $timeSec;
    private $mc;

    public function __construct($maxSimultaneos=3) {
        //echo '<br/> Valor de Env: ';
        //print_r($_ENV);
        //print_r($_SESSION);
        $this->mc = new MultiCurl($maxSimultaneos);
        $opts = RespuestaCurl::$defaultOptions;
        $opts[CURLOPT_COOKIE] = RespuestaCurl::getCookieString();
        $opts[CURLOPT_CUSTOMREQUEST] = 'GET';
        $this->mc->setOptions($opts);
        $this->mc->setCallback(function($url, $response, $user_data) {
            //print_r($response);
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

    public function agregarAcuse($url, $dir, $uuid, $accion, $r, $nombreArchivo=null) {
        $this->mc->addRequest($url, array(
            'ext'=>'pdf',
            'dir'=>$dir,
            'uuid'=>$uuid,
            'fn'=>$nombreArchivo ? $nombreArchivo : $uuid,
            'accion'=>$accion,
            'rfc'=>$r
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

    private function guardarArchivo($str, $dir, $nombre, $ext, $accion, $rfc){
        //exit('Directorio'.$dir.$rfc);
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