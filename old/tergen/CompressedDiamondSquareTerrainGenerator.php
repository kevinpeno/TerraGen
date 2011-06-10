<?php
class CompressedDiamondSquareTerrainGenerator
{
	const RANGE = 1;
	const LEVEL_DIVISOR = 2;

	private $heightMap;
	private $terrainMap;

	private $size;
	private $seaLevel;
	private $seed;
	private $levelDivisor;

	public function __construct( $size, $seaLevel=0.5, $seed=0, $levelDivisor=2.0 )
	{
		# Must be >= 3 or we wont be able to do anything...
		if( !(int)$size > 3 )
			throw new DiamondSquareTerrainGenerator_Exception('Size is too small to use.');

		# Size of the map. Will be applied as height and width thus, number of pixels is $size ^ 2
		$this->size = (int)$size;

		# Sea level is absolute range * percentile, or absolute zero
		$this->seaLevel = $seaLevel >= 0 ? ( (float)$seaLevel * ( self::RANGE * 2 ) ) - self::RANGE : 0;

		# Seed the randomizer
		$seed = (int)$seed > 0 ? (int)$seed : $this->getRandom();
		$this->getRandom( $seed );

		# Determine the roughness of the generated terrain.
		$this->levelDivisor = ( (float)$levelDivisor != 0 ) ? (float)$levelDivisor : self::LEVEL_DIVISOR;

		# Prepare the height map image
		$this->heightMap = imagecreatetruecolor( $this->size, $this->size );

		$this->setPoint( 0, 0, 0 );
		$this->setPoint( ( $this->size - 1 ), 0, 0 );
		$this->setPoint( 0, ( $this->size - 1 ), 0 );
		$this->setPoint( ( $this->size - 1 ), ( $this->size - 1 ), 0 );
	}

	public function buildHeightMap()
	{
		$range = self::RANGE;
		$pass = $this->size;

		while( $pass > 1 )
		{
			for( $x=0; $x < $this->size; $x += $pass )
			{
				for( $y=0; $y < $this->size; $y += $pass )
				{
					//diamond
					$sx = $x + ( $pass >> 1 );
					$sy = $y + ( $pass >> 1 );
					$points = array(
						array( $x, $y ),
						array( ( $x + $pass ), $y ),
						array( $x, ( $y + $pass ) ),
						array( ( $x + $pass ), ( $y + $pass ) )
					);

					$this->alterMidpoint( $sx, $sy, $points, $range );

					//square
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

			$range /= $this->levelDivisor;
			$pass >>= 1;
		}
	}

	# Default position is Top + Left + double the possible height.
	# Strength of the light at the source. Descrease to produce more soft light.
	private function drawLightMap( $sunx=0, $suny=0, $sunz=2, $intensity=1.0, $color=NULL )
	{
		# Illumination value = white @ 100%
		$ill = 255 * (float)$intensity;
		if( $ill <= 0 )
			throw new CompressedDiamondSquareTerrainGenerator_Exception('Bad intensity value');

		$this->lightMap = imagecreatetruecolor( $this->size, $this->size );

		# Figure ray direction to determine travel
		if( $sunx < $suny )
		{
			$a = $suny;
			$b = $sunx;
			$inca = -1;
		}
		else
		{
			$a = $sunx;
			$b = $suny;
			$inca = 1;
		}

		$shadata = array();
		for( ; $x < $this->size; $x++ )
		{
			for( ; $y < $this->size; $y++ )
			{
				if( !isset( $shadata[ $x.'-'.$y ] ) )
					$shadata[ $x.'-'.$y ] = 0;

				
			}
		}
	}

	public function drawTerrainMap( $gradient, $shadow=false )
	{
		# prepare terrain image
		if( $gradient && is_readable( $gradient ) )
		{
			$gradientImg = imagecreatefromstring( file_get_contents( $gradient ) );
			$gradientColors = array();
			$this->terrainMap = imagecreatetruecolor( $this->size, $this->size );
			$buildTerrain = true;
		}
		else
			throw new CompressedDiamondSquareTerrainGenerator_Exception('Terrain gradient passed is not valid.');

		# Apply colors/texture
		for( $x=0; $x < $this->size; $x++ )
		{
			for( $y=0; $y < $this->size; $y++ )
			{
				$color = $this->getPoint( $x, $y, true, true );

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

	public function getSeed()
	{
		return $this->seed;
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
		static $grayColors = array();

		$color = $this->calculateColor( $z );

		if( !isset( $grayColors[ $color ] ) )
			$grayColors[ $color ] = imagecolorallocate( $this->heightMap, $color, $color, $color );

		imagesetpixel( $this->heightMap, $x, $y, $grayColors[ $color ] );
	}

	public function getPoint( $x, $y, $asColor=false, $shift=false )
	{
		static $seaLevelShift = NULL, $seaLevelShiftColor = NULL;

		# Ready sealevel calculations
		if( $seaLevelShift === NULL )
		{
			$seaLevelShift = -( $this->seaLevel * ( self::RANGE * 2 ) - self::RANGE );
			$seaLevelShiftColor = round( 127 - ( $this->seaLevel * 0xFF ) );
		}

		$rgb = @imagecolorat( $this->heightMap, $x, $y );
		if( $rgb )
		{
			$rgb = imagecolorsforindex( $this->heightMap, $rgb );
			$color = $rgb['red'];
		}
		else
			$color = 127;

		if( $asColor )
		{
			if( $shift )
			{
				$color = $color + $seaLevelShiftColor;
				$color = max( 0x00, $color );
				$color = min( 0xFF, $color );
			}

			return $color;
		}
		else
		{
			$eh = $shift ? ( self::RANGE * 2 + $seaLevelShift - self::RANGE ) : ( self::RANGE * 2 );
			return ( $color / 0xFF * $eh - self::RANGE );
		}
	}

	private function alterMidpoint( $x, $y, Array $points, $range )
	{
		$c = 0;
		$avg = 4;
		for( $i = 0; $i < 4; $i++ )
		{
			# Drop the average down if we're off the map
			if(
			   ( $points[ $i ][0] < 0 )
			   || ( $points[ $i ][0] > $this->size )
			   || ( $points[ $i ][1] < 0 )
			   || ( $points[ $i ][1] > $this->size )
			){
				$avg--;
				continue;
			}
			else
			{
				$c += $this->getPoint( $points[ $i ][0], $points[ $i ][1] );
			}
		}

		# Perform average if we can
		if( $avg > 0 )
			$c = $c / $avg;

		# Add random
		$c += $this->getRandom() * $range;

		# Set this point
		$this->setPoint( $x, $y, $c );
	}

	private function calculateColor( $z )
	{
		$r = round( ( $z + self::RANGE ) / ( self::RANGE * 2 ) * 0xFF );
		$r = max( 0x00, $r );
		$r = min( 0xFF, $r );
		return $r;
	}

	private function getRandom( $seed=NULL )
	{
		static $randmax = NULL;

		if( $randmax === NULL )
			$randmax = mt_getrandmax();

		if( (int)$seed )
		{
			mt_srand( $seed );
			return true;
		}
		else
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
class CompressedDiamondSquareTerrainGenerator_Exception extends Exception {}
?>