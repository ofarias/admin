<br/>
<br/>
<br/>

<?php foreach ($usr as $key){ 
    $empresa = $key['empresa'];
}?>
<div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                         <h3><center>Usuarios en la empresa </center><br/></h3>
                         <h1><center><?php echo $empresa ?></center></h1>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-usr">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Usuario<br/>Login</th>
                                            <th>Nombre</th>
                                            <th>Contraseña</th>
                                            <th>Status</th>
                                            <th>Fecha Alta</th>
                                            <th>Fecha Baja</th>
                                            <th>Baja / Reactivar</th>             
                                            <th>Validar</th>
                                       </tr>
                                    </thead>                                   
                                  <tbody>
                                        <?php foreach ($usr as $key): 
                                            $color='';
                                        ?>
                                        <tr class="odd gradeX" <?php echo $color ?> >
                                            <td><?php echo $key['0'] ?></td>
                                            <td>(&nbsp;<?php echo $key['idu']?>&nbsp;)&nbsp;<?php echo $key['usuario']?></td>
                                            <td><?php echo $key['nombre'].' '.$key['segundo_nombre'].' '.$key['apellido_p'].' '.$key['apellido_m'];?> </td>
                                            <td><?php echo $key['contrasenia'];?></td>
                                            <td><?php echo $key['17'].' / '.$key['3'];?></td>
                                            <td><?php echo $key['15'];?></td>
                                            <td><?php echo $key['16'];?> </td>
                                            <td><input type="button" name="" value="<?php echo ($key['3']==1)? 'Desactivar':'Activar'?>" onclick="cambiaUsr(this.value, <?php echo $key['2']?>, <?php echo $key['idu']?>)">
                                                <input type="button" name="" value="<?php echo 'Quitar'?>" onclick="cambiaUsr(this.value, <?php echo $key['2']?>, <?php echo $key['idu']?>)">
                                            </td>
                                            <td></td>
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
                            <center>Usuarios</center><br/>
                         <h3><center>Registrados</center></h3>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-nusr">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Usuario<br/>Login</th>
                                            <th>Nombre</th>
                                            <th>Contraseña</th>
                                            <th>Status</th>
                                            <th>Fecha Alta</th>
                                            <th>Fecha Baja</th>
                                            <th>Empresas</th>                                            
                                            <th>Agregar</th>
                                       </tr>
                                    </thead>                                   
                                  <tbody>
                                        <?php foreach ($nusr as $key): 
                                            $color='';
                                        ?>
                                        <tr class="odd gradeX" <?php echo $color ?> >
                                            <td><?php echo $key['0'] ?></td>
                                            <td><?php echo $key['usuario']?></td>
                                            <td><?php echo $key['nombre'].' '.$key['segundo_nombre'].' '.$key['apellido_p'].' '.$key['apellido_m'];?> </td>
                                            <td><?php echo $key['contrasenia'];?></td>
                                            <td><?php echo $key['status'];?></td>
                                            <td><?php echo $key['fecha_alta'];?></td>
                                            <td><?php echo $key['fecha_baja'];?> </td>
                                            <td><?php echo $key['empresas']?></td>
                                            <td><input type="button" value="Asociar" id="<?php echo $key['0']?>" class="agregar" nombre="<?php echo $key['nombre'].' '.$key['segundo_nombre'].' '.$key['apellido_p'].' '.$key['apellido_m']?>" empresa="<?php echo $empresa?>" ide="<?php echo $ide?>"></td>
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

    $('.agregar').click(function(){
        var idu = $(this).attr('id')
        var usuario = $(this).attr('nombre')
        var nom_emp = $(this).attr('empresa')
        var ide = $(this).attr('ide')
        $.confirm({
            title: 'Asociar Usuario a Empresa',
            content: 'Desea asociar al usuario <b>' + usuario +'</b> a la empresa ' + '<b>( ' + ide + ' ) ' + nom_emp +'</b>' ,
            buttons: {
        OK:{
            text:'Si',
            btnClass:'btn-green',
            action:function(){
            $.confirm({
                content: function(){
                    var self = this;
                    self.setTitle('Asociar');
                    self.setContent('Se intento asociar el usuario con el siguiente resultado:');
                    return $.ajax({
                        url: 'index.php',
                        dataType: 'json',
                        type: 'post',
                        data:{asociaUsuario:1, ide, idu}
                    }).done(function (response) {
                        self.setContentAppend('<div>Se asocio Correctamente, favor de actualizar la Pantalla!</div>');
                    }).fail(function(){
                        self.setContentAppend('<div>No se pudo asociar el usuario.</div>');
                        //location.reload(true)
                    }).always(function(){
                        self.setContentAppend('<div>Favor de actualizar la pagina...</div>');
                    });
                },
                contentLoaded: function(data, status, xhr){
                    self.setContentAppend('<div>Content loaded!</div>');
                },
                onContentReady: function(){
                    this.setContentAppend('<div>Content ready!</div>');
                }
            });
        }
        },
        cancelar:{
            text:'No',
            btnClass:'btn-red',
            action:function(){
            $.alert('No se realizo ningun cambio');
        }
        }
        }
        })
        
    })

    function cambiaUsr(tipo, ide, idu){
        $.confirm({
            title:'Activar / Desactivar usuario',
            content:'Desaea ' + tipo + ' el usuario?',
            buttons:{
                ok:function(){
                    $.ajax({
                        url:'index.php',
                        type:'post',
                        dataType:'json',
                        data:{cambioUsr:1, tipo, ide, idu},
                        success:function(){
                            location.reload(true)
                        },
                        error:function(){
                            $.alert('NO se pudo actualizar el usuario, reportelo con el administrador')
                        }  
                    })
                },
                Cancelar:function(){
                    $.alert('No se realizo ningun cambio.')
                }, 
            }
        })
    }

</script>