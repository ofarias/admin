<br/>
<div style="float: left; width: 400px;">
    <input type="button" name="grabaP" onclick="grabaParam()" class="btn btn-info" value="Guarda Parametros">
<br/><br/>
    <input type="button" name="grabaP" onclick="crearPolizas()" class="btn btn-success" value="Crear Polizas">

</div>
<div style="float: left; width: 400px;" >
    <p>Cuenta de IVA : &nbsp;&nbsp;&nbsp; <?php echo '<b>'.$cimpuestos['iva'].'</b>' ?></p>
    <p>Cuenta de IEPS : &nbsp; <?php echo '<b>'.$cimpuestos['ieps'].'</b>'?></p>
    <p>Cuenta de ISR : &nbsp;&nbsp;&nbsp;<?php echo '<b>'.$cimpuestos['isr'].'</b>'?> </p>
</div>

<br/><br/>
 <?php foreach ($infoCabecera as $key0){ 
        $rfcEmpresa = $_SESSION['rfc'];
        $rfce = $key0->RFCE;
        $rfc=$key0->CLIENTE;
        $serie=$key0->SERIE;
        $folio=$key0->FOLIO;
        $uuid=$key0->UUID;
    }
?>

<?php $dig=$param->NIVELACTU;
    for ($i=1; $i <= $dig ; $i++) { 
        $a = "DIGCTA".$i;
        $c=$param->$a;    
        $p[]=($c);
    }                                                         
?>

<div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div>
                                <p><?php echo 'Usuario: '.$_SESSION['user']->NOMBRE?></p>
                                <p><?php echo 'RFC seleccionado: '.$_SESSION['rfc']?>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <a href="/uploads/xml/<?php echo $rfcEmpresa.'/Recibidos/'.$rfce.'/'.$rfce.'-'.$serie.$folio.'-'.$uuid.'.xml'?>" download>  <img border='0' src='app/views/images/xml.jpg' width='55' height='60'></a>&nbsp;&nbsp;
                                        <a href="javascript:impFact(<?php echo "'".$serie.$folio."'"?>)" download><img border='0' src='app/views/images/pdf.jpg' width='55' height='60'></a>
                                </p>
                                <p><?php echo 'Empresa Seleccionada: <b>'.$_SESSION['empresa']['nombre']."</b>"?></p>  
                               

                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" >
                                    <thead>
                                        <tr>
                                            <th>UUID</th>
                                            <th>TIPO</th>
                                            <th>DOCUMENTO</th>
                                            <th>FECHA</th>
                                            <th>RFC Emisor</th>
                                            <th>RFC Receptor</th>
                                            <th>SUBTOTAL</th>
                                            <th>IVA</th>
                                            <th>RETENCION <br/>IVA</th>
                                            <th>IEPS</th>
                                            <th>RETENCION IEPS</th>
                                            <th>RETENCION <br/>ISR</th>
                                            <th>DESCUENTO</th>
                                            <th>TOTAL</th>                    
                                       </tr>
                                    </thead>                                   
                                  <tbody>
                                        <?php foreach ($infoCabecera as $key): 
                                            $color='';
                                            if($key->TIPO == 'I'){
                                                $tipo = 'Ingreso';
                                                $color =  'style="background-color:orange"';
                                            }elseif ($key->TIPO =='E') {
                                                $tipo = 'Egreso';
                                                $color = 'style="background-color:yellow"';
                                            }
                                            $rfc=$key->CLIENTE;
                                        ?>
                                        <tr class="odd gradeX" <?php echo $color ?> >
                                         <!--<tr class="odd gradeX" style='background-color:yellow;' >-->
                                            <td> <?php echo $key->UUID ?> </td>
                                            <td><?php echo $tipo?></td>
                                            <td><?php echo $key->SERIE.$key->FOLIO?></td>
                                            <td><?php echo $key->FECHA;?> </td>
                                            <td><?php echo '<b>('.$key->RFCE.')'.$key->EMISOR."</b>"?></td>
                                            <td><?php echo '<b>('.$key->CLIENTE.')  '.$key->NOMBRE."</b>";?></td>
                                            <td><?php echo '$ '.number_format($key->SUBTOTAL,2);?></td>
                                            <td><?php echo '$ '.number_format($key->IVA160,2);?></td>
                                            <td><?php echo '$ '.number_format($key->IVA_RET,2);?></td>
                                            <td><?php echo '$ '.number_format($key->IEPS,2);?></td>
                                            <td><?php echo '$ '.number_format($key->IEPS_RET,2);?></td>
                                            <td><?php echo '$ '.number_format($key->ISR_RET,2)?></td>
                                            <td><?php echo '$ '.number_format($key->DESCUENTO,2)?></td>
                                            <td><?php echo '$ '.number_format($key->IMPORTE,2);?> </td>
                                        </tr>
                                        <tr style="background-color:#DFCFF1">
                                            <td colspan="14">
                                                <b><?php echo '<b>Cuenta Actual: '.$cccliente.'<b/>'?></b>
                                                <select id="cClie" >
                                                    <option value="">Cuentas COI </option>
                                                    <?php foreach($ccC as $data):?>
                                                        <option value="<?php echo $rfc.':'.$data->NUM_CTA.':'.$key->UUID.':'.$key->RFCE?>"><?php echo $data->NOMBRE.'('.$data->NUM_CTA.')'?></option>
                                                    <?php endforeach;?>

                                                </select>
                                            </td>
                                        </tr>
                                        </form>
                                        <?php endforeach; ?>
                                 </tbody>  
                                </table>
                            </div>
                      </div>
            </div>
        </div>
