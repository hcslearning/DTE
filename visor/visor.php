<?php

// script ejecutable desde la línea de comandos y desde la web
$shell 	= 'SHELL';
$web 	= 'WEB';

if( isset($_SERVER['SHELL']) ) {
	$env = $shell;
} else {
	$env = $web;
}
?>

<?php if( $env == $web ): ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Visor de DTEs</title>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
</head>
<body>
	<div class="container-fluid">
<?php endif; ?>

<?php
$dataDir = __DIR__ . "/data";

function getTipoDTE($tipoDTE) {
	switch($tipoDTE) {
		case 33: return 'Factura Electrónica';
		case 34: return 'Factura No Afecta o Exenta Electrónica';
		case 61: return 'Nota de Crédito Electrónica';
		case 56: return 'Nota de Débito Electrónica';
		case 52: return 'Guía de Despacho Electrónica';
		default: return $tipoDTE;
	}
}

function moneda($monto) {
	$monto = floatval( $monto );
	return '$'.number_format( $monto, 0, ',', '.' );
}

foreach( glob("${dataDir}/*.xml") as $filename ) {
	$xmlData 	= file_get_contents( $filename );
	$xmlObj 	= new SimpleXMLElement( $xmlData );
?>
<?php if($env == $web): ?>
<table class="table table-striped table-dark">
	<thead class="thead-dark">
		<th scope="col">#</th>
		<th scope="col">Folio</th>
		<th scope="col">Tipo</th>
		<th scope="col">Fecha</th>
		<th scope="col">De</th>
		<th scope="col">A</th>
		<th scope="col">Neto</th>
		<th scope="col">IVA</th>
		<th scope="col">Total</th>
		<th scope="col">Detalle</th>
	</thead>
	<tbody>
<?php endif; ?>
<?php	
$count = 1;
	foreach($xmlObj->DTE as $dte) {
		$tipoDTE 		= $dte->Documento->Encabezado->IdDoc->TipoDTE;
		$tipoDTEString 	= getTipoDTE( $tipoDTE );
		$emisor 		= ($dte->Documento->Encabezado->Emisor->RUTEmisor == '76342262-3')?'TICBiz EIRL':$dte->Documento->Encabezado->Emisor->RznSoc;
		$receptor 		= ($dte->Documento->Encabezado->Receptor->RUTRecep == '76809155-2')?'Carosanti':$dte->Documento->Encabezado->Receptor->RznSocRecep;

		if( $env == $shell ) {
			echo "Cant: ".$count."\n";
			echo "Folio: ".$dte->Documento->Encabezado->IdDoc->Folio."\n";
			echo "TipoDTE: ".$tipoDTEString."\n";			
			echo "Fecha: ".$dte->Documento->Encabezado->IdDoc->FchEmis."\n";
			echo "De: ".$emisor."\n";
			echo "A: ".$dte->Documento->Encabezado->Receptor->RznSocRecep."\n";
			echo "\e[32mNeto: ".moneda($dte->Documento->Encabezado->Totales->MntNeto)."\n";
			echo "IVA: ".moneda($dte->Documento->Encabezado->Totales->IVA)."\n";
			echo "Total: ".moneda($dte->Documento->Encabezado->Totales->MntTotal)."\n";
			echo "\n\e[93m";
			
			foreach( $dte->Documento->Detalle as $d ) {
				$cant = intval( $d->QtyItem );
				echo "$cant x ";
				echo $d->NmbItem;				
				echo " \t".moneda( $d->PrcItem );
				echo " \t".moneda( $d->MontoItem );
				echo "\n";
			}
			echo "\n\e[31m#####################################\n\n\e[0m";
		} else {
		?>
			<tr>
				<th scope="row">#<?=$count?></th>
				<td><?=$dte->Documento->Encabezado->IdDoc->Folio?></td>
				<td><?=$tipoDTEString?></td>
				<td><?=$dte->Documento->Encabezado->IdDoc->FchEmis?></td>
				<td><?=$emisor?></td>
				<td><?=$receptor?></td>
				<td><?=moneda($dte->Documento->Encabezado->Totales->MntNeto)?></td>
				<td><?=moneda($dte->Documento->Encabezado->Totales->IVA)?></td>
				<td><?=moneda($dte->Documento->Encabezado->Totales->MntTotal)?></td>
				<td>
					<?php foreach( $dte->Documento->Detalle as $d ):
						$monto = moneda( $d->MontoItem );
						$cant = intval( $d->QtyItem );
					?>
						<?="{$cant} x {$d->NmbItem} = {$monto} <br />"?>
					<?php endforeach; ?>
				</td>
			</tr>
		<?php
		}
		
		$count++;
	}
?>

<?php if( $env == $web ): ?>
	</tbody>
</table>
<?php endif; ?>

<?php
}
?>

<?php if( $env == $web ): ?>
	</div><!-- end div.container -->
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
	</body>
</html>
<?php endif; ?>
