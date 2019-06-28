<br />
<div class="container">
<div class="row">
	<h3>Formulario para alta de Empresas</h3>
</div>
<br />
<div class="row">
	<div class="col-lg-6">
		<form action="index.php" method="post">
			<input required type="text" class="form-control" name="empresa" placeholder="Razon Social" /><br />
			<input required type="number" class="form-control" name="usuarios" placeholder="Usuarios" /><br />
			Integracion con Coi: <input type="checkbox" name="coi" value="1" /><br/>
			Integracion con Noi: <input type="checkbox" name="noi" value="1"/><br/>
			Integracion con Sae: <input type="checkbox" name="sae" value="1"/><br/><br/>
			<input required type="text" class="form-control" name="serverCOI" placeholder ="Servidor COI"/><br/>
			<input required type="text" class="form-control" name="bd_coi" placeholder ="Ruta COI"/><br/>
			<input required type="text" class="form-control" name="bd" placeholder ="Ruta BD"/><br/>
			<input required type="text" class="form-control" name="tim" placeholder ="Timbrado" /><br />	
			<input required type="text" class="form-control" name="rfc" placeholder ="RFC" minlength="12" maxlength="13" /><br />	
			<input required type="text" class="form-control" name="cveSat" placeholder ="Clave Recuperar Facturas SAT" /><br />	
			<input required type="text" class="form-control" name="rtaLog" placeholder ="Ruta Logotipo" /><br />	
			<input type="submit" class="alert alert-warning" name="altaempresa" value="Dar de Alta!" >
		</form>
	</div>
</div>
</div>