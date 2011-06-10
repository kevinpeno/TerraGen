<?php
error_reporting( E_ALL | E_STRICT );
ini_set( 'display_errors', true );
ob_start();
include_once 'room.php';
$minwidth = 1;
$maxwidth = 10;
$minlength = 1;
$maxlength = 10;
$num_rooms = rand( 20, 70 );

$out = "";
$out1 = "";
$out2 = "";

for ($i=0; $i < $num_rooms; $i++) {
  $rooms[$i] = new room($maxlength, $minlength, $maxwidth, $minwidth); 
}
?>

<html>
<head>
	<style type="text/css">
		.land{
			background-color:#87421F;
		}
		.ocean{
			background-color: #42C0FB;
		}
		.land, .ocean {
			float: left;
			height: 5px;
			width: 5px;
		}
		.map {
			height: 400px;
			width: 400px;
			overflow: hidden;
		}
	</style>
<body align="center">

<?php
for ($i=0; $i < 80; $i++) {
  for ($j=0; $j < 80; $j++) {
    $roomblock = 0;
    for ($k=0; $k < $num_rooms; $k++) {
      if ($rooms[$k]->in_room($i, $j)) {
        $roomblock = 1;
      }
    }
	$class = $roomblock == 1 ? 'land' : 'ocean';
	$out .= "<div class=\"{$class}\"><!--space--></div>\r\n";
  }
}
?>
<div class="map">
	<?php echo $out;?>
</div>

</body>
</html>
<?php
ob_flush();
exit();
?>