</div>
<div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" >
                                    <thead>
                                        <tr>
                                            <th>Part</th>
                                            <th>Doc</th>
                                            <th>Unidad</th>
                                            <th>Cantidad</th>
                                            <th>Descripcion</th>
                                            <th>Unitario</th>
                                            <th>Importe</th>
                                            <th>Descuento</th>
                                            <th>Clave SAT</th>
                                            <th>Unidad SAT</th>
                                            <th>Retencion <br/>ISR (001)</th>                                            
                                            <th>Traslado <br/>IVA (002)</th>                                            
                                            <th>Retencion <br/>IVA (002)</th>                                            
                                            <th>Traslado <br/>IEPS (003)</th>                                            
                                            <th>Retencion<br/>IEPS (003)</th>                                            
                                       </tr>
                                    </thead>                                   
                                  <tbody>
                                        <?php
                                            $ln= 0;
                                            foreach ($info as $key): 
                                                $color='';
                                                $ln++;
                                                $ccp='Sin Cuenta Definida';
                                                foreach($ccpartidas as $key0){
                                                    $cuenta = $key0->NUM_CTA;
                                                    if($key->CUENTA_CONTABLE ==$cuenta){
                                                        $ccp=$key0->NUM_CTA.'-->'.$key0->NOMBRE;
                                                    }
                                                }
                                        ?>
                                        <tr class="odd gradeX" <?php echo $color ?> >
                                         <!--<tr class="odd gradeX" style='background-color:yellow;' >-->
                                            <td><?php echo $key->PARTIDA?></td>
                                            <td> <?php echo $key->DOCUMENTO ?> </td>
                                            <td><?php echo $key->UNIDAD;?> </td>
                                            <td><?php echo $key->CANTIDAD;?></td>
                                            <td><?php echo $key->DESCRIPCION?></td>
                                            <td><?php echo '$ '.number_format($key->UNITARIO,6);?></td>
                                            <td><?php echo '$ '.number_format($key->IMPORTE,6);?> </td>
                                            <td><?php echo '$ '.number_format($key->DESCUENTO,2)?></td>
                                            <td><?php echo $key->CLAVE_SAT.'<br/><font color ="blue" size="1.5pxs">'.$key->DESCSAT.'</font>'?></td>
                                            <td><?php echo $key->UNIDAD_SAT?></td>
                                            <td><?php echo '$ '.number_format($key->ISR,2).'<br/>'.$key->FACT_ISR.'<br/>'.$key->TASA_ISR.'<br/><b>Base:'.number_format($key->B_ISR,2)?></td>
                                            <td><?php echo '$ '.number_format($key->IVA,2).'<br/>'.$key->FACT_IVA.'<br/>'.$key->TASA_IVA.'<br/><b>Base:'.number_format($key->B_IVA,2)?></td>
                                            <td><?php echo '$ '.number_format($key->IVA_R,2).'<br/>'.$key->FACT_IVA_R.'<br/>'.$key->TASA_IVA_R.'<br/><b>Base:'.number_format($key->B_IVA_R,2)?></td>
                                            <td><?php echo '$ '.number_format($key->IEPS,2).'<br/>'.$key->FACT_IEPS.'<br/>'.$key->TASA_IEPS.'<br/><b>Base:'.number_format($key->B_IEPS,2)?></td>
                                            <td><?php echo '$ '.number_format($key->IEPS_R,2).'<br/>'.$key->FACT_IEPS_R.'<br/>'.$key->TASA_IEPS_R.'<br/><b>Base:'.number_format($key->B_IEPS_R,2)?></td>
                                            </td>
                                            <tr style="background-color:#DFCFF1">
                                                <td colspan="14">
                                                    <?php echo '<b>Cuenta Actual: '.$ccp.'#### Cambiar Cuenta --><b>'?>
                                                    <input type="text" name="cuenta" placeholder="Cuenta Contable" class="cuencont" size="120" id="cPP_<?php echo $key->PARTIDA?>" 
                                                    valor="<?php echo $key->PARTIDA.':'.$key->CLAVE_SAT.':'.$key->UNIDAD_SAT.':'?>" rfc="<?php echo ':'.$rfce?>" >
                                                </td>    
                                            </tr>
                                        </tr>
                                        <?php endforeach; ?>
                                        <input type="hidden" name="partidas" id="partidas" value="<?php echo $ln?>" >
                                 </tbody>
                                </table>
                            </div>
                      </div>
            </div>
        </div>
