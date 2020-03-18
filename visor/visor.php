<?php

$dataDir = __DIR__ . "/data";

foreach( glob("${dataDir}/*.xml") as $filename ) {
	$xmlData 	= file_get_contents( $filename );
	$xmlObj 	= new SimpleXMLElement( $xmlData );

}

