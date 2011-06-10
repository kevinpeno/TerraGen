<?php
$imgX = 800;
$imgY = 400;

$img = imagecreatetruecolor($imgX, $imgY);

for ($x = 0; $x < $imgX; ++$x) {
    for ($y = 0; $y < $imgY; ++$y) {
        $a = $x - 830;
        $a = $a / 400;
        $b = $y - 400;
        $b = $b / 320;
        $g = $a;
        $h = $b;
        for ($p = 0; $p < 255; ++$p) {
            $c = $a;
            $a = (($a * $a) - ($b * $b)) + $g;
            $b = (2 * $c * $b) + $h;
            if ($a > 4 || $b > 4) {
                break;
            }
        }
        imagesetpixel($img, $x, $y, imagecolorallocate($img, $p, 0, 0));
    }
}

header('Content-Type: image/jpeg');
imagejpeg($img);
imagedestroy($img); 
?>