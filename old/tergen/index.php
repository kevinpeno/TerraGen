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
	set_include_path( realpath( dirname(__FILE__) ).DIRECTORY_SEPARATOR.'libs'.DIRECTORY_SEPARATOR.PATH_SEPARATOR.get_include_path() );

	require_once('Utilities/AutoLoad.php');
	
	$generator = Terra_HeightMap::getGenerator( 'DiamondSquare', $_GET['lvldiv'] );
	$map = new Terra_HeightMap( pow( 2, $_GET['size'] ), (int)$_GET['seed'] ? $_GET['seed'] : NULL );

	$generator->generate( $map );

	header('Content-Type: image/png');
	$map->getImage(true);
	exit();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<title>Terrain Generator</title>
</head>

<body>
<h1>Terrain Generator</h1>
<p>After submitting, please allow time for page to load...</p>
<form action="" method="get">
	<fieldset>
		<input name="size" type="text" value="7" />
		<label>Size of map as power of 2. Large values may crash the process. (Keep below 9 )</label><br />
		<input name="seed" type="text" value="0" />
		<label>Seed the randomizer to regenerate a previous map. 0 will use a random seed.</label><br />
		<input name="lvldiv" type="text" value="2" />
		<label>Larger values will result in smoother looking terrain (default 2).</label><br />
		<input name="sealevel" type="text" value="50" />%
		<label>Sea Level. Used to shift Sea Level up or down. Must be greater than 0.</label><br />
		<select name="maptype">
			<option value="t">Terrain Map</option>
			<option value="h">Height Map</option>
		</select>
		<input name="submit" type="submit" value="Generate Map" />
		<input type="reset" value="Reset" />
	</fieldset>
</form>
</body>
</html>