</div>
<input type="hidden" name="u" id='uuid' value='<?php echo $uuid?>' doc="<?php echo $serie.$folio?>">
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css">
<link rel="stylesheet" href="/resources/demos/style.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.js"></script>
<script type="text/javascript">

    $(".cuencont").autocomplete({
        source: "index.coi.php?cuentas=1",
        minLength: 3,
        select: function(event, ui){
        }
    })

    function grabaParam(){
        var part=document.getElementById("partidas").value;
        alert('Total de Partidas' + part);
        var partidas = '';
        for (var i = part; i >= 1; i--) {
            //alert('Partida: '+ i);
            //var valPar = document.getElementById("cP_"+i).value;
            Par = document.getElementById("cPP_"+i).getAttribute('valor');
            var rfc = document.getElementById("cPP_"+i).getAttribute('rfc');
            var cuentaNueva = document.getElementById("cPP_"+i).value
            var t = cuentaNueva.split(":")
            cuenta = t[7]
            valPar = Par+cuenta+rfc
            if(valPar == ''){
                var ln = parseFloat(i) + 0;
                alert ('Revise por favor la partida ' + ln + ' ya que no cuenta con un valor, gracias...');
                return false;
            }else{
                partidas=partidas +'###'+valPar;
            }
        }
        partidas = partidas.substring(3);
        var cclie = document.getElementById("cClie").value;
        if(cclie == ''){
            alert ('El proveedor debe de tener un valor en el catalogo de cuentas, favor de revisar la informacion.');
            return false;
        }else{
            if(confirm('Se graba parametros, desea continuar?')){
                $.ajax({
                    url:'index.coi.php',
                    type:'post',
                    dataType:'json',
                    data:{creaParam:1,cliente:cclie,partidas},
                    success:function(data){
                        alert(data.mensaje);
                    },
                    error:function(data){
                        alert('<b>Es penoso, pero un error no calculado ocurrio, favor de intentar de nuevo, de cualquier forma ya se levanto un ticket con el error, le pedimos sea paciente</b>');
                    }
                })
            }
        }
    }

    function impFact(factura){
        alert('Proximamente');
            $.ajax({
                url:'index.php',
                type:'post',
                dataType:'json',
                data:{imprimeFact:1, factura:factura},
                success:function(data){
                }
            })
        return;     
        }

    function crearPolizas(){
        var ent= document.getElementById('uuid');
        var uuid = ent.value;
        var docu = ent.getAttribute('doc'); 
        var tipo = 'Dr';
        if(confirm('Desea Realizar la poliza de Dr de Documento '+docu+' con estos parametros seleccionados?')){
            $.ajax({
                url:'index.coi.php',
                type:'post',
                dataType:'json',
                data:{creaPoliza:tipo,uuid},
                success:function(data){
                    alert(data.mensaje + ': ' + data.poliza + ' en el  Periodo ' + data.periodo + ' del Ejercicio ' + data.ejercicio + 'Favor de revisar en COI ');
                }
            })
        }else{
            alert('No se ha realizado ningun cambio');
        }
    }
        
</script>