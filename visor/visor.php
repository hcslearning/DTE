<?php

// script ejecutable desde la línea de comandos y desde la web
$shell 	= 'SHELL';
$web 	= 'WEB';

if( isset($_SERVER['SHELL']) ) {
	$env = $shell;
} else {
	$env = $web;
}

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
<table>
	<thead>
		<th>Folio</th>
		<th>Tipo</th>
		<th>Fecha</th>
		<th>De</th>
		<th>A</th>
		<th>Neto</th>
		<th>IVA</th>
		<th>Total</th>
		<th>Detalle</th>
	</thead>
<?php endif; ?>
<?php	foreach($xmlObj->DTE as $dte) {
		$tipoDTE = $dte->Documento->Encabezado->IdDoc->TipoDTE;
		$tipoDTEString = getTipoDTE( $tipoDTE );

		if( $env == $shell ) {
			echo "Folio: ".$dte->Documento->Encabezado->IdDoc->Folio."\n";
			echo "TipoDTE: ".$tipoDTEString."\n";			
			echo "Fecha: ".$dte->Documento->Encabezado->IdDoc->FchEmis."\n";
			echo "De: ".$dte->Documento->Encabezado->Emisor->RznSoc."\n";
			echo "A: ".$dte->Documento->Encabezado->Receptor->RznSocRecep."\n";
			echo "\e[32mNeto: ".moneda($dte->Documento->Encabezado->Totales->MntNeto)."\n";
			echo "IVA: ".moneda($dte->Documento->Encabezado->Totales->IVA)."\n";
			echo "Total: ".moneda($dte->Documento->Encabezado->Totales->MntTotal)."\n";
			echo "\n\e[93m";
			
			foreach( $dte->Documento->Detalle as $d ) {
				echo $d->NmbItem;
				echo " \t".$d->QtyItem;
				echo " \t".moneda( $d->PrcItem );
				echo " \t".moneda( $d->MontoItem );
				echo "\n";
			}
			echo "\n\e[31m#####################################\n\n\e[0m";
		} else {
			
		}
	}
}

