<?php

require_once 'app/model/ftc.php';
/* Clase para hacer uso de database */
class ftc extends ftcws {
    
    function valUsr($usr){
        $data=array();
        $this->query="SELECT * FROM ftc_usuarios where usuario = '$usr'";
        $res=$this->EjecutaQuerySimple();
        while ($tsArray= mysqli_fetch_array($res)){
            $data[]=$tsArray;
        }
        return $data;
    }

    function intUser($usuario, $contra, $email, $rol, $letra, $nomcom, $letras, $paterno, $materno){
        $c= md5($contra);
        $this->query="INSERT INTO ftc_usuarios (id, usuario, nombre, apellido_p, apellido_m, segundo_nombre, contrasenia, fecha_alta, status) values (null, '$usuario','$nomcom', '$paterno','$materno','','$c',current_timestamp, 'Activo')";
        $this->EjecutaQuerySimple();
        return;
    }

    function loginMysql($user, $password){
        $data=array();
        $contra = $password;
        $this->query="SELECT * FROM ftc_usuarios where usuario = '$user' and contrasenia = '$contra' and status= 'Activo'";
        $res=$this->EjecutaQuerySimple();
        while ($tsArray=mysqli_fetch_array($res)){
            $data[]=$tsArray;
        }
        $ln = 0;
        $_SESSION['user']=$data;
        foreach ($data as $key) {
            $usuario = $key['usuario'];
            $ln++;
            $idu = $key['id'];
            $_SESSION['iduFTC']= $idu;
        }
        $equipo=php_uname();
        $ip= $_SERVER['REMOTE_ADDR'];
        $p=session_id();
        $pn=$_SERVER['HTTP_USER_AGENT'];
        if(isset($idu)){
            $empresas = $this->traeEmpresasUsuario($idu);
            $this->query="INSERT INTO FTC_LOGIN (id, USUARIO, IP, FECHA, EXITO, PHP_SESSION, CIERRE_SESSION,  EQUIPO, NAVEGADOR, SISTEMA) 
                                               VALUES (null, '$user', '$ip', current_timestamp, 'Si', '$p','No', '$equipo', '$pn', 'admin')";
            $this->EjecutaQuerySimple();
            return $empresas;    
        }else{
            $this->query="INSERT INTO FTC_LOGIN (USUARIO, IP, FECHA, EXITO, PHP_SESSION, CIERRE_SESSION, FECHA_CIERRE, EQUIPO, NAVEGADOR, SISTEMA) 
                                               VALUES ('$user', '$ip', current_timestamp, 'No', '$p','Si',current_timestamp, '$equipo', '$pn', 'admin')";
            $this->EjecutaQuerySimple();
            exit('No se encontro el usuario, favor de revisar la información');
        }
        return;
    }

    function traeEmpresasUsuario($idu){
        $data=array();
        $this->query="SELECT u.*, e.*, (SELECT concat(NOMBRE,' ', APELLIDO_P, ' ', APELLIDO_M)  FROM ftc_usuarios fu where fu.id = $idu) AS usuario 
            FROM ftc_empresas_usuarios u 
            left join ftc_empresas e on u.ide=e.ide 
            WHERE u.idu = $idu and u.status = 9";
        $res=$this->EjecutaQuerySimple();
        while ($tsArray=mysqli_fetch_array($res)) {
            $data[]=$tsArray;
        }
        return $data;
    }

    function traeEmpresas(){
        $this->query="SELECT * FROM ftc_empresas where fecha_baja is null order by ide";
        $res=$this->EjecutaQuerySimple();
        while ($tsArray = mysqli_fetch_array($res)){
            $data[]=$tsArray;
        }
        return $data;
    }

    function traeBD(){
        $empresa=$_SESSION['empresa'];
        $ide=explode(":", $empresa);
        $ide=$ide[0];
        $this->query="SELECT * FROM ftc_empresas where ide = $ide";
        $res=$this->EjecutaQuerySimple();
        while ($tsArray = mysqli_fetch_array($res)){
            $data=$tsArray;
        }
        $_SESSION['bd']=$data['ruta_bd'];
        $_SESSION['rfc']=$data['rfc'];
        $_SESSION['r_coi']=$data['ruta_coi'];
        $_SESSION['empresa']=$data;
        return $data['ruta_bd'];
    }

