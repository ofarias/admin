<br/>
<br/>
<div class="row">
        <div class="col-lg-12">
            <p><label> Bienvenido: <?php echo $usuario?></label></p>
                <!--<input type="button" name="acomodo" onclick="acomodar()" value="Renombrar UUID" class="btn btn-success">-->
                <a href="app/descargasat/html/index.php" target="_Blank" onclick="windows.open(this.href, this.target )" class="btn btn-success">Descarga SAT</a>
                <br/>
        <!--<p><label><?php echo $_SESSION['user'][0][1].'<br/>'?></label></p>-->
            <br/>
            <div class="panel panel-default">
                <div class="panel-heading">
                        Empresas.
                </div>
                   <div class="panel-body">
                    <div class="table-responsive">                            
                        <table class="table table-striped table-bordered table-hover" id="dataTables-empresas" >
                            <thead>
                                <tr>
                                    <th>id</th>
                                    <th width="15%">Empresa</th>
                                    <th width="5%">Usuarios</th>
                                    <th width="10%">Fecha Alta</th>
                                    <th width="10%">Fecha Baja</th>
                                    <th width="10%">Usuario Alta</th>
                                    <th>Coi</th>
                                    <th>Sae</th>
                                    <th>Nomina</th>
                                    <th>App</th>
                                    <th>Impuestos</th>
                                    <th>BD</th>
                                    <th>Timbrado</th>
                                    <th>R.F.C.</th>
                                    <th>Clave</th>
                                    <th>Logo</th>
                                    <th>Fecha Inicio</th>
                                    <th>Cambiar</th>
                                    <th>Imagen</th>
                                </tr>
                            </thead>
                          <tbody>
                                <?php
                                foreach ($empresas as $data): 
                                    $color = '';
                                    ?>
                                <tr class="odd gradeX" <?php echo $color;?> >
                                    <td><?php echo $data['ide']?></td>
                                    <td><a href="index.php?action=usXemp&ide=<?php echo $data['ide']?>" target="popup" onclick="window.open(this.href, this.target,'width=600, height=800'); return false;" ><b><?php echo $data['nombre'] ?></b></a>
                                        <br/>Auto?&nbsp;&nbsp; <input type="checkbox" name="auto"  <?php echo ($data['auto']== 'si')? 'checked':''?> onchange="auto(<?php echo $data['ide']?>)" id="a_<?php echo $data['ide']?>">
                                    </td>
                                    <td><input type="number" step="1" id="usrs_<?php echo $data['ide']?>" placeholder="<?php echo $data['usuarios'];?>" Style='width:60'></td>
                                    <td><?php echo $data['fecha_alta'];?> </td>
                                    <td><?php echo $data['fecha_baja'];?></td>
                                    <td><?php echo $data['usuario_alta'];?></td>
                                    <td><?php echo $data['ruta_coi'];?></td>
                                    <td align="center"><font color="blue"><?php echo $data['sae']==1? 'Si':'No'?></font> <br/>
                                            <select id="s_<?php echo $data['ide']?>">
                                                <option><?php echo ($data['sae']==1)? 'Si':'No'?></option>
                                                <option><?php echo ($data['sae']==1)? 'No':'Si'?></option>
                                            </select>
                                    </td>
                                    <td align="center"><font color="blue"><?php echo $data['noi']==1? 'Si':'No'?></font> <br/>
                                            <select id="n_<?php echo $data['ide']?>">
                                                <option><?php echo ($data['noi']==1)? 'Si':'No'?></option>
                                                <option><?php echo ($data['noi']==1)? 'No':'Si'?></option>
                                            </select>
                                    </td>
                                    <td align="center"><font color="blue"><?php echo $data['mobile']==1? 'Si':'No'?></font> <br/>
                                            <select id="m_<?php echo $data['ide']?>">
                                                <option><?php echo ($data['mobile']==1)? 'Si':'No'?></option>
                                                <option><?php echo ($data['mobile']==1)? 'No':'Si'?></option>
                                            </select>
                                    </td>
                                    <td align="center"><font color="blue"><?php echo $data['impuestos']==1? 'Si':'No'?></font> <br/>
                                            <select id="i_<?php echo $data['ide']?>">
                                                <option><?php echo ($data['impuestos']==1)? 'Si':'No'?></option>
                                                <option><?php echo ($data['impuestos']==1)? 'No':'Si'?></option>
                                            </select>
                                    </td>
                                    <td><?php echo $data['ruta_bd']?></td>
                                    <td><?php echo $data['timbrado']?></td>
                                    <td><?php echo $data['rfc']?></td>
                                    <td><?php echo $data['cve']?><a href="app/descargasat/html/index.php" target="_Blank" onclick="windows.open(this.href, this.target )" class="btn btn-success">Descarga SAT</a></td>
                                    <td><?php echo $data['logo']?><br>
                                        <form action="carga_logo.php" method="post" enctype="multipart/form-data">
                                            <input type="file" name="fileToUpload">
                                            <input type="hidden" name="ide" value="<?php echo $data['ide']?>">
                                            <input type="submit" name="cargaLogo" value="Cargar Logotipo" class="btn btn-success btn-sm">
                                        </form>
                                    </td>
                                    <td><input type="button" name="baja" value="Suspender" onclick="suspender(<?php echo $data['ide']?>)">
                                        <br/><b><?php echo empty($data['fecha_inicio'])? '':'<br/>'.$data['fecha_inicio'].'<br/>' ?></b>
                                        <br/>
                                        <input type="date" name="fechaIni" id="fi_<?php echo $data['ide']?>">
                                        <input type="button" name="" value="Cambiar" class="btn btn-success" onclick="cambiaFecha(<?php echo $data['ide']?>)"> 
                                    </td>
                                    <td>
                                         <a class="cambio" trx="s_<?php echo $data['ide']?>"><font color="blue" >Cambiar</font></a>
                                    </td>
                                    <td>
                                        <?php if(!empty($data[22])){?>
                                        <img src="logo.php?ide=<?php echo $data['ide']?>&tam=1" alt="Imagen desde Blob">
                                        <?php }?>
                                    </td>
                                </tr> 
                                <?php endforeach; 
                                ?>
                         </tbody>
                         </table>
                         <input type="button" name="" value="Alta de Empresa" class="btn btn-success" onclick="AltaEmpresa()">
                </div>
            </div>
        </div>
    </div>
