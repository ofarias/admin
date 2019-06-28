<br/>
<div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div>
                        <p><?php echo 'Usuario: '.$_SESSION['user']->NOMBRE?></p>
                        <p><?php echo 'RFC seleccionado: '.$_SESSION['rfc']?></p>
                        <p><?php echo 'Empresa Seleccionada: <b>'.$_SESSION['empresa']['nombre']."</b>"?></p>  
                        <p><?php echo 'Se muestran los XML '.$ide." del mes ".$mes." del ".$anio?></p>
                    </div>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
                                            <th>Ln</th>
                                            <th>Sta</th>
                                            <th>UUID</th>
                                            <th>TIPO</th>
                                            <th>FOLIO</th>
                                            <th>FECHA</th>
                                            <th>RFC RECEPTOR</th>
                                            <th>RFC EMISOR</th>
                                            <th>SUBTOTAL</th>
                                            <th>IVA</th>
                                            <th>RETENCION <br/>IVA</th>
                                            <th>IEPS</th>
                                            <th>RETENCION <br/>IEPS</th>
                                            <th>RETENCION ISR</th>
                                            <th>DESCUENTO</th>
                                            <th>TOTAL</th>
                                            <th>MON</th>
                                            <th>TC</th>
                                            <th>CLASIFICAR</th>
                                            <th>DESCARGA</th>                                            
                                       </tr>
                                    </thead>                                   
                                  <tbody>
                                        <?php $ln=0;
                                            foreach ($info as $key): 
                                            $color='';
                                            $ln++;
                                            $descSta = '';
                                            if($key->TIPO == 'I'){
                                                $tipo = 'Ingreso';
                                                $color =  'style="background-color: #daf2a5"';
                                            }elseif ($key->TIPO =='E') {
                                                $tipo = 'Egreso';
                                                $color = 'style="background-color:yellow"';
                                            }elseif($key->TIPO == 'P'){
                                                $tipo = 'Pago';
                                                $color = 'style="background-color:#aee7e3"';
                                            }else{
                                                $tipo = 'Desconocido';
                                                $color = 'style="background-color:brown"';
                                            }
                                            $rfcEmpresa=$_SESSION['rfc'];
                                            if($key->STATUS == 'P'){
                                                $descSta = 'Pendiente';
                                            }elseif($key->STATUS== 'D'){
                                                $descSta = 'Poliza de Dr para ver la poliza del documento da click en el UUID';
                                                $color = 'style="background-color:#f9fbae"';
                                            }elseif($key->STATUS=='I'){
                                                $descSta = 'Con Poliza de Ingreso para ver las polizas del documento da click en el UUID';
                                                $color = 'style="background-color:#a0ecfb"';
                                            }elseif($key->STATUS=='E'){
                                                $descSta = 'Con Poliza de Egreso para ver las polizas del documento da click en el UUID';
                                                $color = 'style="background-color:#bcffe9"';
                                            }
                                        ?>
                                        <tr class="odd gradeX" <?php echo $color ?> title="<?php echo $descSta?>" >
                                         <!--<tr class="odd gradeX" style='background-color:yellow;' >-->
                                            <td><?php echo $ln?></td>
                                            <td><?php echo $key->STATUS?></td>
                                            <td><a href="index.coi.php?action=verPolizas&uuid=<?php echo $key->UUID ?>" target="popup" onclick="window.open(this.href, this.target, 'width=1200,height=1320'); return false"> <?php echo $key->UUID ?></a> </td>
                                            <td><?php echo $tipo?></td>
                                            <td><?php echo $key->SERIE.$key->FOLIO?></td>
                                            <td><?php echo $key->FECHA;?> </td>
                                            <td><?php echo '('.$key->CLIENTE.')  <br/><b>'.$key->NOMBRE.'<b/>';?></td>
                                            <td><?php echo '('.$key->RFCE.')  <br/><b>'.$key->EMISOR.'<b/>'?></td>
                                            <td><?php echo '$ '.number_format($key->SUBTOTAL,2);?></td>
                                            <td><?php echo '$ '.number_format($key->IVA,2);?></td>
                                            <td><?php echo '$ '.number_format($key->IVA_RET,2);?></td>
                                            <td><?php echo '$ '.number_format($key->IEPS,2);?></td>
                                            <td><?php echo '$ '.number_format($key->IEPS_RET,2);?></td>
                                            <td><?php echo '$ '.number_format($key->ISR_RET,2);?></td>
                                            <td><?php echo '$ '.number_format($key->DESCUENTO,2);?></td>
                                            <td><?php echo '$ '.number_format($key->IMPORTE,2);?> </td>
                                            <td><?php echo '<b>'.$key->MONEDA.'<b/>';?> </td>
                                            <td><?php echo '$ '.number_format($key->TIPOCAMBIO,2);?> </td>
                                            <td><a href="index.php?action=verXML&uuid=<?php echo $key->UUID?>" class="btn btn-info" target="popup" onclick="window.open(this.href, this.target, 'width=1800,height=1320'); return false;"> Clasificar </a></td>
                                            <form action="index.php" method="POST">
                                                    <input type="hidden" name="factura" value="<?php echo $key->SERIE.$key->FOLIO?>">
                                                <td>
                                                    <a href="/uploads/xml/<?php echo $rfcEmpresa.'/Recibidos/'.$key->RFCE.'/'.$key->RFCE.'-'.$key->SERIE.$key->FOLIO.'-'.$key->UUID.'.xml'?>" download>  <img border='0' src='app/views/images/xml.jpg' width='25' height='30'></a>&nbsp;&nbsp;

                                                    <a href="index.php?action=imprimeUUID&uuid=<?php echo $key->UUID?>" ><img border='0' src='app/views/images/pdf.jpg' width='25' height='30'></a>
                                                    <!--<button name="imprimeFact" value="enviar" type="submit">Imprimir</button>-->
                                                </td>
                                            </form>
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

<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css">
<link rel="stylesheet" href="/resources/demos/style.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.js"></script>
<script type="text/javascript">

</script>