    function aempresa($emp, $usrs, $coi, $noi, $sae, $bd_coi, $tim, $rfc, $cveSat, $rtaLog, $bd, $serverCOI){
        $data = array();
        $this->query="SELECT * FROM ftc_empresas where UPPER(RFC) = UPPER('$rfc')";
        $res=$this->EjecutaQuerySimple();
        while ($tsArray=mysqli_fetch_array($res)) {
            $data[]=$tsArray;
        }
        if(count($data)>0){
            echo '<script>alert("El rfc ya existe, favor de revisar los datos"</script>';
            return;
        }else{
            echo '<script>alert("El rfc ya existe, favor de revisar los datos"</script>';
            $idu = $_SESSION['iduFTC'];
            $bd_coi = $serverCOI.':'.$bd_coi;
            $this->query="INSERT INTO FTC_EMPRESAS (IDE, NOMBRE, USUARIOS, FECHA_ALTA, USUARIO_ALTA, COI, SAE, NOI, RUTA_COI, RUTA_BD, RFC, CVE, logo ) VALUES (null, '$emp', $usrs, current_timestamp, (SELECT concat(NOMBRE, ' ', APELLIDO_P,' ', APELLIDO_M) FROM ftc_usuarios WHERE ID = $idu), $coi , $sae, $noi, '$bd_coi', '$bd', '$rfc', '$cveSat', '$rtaLog' )";
            $this->EjecutaQuerySimple();
            $this->query="INSERT INTO ftc_empresas_usuarios (ID, IDU, IDE, STATUS, FECHA_ALTA, FECHA_BAJA, USUARIO_ALTA, USUARIO_BAJA) 
                                VALUES (NULL, 1, (select max(ide) from ftc_empresas), 1, current_timestamp, null, 1, 0 )";
            $this->EjecutaQuerySimple();
        }
        return;
    }

    function usXemp($ide){
        $this->query="SELECT e.*, f.*, (SELECT em.nombre from ftc_empresas em where e.ide = em.ide) as empresa FROM ftc_ws.ftc_empresas_usuarios e left join ftc_usuarios f on f.id = e.idu where ide = $ide";
        $res=$this->EjecutaQuerySimple();
        while ($tsArray=mysqli_fetch_array($res)){
            $data[]=$tsArray;
        }
        return $data;
    }

    function usXempNo($usr){
        $lista = '';
        foreach ($usr as $k) {
            $lista .= ', '.$k['idu']; 
        }
        $lista = substr($lista, 1);     
        $this->query="SELECT * FROM ftc_usuarios where id not in ($lista)";
        $res=$this->EjecutaQuerySimple();
        while ($tsArray=mysqli_fetch_array($res)){
            $data[]=$tsArray;
        }
        return $data;   
    }

    function traeUsuario($ide, $idu){
        $usuario = $_SESSION['user'][0]['id'];
        $this->query="SELECT * FROM ftc_usuarios WHERE id = $idu";
        $res=$this->EjecutaQuerySimple();
        $row=mysqli_fetch_array($res);
        if($row['status'] == 'Activo'){
            $this->query="SELECT * FROM ftc_empresas_usuarios where ide = $ide and idu = $idu";
            $res=$this->EjecutaQuerySimple();
            $row2= mysqli_fetch_row($res);
            if(empty($row2)){
                $this->query="INSERT INTO ftc_empresas_usuarios (idu, ide, status, fecha_alta, USUARIO_ALTA) 
                                values ($idu, $ide, 1, current_timestamp, (SELECT ID FROM ftc_usuarios WHERE usuario = '$usuario'))";
                $this->EjecutaQuerySimple();
                $mensaje = array("status"=>'ok', "mensaje"=>'Se inserto correctamente el usuario',"usuario"=>$row['usuario'], "contrasenia"=>$row['contrasenia'], "nombre"=>$row['nombre'], "segundo_nombre"=>$row['segundo_nombre'], "apellido_p"=>$row['apellido_p'], "apellido_m"=>$row['apellido_m']);    
            }else{
                $mensaje = array("status"=>'no', "menasaje"=>'El usuario ya esta registrado en la empresa seleccionada',"usuario"=>$row['usuario'], "contrasenia"=>$row['contrasenia'], "nombre"=>$row['nombre'], "segundo_nombre"=>$row['segundo_nombre'], "apellido_p"=>$row['apellido_p'], "apellido_m"=>$row['apellido_m']);                
            }
        }else{
            $mensaje = array("status"=>'no', "menasaje"=>'El usuario no esta activo, favor de revisar la informacion');
        }
        return $mensaje;
    }

    function traeEmpresa($ide){
        $this->query="SELECT * FROM ftc_empresas WHERE ide = $ide";
        $res=$this->EjecutaQuerySimple();
        $row =mysqli_fetch_row($res);
        return $row;
    }

    function fechaUltimaDescarga($ide){
        $this->query="SELECT fecha_ultima_descarga, nombre, rfc FROM ftc_empresas WHERE ide = $ide";
        $res=$this->EjecutaQuerySimple();
        $row =mysqli_fetch_row($res);
        return $row;
    }

