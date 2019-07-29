<br />
<div class="container">
<div class="row">
	<h3>Descarga de documentos para: <?php echo $nombre;?></h3>
</div>
<br />
<div class="row">
	<div class="col-lg-6">
		<form action="index.php" method="get">
            Ultima descarga: <input required type="text" class="form-control" name="fecha" value='<?php echo $fecha;?>' /><br />
            Empresa: <input required type="text" class="form-control" name="rfc" value='<?php echo $nombre;?>' /><br />
			RFC: <input required type="text" class="form-control" name="rfc" value='<?php echo $rfc;?>' /><br />
			Clave: <input required type="text" class="form-control" name="clave" value= '<?php echo $clave;?>' /><br />
            Captcha: <input required type="text" class="form-control" name="captcha" />&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $imgStr;?><br />
			<input type="submit" class="alert alert-warning" name="descargasat" value="Descargar!" >
		</form>
	</div>
</div>
</div>