</div>  
<script type="text/javascript">
    function AltaEmpresa(){
        alert('Alta empresa')
        window.open("index.php?action=AltaEmpresa", "_self")
    }


    function auto(ide){
        var val = document.getElementById('a_'+ide)
        var v = '';

        if(val.checked){
            v = 'si'
        }else{
            v = 'no'
        }

        $.confirm({
            title:'Desactivacion de Sistema Automatico.',
            content:'Se desactivara la creacion de polizas de forma Automatica.',
            buttons:{
                specialKey:{
                    text:'si',
                    keys:['enter'],
                    action:function(){
                    $.ajax({
                        url:'index.php',
                        type:'post',
                        dataType:'json',
                        data:{cambiaModo:v, ide},
                        success:function(data){
                            $.alert("Se cambio a modo " + data.modo +" correctamente.")
                        }
                    });
                    }
                },
                ecapar:{
                    text:'no',
                    keys:['Esc'],
                    action:function(){
                        if(v == 'no'){
                            val.checked =true
                             $.alert('No se realizo nigun cambio')
                        }else{
                            val.checked =false
                            $.alert('No se realizo nigun cambio')
                        }
                    }
                }   
            }
        });
    }   

    function cambiaFecha(ide){
        var fecha=document.getElementById('fi_'+ide).value
        if(fecha ==''){
            $.alert('Favor de capturar una fecha de inicio')
            return false
        }
        $.confirm({
            title: 'Cambia de Fecha inicio de polizas',
            content: 'Se procesaran los xml a partir del ' + fecha ,
            buttons:{
                Aceptar:function(){
                    $.ajax({
                        url:'index.php',
                        type:'post',
                        dataType:'json',
                        data:{cambiaFecha:1,ide, fecha},
                        success:function(){
                             location.reload(true)
                        },
                        error:function(){
                            $.alert('No se pudo realizar el cambio, favor de reportar al Administrador')
                        }
                    })
                }, 
                Cancelar:function(){
                    $.alert('No se realizo ningun cambio')
                }
            }
        });
    }

    function acomodar(){
        $.ajax({
            url:'index.php',
            type:'post',
            dataType:'json',
            data:{acomodar:1},
            success:function(){

            },
            error:function(){
                alert('Error')
            }
        });
    }
</script>