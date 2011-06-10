<?php
/**
 *	Diamond Square Generator
 *	Randomizes a height map using the Diamond Square method of terrain generation.
 *
 *	@version	1.0
 *	@uses		Utilities\Autoload
 *	@uses		Utilities\Random\MersenneTwister
 *	@uses		Terra\HeightMapGenerator
 *
 *	@notes		- Optimized for tab space = 4
 *				- Drop prefixing for namespacing PHP 5.3
 */
require_once('Utilities/AutoLoad.php');

class Terra_HeightMapGenerator_DiamondSquare implements Terra_HeightMapGenerator
{
	private $range;
	private $doubleRange;
	private $currentRange;
	private $levelDivisor;

	const DEFAULT_RANGE = 1;
	const DEFAULT_LEVEL_DIVISOR = 2;

	private $map;

	/**
	 *	Construct
	 *
	 *	@param		Int		Determines smoothing factor
	 *	@return		Void
	 *	@public
	 */
	public function __construct( $levelDivisor=NULL, $range=NULL )
	{
		$this->levelDivisor = $levelDivisor <> 0 ? (int)$levelDivisor : self::DEFAULT_LEVEL_DIVISOR;
		$this->range = $range <> 0 ? (int)$range : self::DEFAULT_RANGE;
		$this->doubleRange = $this->range * 2;
		$this->currentRange = $this->range;
	}

	/**
	 *	Generate
	 *	Creates a random image with height values stored in Grayscale format
	 *
	 *	@param		HeightMap		Map to perform the randomizing on
	 *	@return		HeightMap		Rondomized map
	 *	@public
	 */
	public function generate( Terra_HeightMap $map )
	{
		if( $map->getSize() < 3 )
			throw new Terra_HeightMap_TooSmall_Exception();

		$this->map = $map;

		# Seed the randomizer
		Utilities_Random_MersenneTwister::setSeed( $map->getSeed() );

		$pass = $loop = $map->getSize();

		# Seed 4 corners
		$this->setPoint( 0, 0, 0.0 );
		$this->setPoint( 0, $loop, 0.0 );
		$this->setPoint( $loop, $loop, 0.0 );
		$this->setPoint( $loop, 0, 0.0 );

		while( $pass > 1 )
		{
			$x = 0;
			while( $x < $loop )
			{
				$y = 0;
				while( $y < $loop )
				{
					$half = ( $pass >> 1 );

					# Diamond
					$points = array( 
						$this->getPoint( $x, $y ),							# NW
						$this->getPoint( $x, ( $y + $pass ) ),				# NE
						$this->getPoint( ( $x + $pass ), ( $y + $pass ) ),	# SE
						$this->getPoint( ( $x + $pass ), $y ),				# SW
					);
					$this->alterMidpoint( ( $x + $half ), ( $y + $half ), $points );

					# Squares - Top
					$sx = $x + $half;
					$sy = $y;
					$points = array( 
						$this->getPoint( ( $sx - $half ), $sy ), # N
						$this->getPoint( $sx, ( $sy + $half ) ), # E
						$this->getPoint( ( $sx + $half ), $sy ), # S
						$this->getPoint( $sx, ( $sy - $half ) ), # W
					);
					$this->alterMidpoint( $sx, $sy, $points );

					$sx = $x;
					$sy = $y + $half;
					$points = array( 
						$this->getPoint( ( $sx - $half ), $sy ), # N
						$this->getPoint( $sx, ( $sy + $half ) ), # E
						$this->getPoint( ( $sx + $half ), $sy ), # S
						$this->getPoint( $sx, ( $sy - $half ) ), # W
					);
					$this->alterMidpoint( $sx, $sy, $points );

					$y += $pass;
				}
				$x += $pass;
			}

			$this->currentRange /= $this->levelDivisor;
			$pass >>= 1;
		}

		return $map;
	}

	/**
	 *	Alter Midpoint
	 *	Averages supplied points, applies random Float between -1.0 and 1.0,
	 *	then applys it to the point on the map at position specified.
	 *
	 *	@param		Terra_HeightMap		Map
	 *	@param		Int				X coord
	 *	@param		Int				Y coord
	 *	@param		Array			Averaging points
	 *	@return		Float			Random value between -1.0 and 1.0
	 *	@public
	 */
	private function alterMidpoint( $x, $y, Array $points )
	{
		$avg = 0.0;
		$i = 0;
		while( isset( $point[ $i ] ) )
		{
			$avg += $points[ $i ];
			++$i;
		}

		if( $i > 0 )
			$avg /= $i;

		$avg += Utilities_Random_MersenneTwister::NextFloat() * $this->currentRange;

		return $this->setPoint( $x, $y, $avg );
	}

	/**
	 *	Get Point
	 *	Performs calculations against value returned from HeightMap
	 *	to translate that value to a Float within $range
	 *
	 *	@param		Int				X coord
	 *	@param		Int				Y coord
	 *	@return		Float			Z Value
	 *	@public
	 */
	public function getPoint( $x, $y )
	{
		try
		{
			return ( $this->map->getPoint( $x, $y ) * $this->doubleRange - $this->range );
		}
		catch( Terra_HeightMap_OutOfRange_Exception $e )
		{
			return 0.0;
		}
	}

	/**
	 *	Generate
	 *	Creates a random image with height values stored in Grayscale format
	 *
	 *	@param		Int				X coord
	 *	@param		Int				Y coord
	 *	@param		Float			Z Value
	 *	@return		Float			Z Value
	 *	@public
	 */
	public function setPoint( $x, $y, $z )
	{
		$this->map->setPoint(
			$x,
			$y,
			( ( $z + $this->range ) / $this->doubleRange )
		);
		return $z;
	}
}
?>