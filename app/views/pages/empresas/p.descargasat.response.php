<br />
<div class="container">
<div class="row">
	<h3>Descarga de documentos para: <?php echo $enpresa;?></h3>
</div>
<br />
<div class="row">
	<div class="col-lg-6">
	<?php
		$items = array();
		foreach ($xmlInfoArr as $xmlInfo) {
			$items[] = (array)$xmlInfo;
		}
		print_r($items);
		/*echo json_response(array(
			'items' => $items,
			'sesion' => $descargaCfdi->obtenerSesion()
		));*/
	?>
	</div>
</div>
</div>