<?php
$tipo = null;
switch (empty($_GET['tipo']) ? null : $_GET['tipo']) {
	case 'fiel':
		$tipo = 'fiel';
		break;
	case 'ciecc':
	default:
		$tipo = 'ciecc';
		require '../../../app/lib/DescargaMasivaCfdi.php';
		$descargaCfdi = new DescargaMasivaCfdi;
		break;
	
}
?>
<!DOCTYPE html>
<html lang="es">
  <head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Descarga Masiva de CFDIs</title>
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<link href="css/styles.css" rel="stylesheet">
		<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>
		<nav class="navbar navbar-inverse navbar-fixed-top">
			<div class="container-fluid">
				<div class="navbar-header">
					<span class="navbar-brand">Descarga Masiva de CFDIs 9.8</span>
				</div>
				<div class="collapse navbar-collapse">
					<ul class="nav navbar-nav navbar-right">
						<li class="<?php echo ($tipo == 'ciecc') ? 'active' : '' ?>"><a href="?tipo=ciecc">CIEC con Captcha</a></li>
						<li class="<?php echo ($tipo == 'fiel') ? 'active' : '' ?>"><a href="?tipo=fiel">FIEL (recomendado)</a></li>
					</ul>
				</div>
			</div>
		</nav>

	    <div id="main">
			<div class="container-fluid">
<?php
if($tipo == 'fiel'){
	echo '<h2>Inicio de sesión con FIEL</h2>';
	require 'form-login-fiel.inc.php';
}else{
	echo '<h2>Inicio de sesión con CIEC/Captcha</h2>';
	require 'form-login-ciec-captcha.inc.php';	
}
?>
				<hr/>

				<h2>Descarga</h2>
				<div class="tablas-resultados">
					<div class="overlay"></div>
					<ul class="nav nav-tabs" role="tablist">
						<li role="presentation" class="active"><a href="#recibidos" aria-controls="recibidos" role="tab" data-toggle="tab">Recibidos</a></li>
						<li role="presentation"><a href="#emitidos" aria-controls="emitidos" role="tab" data-toggle="tab">Emitidos</a></li>
					</ul>
					<div class="tab-content">
					    <div role="tabpanel" class="tab-pane active" id="recibidos">
					    	<?php require 'form-recibidos.inc.php'; ?>
							<form method="POST" class="descarga-form">
								<input type="hidden" name="accion" value="descargar-recibidos" />
								<input type="hidden" name="r" id="rfc_a" value="">
								<input type="hidden" name="sesion" class="sesion-ipt" />
								<div style="overflow:auto">
									<table class="table table-hover table-condensed" id="tabla-recibidos">
										<thead>
											<tr>
												<th class="text-center">XML</th>
												<th class="text-center">Acuse</th>
												<th>Efecto</th>
												<th>Razón Social</th>
												<th>RFC</th>
												<th>Estado</th>
												<th>Folio Fiscal</th>
												<th>Emisión</th>
												<th>Total</th>
												<th>Certificación</th>
												<th>Cancelación</th>
												<th>PAC</th>
											</tr>
										</thead>
										<tbody></tbody>
									</table>
								</div>
								<div class="text-right">
									<a href="#" class="btn btn-primary excel-export" download="cfdi_recibidos.xls">Exportar a Excel</a>
									<button type="submit" class="btn btn-success" onclick="valorRFC()">Descargar seleccionados</button>
								</div>
							</form>
					    </div>
					    <div role="tabpanel" class="tab-pane" id="emitidos">
							<?php require 'form-emitidos.inc.php'; ?>
							<form method="POST" class="descarga-form">
								<input type="hidden" name="accion" value="descargar-emitidos" />
								<input type="hidden" name="sesion" class="sesion-ipt" />
								<input type="hidden" name="r" id="rfc_b" value="">
								<div style="overflow:auto">
									<table class="table table-hover table-condensed" id="tabla-emitidos">
										<thead>
											<tr>
												<th class="text-center">XML</th>
												<th class="text-center">Acuse</th>
												<th>Efecto</th>
												<th>Razón Social</th>
												<th>RFC</th>
												<th>Estado</th>
												<th>Folio Fiscal</th>
												<th>Emisión</th>
												<th>Total</th>
												<th>Certificación</th>
												<th>PAC</th>
											</tr>
										</thead>
										<tbody></tbody>
									</table>
								</div>
								<div class="text-right">
									<a href="#" class="btn btn-primary excel-export" download="cfdi_emitidos.xls">Exportar a Excel</a>
									<button type="submit" class="btn btn-success" onclick="valorRFC()">Descargar seleccionados</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script src="js/jquery-3.1.1.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/code.js"></script>
		<script type="text/javascript">
			function valorRFC(){
				var a=document.getElementById('login-ciec-rfc')
				var rf =a.value
				document.getElementById('rfc_a').value=rf
				document.getElementById('rfc_b').value=rf

			}
		</script>
	</body>
</html>

