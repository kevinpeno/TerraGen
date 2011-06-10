<?php
class DiamondSquareTerrainGenerator
{
	const DEFAULT_RANGE = 10;
	const DEFAULT_RANGE_DIVISOR = 2.5;

	private $heightMap;
	private $terrainMap;
	private $map = array();

	private $size;
	private $subSize;
	private $ratio;
	private $scale;
	private $stride;

	private $height;
	private $width;
	private $startPlane;
	private $rangeDivisor;
	private $minPeak;
	private $maxPeak;
	private $passes;

	private $minValue = 0;
	private $maxValue = 0;
	
	public function __construct( $size=8, $seed=37726, $scale=1.0, $ratio=0.7 )
	{
		if( !( $size > 1 && $size < 11 ) )
			throw new DiamondSquareTerrainGenerator_Exception('Size must be a whole number between 2 and 10');

		$this->size = 1 << $size;
		--$this->size;

		mt_srand( (int)$seed );

		$this->ratio = (float)$ratio <> 0 ? ( 1 << -(float)$ratio ) : 0.5;
		$this->scale = $this->ratio * ( (float)$scale <> 0 ? (float)$scale : 1. );

		# Seed em up
		$this->stride = floor( $this->size / 2 );

		$this->setPoint( 0, 0, 0 );
		$this->setPoint( $this->size, 0, 0 );
		$this->setPoint( $this->size, $this->size, 0 );
		$this->setPoint( 0, $this->size, 0 );
	}

	public function buildHeightMap()
	{
		while( $this->stride )
		{
			# Diamondize
			for( $x = $this->stride; $x <= $this->size; $x += $this->stride )
			{
				for( $y = $this->stride; $y <= $this->size; $y += $this->stride )
				{
					$z = $this->getRandom( -$this->scale, $this->scale ) + $this->avgSquareVals( $x, $y );
					$this->setPoint( $x, $y, $z );

					//$y += $this->stride;
				}
				//$x += $this->stride;
			}

			# Gotta make that sexy booty mine
			//$oddline = 0;
			for( $x = $this->stride; $x <= $this->size; $x += $this->stride )
			{
				//$oddline = ( $oddline === 0 );
				for( $y = $this->stride; $y <= $this->size; $y += $this->stride )
				{
					//if( $oddline && !$y ) $y += $this->stride;

					$z = $this->getRandom( -$this->scale, $this->scale ) + $this->avgDiamondVals( $x, $y );
					$this->setPoint( $x, $y, $z );

					# Wrap values
					if( $x === 0 )
						$this->setPoint( $this->size, $y, $z );

					if( $y === 0 )
						$this->setPoint( $x, $this->size, $z );

					//$y += $this->stride;
				}
			}

			/* reduce random number range. */
			$this->scale *= $this->ratio;
			$this->stride >>= 1;
		}

		return $this->map;
	}

	public function drawMap( $gradient=NULL )
	{
		$range = abs( $this->minValue ) + abs( $this->maxValue );

		if( $range == 0 )
			throw new DiamondSquareTerrainGenerator_Exception('Range is zero');

		# Prepare the height map image
		$this->heightMap = imagecreatetruecolor( $this->size + 1, $this->size + 1 );
		$grayColors = array();

		# prepare terrain image
		if( $gradient && is_readable( $gradient ) )
		{
			$gradientImg = imagecreatefromstring( file_get_contents( $gradient ) );
			$gradientColors = array();
			$this->terrainMap = imagecreatetruecolor( $this->size, $this->size );
			$buildTerrain = true;
		}
		else
			$buildTerrain = false;

		# Apply colors/texture
		for( $x=0; $x <= $this->size; $x++ )
		{
			for( $y=0; $y <= $this->size; $y++ )
			{
				$v = $this->getPoint( $x, $y );
				$color = $this->calculateColor( $v, $range );

				if( !isset( $grayColors[ $color ] ) )
					$grayColors[ $color ] = imagecolorallocate( $this->heightMap, $color, $color, $color );

				imagesetpixel( $this->heightMap, $x, $y, $grayColors[ $color ] );

				if( $buildTerrain )
				{
					if( !isset( $gradientColors[ $color ] ) )
						$gradientColors[ $color ] = imagecolorat( $gradientImg, $color, 0 );

					imagesetpixel( $this->terrainMap, $x, $y, $gradientColors[ $color ] );
				}
			}
		}
	}