    function cargarLogo($fileName, $ide){
        $this->query="UPDATE FTC_EMPRESAS SET LOGO = '$fileName' where ide = $ide";
        $this->EjecutaQuerySimple();
        return array("status"=>'ok');
    }

    function cambiaFecha($ide, $fecha){
        $this->query="UPDATE FTC_EMPRESAS SET FECHA_INICIO = '$fecha' where ide  = $ide";
        $this->EjecutaQuerySimple();
        return array("status"=>'ok');
    }

    function cambiaModo($ide, $modo){
        $this->query="UPDATE FTC_EMPRESAS SET AUTO = '$modo' where ide = $ide";
        $this->EjecutaQuerySimple();
        return array("status"=>'ok', "modo"=>$modo == 'si'? 'Automatico':'Manual');
    }

    function cambioUsr($ide, $idu, $tipo){
        $val= $tipo=='Activar'? 1:0;
        $this->query="UPDATE ftc_empresas_usuarios SET STATUS= $val WHERE ide= $ide and idu = $idu";
        $this->EjecutaQuerySimple();
        return array("status"=>'ok', "mensaje"=>'Se ha cambiado el usuario.');
    }

    function acomodar(){

        $emp=$this->traeEmpresas();
        print_r($emp);
        //foreach ($data as $key){
        //    $rfc=$data['']; 
        //}
        $path="C:\\xampp\\htdocs\\descargasat\\ejemplos\\descargas\\";
        $dir = opendir($path);
        while ($archivo = readdir($dir)){
            if(is_dir($archivo)){
                echo "[".$archivo."]<br/>";
            }else{
                echo $path.$archivo."<br/>";
                $file=$path.$archivo;
                $nombre = $this->leeXML($file);
                $nuevo=$nombre['rfce'].'-'.$nombre['rfcr'].'-'.$nombre['fecha'].'-'.$nombre['uuid'].'.xml';
                rename($file, $path.$nuevo);
            }
        }
    }

    function leeXML($archivo){
        if($myFile = fopen("$archivo", "r")){
            $myXMLData = fread($myFile, filesize($archivo));
            if($xml = @simplexml_load_string($myXMLData)){
                $ns = $xml->getNamespaces(true);
                $xml->registerXPathNamespace('c', $ns['cfdi']);
                $xml->registerXPathNamespace('t', $ns['tfd']);   
                foreach ($xml->xpath('//cfdi:Comprobante') as $cfdiComprobante){
                    $version = $cfdiComprobante['version'];
                    if($version == ''){
                    $version = $cfdiComprobante['Version'];
                    }
                }
                foreach ($xml->xpath('//t:TimbreFiscalDigital') as $tfd) {
                    if($version == '3.2'){
                        $uuid = $tfd['UUID'];
                        $fecha = '';
                    }else{
                        $uuid = $tfd['UUID'];
                        $fecha = $tfd['FechaTimbrado'];
                    }
                }
                if($version == '3.2'){
                    $tipo = $cfdiComprobante['tipoDeComprobante'];
                }elseif($version == '3.3'){
                    $tipo = $cfdiComprobante['TipoDeComprobante'];
                }
                foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Receptor') as $Receptor) {
                    if($version == '3.2'){
                        $rfc= $Receptor['rfc'];
                        $nombre_recep = utf8_encode($Receptor['nombre']);
                        $usoCFDI = '';
                    }elseif($version == '3.3'){
                        $rfc= $Receptor['Rfc'];
                        $nombre_recep=utf8_encode($Receptor['Nombre']);
                        $usoCFDI =$Receptor['UsoCFDI'];
                     }
                }
                foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Emisor') as $Emisor){
                    if($version == '3.2'){
                        $rfce = $Emisor['rfc'];
                        $nombreE = '';
                        $regimen = '';  
                    }elseif($version == '3.3'){
                        $rfce = $Emisor['Rfc'];
                        $nombreE = utf8_encode($Emisor['Nombre']);
                        $regimen = $Emisor['RegimenFiscal'];
                    }
                }    
            }else{
                echo ("Error: No se ha logrado crear el objeto XML ($archivo)");
                return array("uuid"=>substr($archivo, -35), "tcf"=>'', "rfce"=>'',"rfcr"=>'', "fecha"=>'');
            }
        }else{
            echo ("No se ha logrado abrir el archivo ($archivo)!");  
            return array("uuid"=>substr($archivo, -35), "tcf"=>'', "rfce"=>'',"rfcr"=>'', "fecha"=>'');
        } 
        
        //echo '<br/>'.utf8_decode($nombre_recep);
        return array("uuid"=>$uuid, "tcf"=>$tipo, "rfce"=>$rfce,"rfcr"=>$rfc, "fecha"=>substr($fecha, 0,10));
    }

}      
?>
