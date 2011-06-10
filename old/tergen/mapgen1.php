<?php
class TerainGenerator
{
	public function __construct()
	{
		parent::__construct( $height, $width, $startPlane, $initialRange, $divisor, $passes );

		# Prepare the image
		$this->img = imagecreatetruecolor( $h, $h );
		imagefill( $this->img, 0, 0, imagecolorallocate( $this->img, 255, 255, 255 ) );
	}

	public function drawMap()
	{
		for( $x=0; $x < $this->width; $x++ )
		{
			for( $y=0; $y < $this->height; $y++ )
			{
				$color = $this->calculateColor( $this->map[ $x ][ $y ] );
				imagesetpixel( $this->img, $x, $y, imagecolorallocate( $this->img, $color, $color, $color ) );
			}
		}

		return $this->img;
	}
}
?>