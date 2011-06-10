<?php
error_reporting( E_ALL | E_STRICT );
ini_set( 'display_errors', true );
set_time_limit(0);
if( isset( $_GET['source'] ) )
{
	highlight_file( __FILE__ );
	exit();
}

if( isset( $_GET['submit'] ) )
{

	require 'CompressedDiamondSquareTerrainGenerator.php';
	
	$mapper = new CompressedDiamondSquareTerrainGenerator( pow( 2, $_GET['size'] ), ( $_GET['sealevel'] / 100 ), $_GET['seed'], $_GET['lvldiv'] );
	$mapper->buildHeightMap();

	header('Content-Type: image/png');

	switch( $_GET['maptype'] )
	{
		case 'h':
			imagepng( $mapper->getHeightMap() );
		break;
		case 't':
			$grad = ( isset( $_GET['grad'] ) && file_exists( 'altg0'.(int)$_GET['grad'].'.png' ) ) ? ( 'altg0'.(int)$_GET['grad'].'.png' ) : 'altg01.png';
			$mapper->drawTerrainMap( $grad );
			imagepng( $mapper->getTerrainMap() );
		break;
	}

	exit();
}?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Terrain Generator</title>
</head>

<body>
<h1>Terrain Generator</h1>
<p>After submitting, please allow time for page to load...</p>
<form action="" method="get">
	<fieldset>
		<input type="text" name="size" value="7"/>
		<label>Size of map as power of 2. Large values may crash the process. (Keep below 9 )</label><br />
		<input type="text" name="seed" value="0"/>
		<label>Seed the randomizer to regenerate a previous map. 0 will use a random seed.</label><br />
		<input type="text" name="lvldiv" value="2"/>
		<label>Larger values will result in smoother looking terrain (default 2).</label><br />
		<input type="text" name="sealevel" value="50"/>%
		<label>Sea Level. Used to shift Sea Level up or down. Must be greater than 0.</label><br />
		<select name="maptype">
			<option value="t">Terrain Map</option>
			<option value="h">Height Map</option>
		</select>
		<input type="submit" name="submit" value="Generate Map"/>
		<input type="reset" value="Reset"/>
	</fieldset>
</form>
</body>
</html>