	public function getHeightMap()
	{
		return $this->heightMap;
	}

	public function getTerrainMap()
	{
		return $this->terrainMap;
	}

	public function setPoint( $x, $y, $z )
	{
//		$z = min( $this->maxPeak, $z );
//		$z = max( $this->minPeak, $z );

		$this->minValue = min( $this->minValue, $z );
		$this->maxValue = max( $this->maxValue, $z );

		if( !isset( $this->map[ $x ] ) )
			$this->map[ $x ] = array();

		$this->map[ $x ][ $y ] = $z;
	}

	public function getPoint( $x, $y )
	{
		if( !isset( $this->map[ $x ][ $y ] ) )
			$this->map[ $x ][ $y ] = 0;

		return $this->map[ $x ][ $y ];
	}

	/*
	 * avgDiamondVals - Given the i,j location as the center of a diamond,
	 * average the data values at the four corners of the diamond and
	 * return it. "Stride" represents the distance from the diamond center
	 * to a diamond corner.
	 *
	 * Called by fill2DFractArray.
	 */
	private function avgDiamondVals( $x, $y )
	{
		/* In this diagram, our input stride is 1, the i,j location is
		   indicated by "X", and the four value we want to average are
		   "*"s:
			   .   *   .
	
			   *   X   *
	
			   .   *   .
		*/
		$points = array();
		$points['n'] = array( ( $x - $this->stride ), $y );
		$points['s'] = array( ( $x + $this->stride ), $y );
		$points['w'] = array( $x, ( $y - $this->stride ) );
		$points['e'] = array( $x, ( $y + $this->stride ) );
		$divisor = 4;

		/* In order to support tiled surfaces which meet seamless at the
		   edges (that is, they "wrap"), We need to be careful how we
		   calculate averages when the i,j diamond center lies on an edge
		   of the array. The first four 'if' clauses handle these
		   cases. The final 'else' clause handles the general case (in
		   which i,j is not on an edge).
		*/

		if( $x === 0 )
			$points['n'][0] = $this->size - $this->stride;
		elseif( $x === ( $this->size - 1 ) )
			$points['s'][0] = $this->stride;
		
		if( $y === 0 )
			$points['w'][1] = $this->size - $this->stride;
		elseif( $y === ( $this->size - 1 ) )
			$points['e'][1] = $this->stride;

		# Make Average
		return
			$this->getPoint( $points['n'][0], $points['n'][1] ) +
			$this->getPoint( $points['s'][0], $points['s'][1] ) +
			$this->getPoint( $points['w'][0], $points['w'][1] ) +
			$this->getPoint( $points['e'][0], $points['e'][1] )
			/ 4
		;
	}
	
	/*
	 * avgDiamondVals - Given the i,j location as the center of a diamond,
	 * average the data values at the four corners of the diamond and
	 * return it. "Stride" represents the distance from the diamond center
	 * to a diamond corner.
	 *
	 * Called by fill2DFractArray.
	 */
	private function avgSquareVals( $x, $y )
	{
		/* In this diagram, our input stride is 1, the i,j location is
		   indicated by "*", and the four value we want to average are
		   "X"s:
			   X   .   X
	
			   .   *   .
	
			   X   .   X
		   */
		return
			$this->getPoint( ( $x - $this->stride ), ( $y - $this->stride ) ) +		# NW
			$this->getPoint( ( $x - $this->stride ), ( $y + $this->stride ) ) +		# NE
			$this->getPoint( ( $x + $this->stride ), ( $y - $this->stride ) ) +		# SW
			$this->getPoint( ( $x + $this->stride ), ( $y + $this->stride ) )		# SE
			/ 4
		;
	}

	private function calculateColor( $z, $range )
	{
		return round( 0xFF * ( ( $z + abs( $this->minValue ) ) / $range ) );
	}

	private function getRandom( $min, $max )
	{
		static $randmax;

		if( $randmax === NULL )
			$randmax = mt_getrandmax();

		return mt_rand( -$randmax, $randmax ) / $randmax * ( abs( $min ) * 1000 + abs( $max ) * 1000 ) / 1000;
	}

	public function __destruct()
	{
		if( $this->heightMap )
			imagedestroy( $this->heightMap );
		if( $this->terrainMap )
			imagedestroy( $this->heightMap );
	}
}
class DiamondSquareTerrainGenerator_Exception extends Exception {}
?>