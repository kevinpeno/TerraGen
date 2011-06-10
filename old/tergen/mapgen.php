<?php
class DiamondSquareTerrainGenerator
{
	const DEFAULT_RANGE = 1;
	const DEFAULT_RANGE_DIVISOR = 2;

	private $heightMap;
	private $terrainMap;
	private $map = array();

	private $height;
	private $width;
	private $startPlane;
	private $rangeDivisor;
	private $minPeak;
	private $maxPeak;
	private $passes;

	private $minValue = 0;
	private $maxValue = 0;
	
	public function __construct( $height, $width, $initialRange=NULL, $seaLevel=NULL, $rangeDivisor=NULL, $minPeak=NULL, $maxPeak=NULL, $passes=0 )
	{
		# Prep functional info
		if( !( $height > 0 && $width > 0 ) )
			throw new DiamondSquareTerrainGenerator_Exception('Invalid height or width defined');

		$this->height = (int)$height;
		$this->width = (int)$width;

		$this->initialRange = ( $initialRange > 0 ) ? $initialRange : self::DEFAULT_RANGE;
		$this->seaLevel = ( $seaLevel <= 1 && $seaLevel >= 0 ) ? (float)$seaLevel : 0;
		$this->seaLevel = -$this->seaLevel * ( 1 << $this->initialRange ) + $this->initialRange;

		$this->rangeDivisor = ( $rangeDivisor != 0 ) ? $rangeDivisor : self::DEFAULT_RANGE_DIVISOR;
		$this->passes = ( (int)$passes > 0 && (int)$passes < $this->width ) ? (int)$passes : $this->width - 1;
		$this->minPeak = !is_numeric( $minPeak ) ? $minPeak : NULL;
		$this->maxPeak = ( !is_numeric( $maxPeak ) && $maxPeak > $this->minPeak ) ? $maxPeak : NULL;

		$this->setPoint( 0, 0, 0 );
		$this->setPoint( ( $this->width - 1 ), 0, 0 );
		$this->setPoint( 0, ( $this->height - 1 ), 0 );
		$this->setPoint( ( $this->width - 1 ), ( $this->height - 1 ), 0 );
	}

	public function buildHeightMap()
	{
		$range = $this->initialRange;
		$pass = $this->passes;

		while( $pass > 1 )
		{
			//diamond
			for( $x=0; $x < $this->width - 1; $x += $pass )
			{
				for( $y=0; $y < $this->height - 1; $y += $pass )
				{
					$sx = $x + ( $pass >> 1 );
					$sy = $y + ( $pass >> 1 );
					$points = array(
						array( $x, $y ),
						array( ( $x + $pass ), $y ),
						array( $x, ( $y + $pass ) ),
						array( ( $x + $pass ), ( $y + $pass ) )
					);

					$this->alterMidpoint( $sx, $sy, $points, $range );
				}
			}

			//square
			for( $x=0; $x < $this->width - 1; $x += $pass )
			{
				for( $y=0; $y < $this->height - 1; $y += $pass )
				{
					$halfstep = $pass >> 1;
					$x1 = $x + $halfstep;
					$y1 = $y;
					$x2 = $x;
					$y2 = $y + $halfstep;

					$points1 = array(
						array( ( $x1 - $halfstep ), $y1 ),
						array( $x1, $y1 - $halfstep ),
						array( ( $x1 + $halfstep ), $y1 ),
						array( $x1, $y1 + $halfstep )
					);

					$points2 = array(
						array( ( $x2 - $halfstep ), $y2 ),
						array( $x2, $y2 - $halfstep ),
						array( ( $x2 + $halfstep ), $y2 ),
						array( $x2, $y2 + $halfstep )
					);
					$this->alterMidpoint( $x1, $y1, $points1, $range );
					$this->alterMidpoint( $x2, $y2, $points2, $range );
				}
			}

			$range /= $this->rangeDivisor;
			$pass >>= 1;
		}

		# Get SeaLevel and Scale
		$this->absoluteRange = abs( $this->maxValue ) + abs( $this->minValue );

		# Retirn map
		return $this->map;
	}

