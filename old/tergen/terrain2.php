<?php
error_reporting( E_ALL | E_STRICT);
ini_set('display_errors', true);
//ini_set( 'memory_limit', '2048M' );

print '<pre>';

require 'mapgen2.php';

$mapper = new DiamondSquareTerrainGenerator();
$map = $mapper->buildHeightMap();
var_dump( $map );

$mapper->drawMap();
//$mapper->drawMap('altg01.png');

//header('Content-Type: image/png');
ob_start();
imagepng( $mapper->getHeightMap() );
//$test = imagepng( $mapper->getTerrainMap() );
$blah = ob_get_contents();
ob_clean();
//var_dump( $map );
//exit( $blah );
?>