	public function drawMap( $gradient=NULL )
	{
		# Prepare the height map image
		$this->heightMap = imagecreatetruecolor( $this->height - 1, $this->width - 1 );
		$grayColors = array();

		# prepare terrain image
		if( $gradient && is_readable( $gradient ) )
		{
			$gradientImg = imagecreatefromstring( file_get_contents( $gradient ) );
			$gradientColors = array();
			$this->terrainMap = imagecreatetruecolor( $this->height - 1, $this->width - 1 );
			$buildTerrain = true;
		}
		else
			$buildTerrain = false;

		# Apply colors/texture
		for( $x=0; $x < $this->width - 1; $x++ )
		{
			for( $y=0; $y < $this->height - 1; $y++ )
			{
				$v = $this->getPoint( $x, $y );
				$color = $this->calculateColor( $v );

				if( !isset( $grayColors[ $color ] ) )
					$grayColors[ $color ] = imagecolorallocate( $this->heightMap, $color, $color, $color );

				imagesetpixel( $this->heightMap, $x, $y, $grayColors[ $color ] );

				if( $buildTerrain )
				{
					if( !isset( $gradientColors[ $color ] ) )
					{
						$gradientColors[ $color ] = imagecolorat( $gradientImg, $color, 0 );
					}
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

	private function alterMidpoint( $x, $y, Array $points, $range )
	{
		$c = 0;
		for( $i = 0; $i < 4; $i++ )
		{
			if( $points[ $i ][0] < 0 )
			{
				$points[ $i ][0] += $this->width - 1;
			}
			else if( $points[ $i ][0] > $this->width )
			{
				$points[ $i ][0] -= $this->width - 1;
			}
			else if( $points[ $i ][1] < 0 )
			{
				$points[ $i ][1] += $this->height - 1;
			}
			else if( $points[ $i ][1] > $this->height )
			{
				$points[ $i ][1] -= $this->height - 1;
			}

			$c += $this->getPoint( $points[ $i ][0], $points[ $i ][1] );
		}

		$c = $c / 4 + $this->getRandomSeed() * $range;

		if( $this->minPeak )
			$c = max( $this->minPeak, $c );
		if( $this->maxPeak )
			$c = min( $this->maxPeak, $c );

		$this->maxValue = max( $this->maxValue, $c );
		$this->minValue = min( $this->minValue, $c );

		$this->setPoint( $x, $y, $c );

		if( $x === 0 )
		{
			$this->setPoint( ( $this->width - 1 ), $y, $c );
		}
		elseif( $x === ( $this->width - 1 ) )
		{
			$this->setPoint( 0, $y, $c );
		}
		elseif( $y === 0 )
		{
			$this->setPoint( $x, ( $this->height - 1 ), $c );
		}
		elseif( $y === ( $this->height - 1 ) )
		{
			$this->setPoint( $x, 0, $c );
		}
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

	private function calculateColor( $z )
	{
		$r = round( 0xFF * ( ( $z + abs( $this->minValue ) + $this->seaLevel ) / $this->absoluteRange ) );
		$r = max( 0x00, $r );
		$r = min( 0xFF, $r );
		return $r;
	}

	private function getRandomSeed( $seed=NULL )
	{
		static $randmax;

		if( $randmax === NULL )
			$randmax = mt_getrandmax();

		if( $seed )
			mt_srand( $seed );

		return mt_rand( -$randmax, $randmax ) / $randmax;
	}

	public function __destruct()
	{
		if( $this->heightMap )
			imagedestroy( $this->heightMap );
		if( $this->terrainMap )
			imagedestroy( $this->terrainMap );
	}
}
class DiamondSquareTerrainGenerator_Exception extends Exception {}
?>