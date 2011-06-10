<?php
/**
	Require dependencies
**/
require_once('Utilities/AutoLoad.php');

/**
	Mersenne Twister Random
	Adds functionality to the mt_rand function group. Just like mt_rand, re-seeding will reset the randomizer.

	@notes		- Optimized for tab space = 4
				- PHP 5.3
				-- Drop prefixing for namespacing
**/
class Utilities_Random_MersenneTwister implements Utilities_Random_Interface
{
	#region Seed
	/**
		Seed
		Used to seed the randomizer

		@private
	**/
	private static $seed;

	/**
		Get seed
		
		@return		Int
		@public
	**/
	public static function getSeed()
	{
		return self::$seed;
	}

	/**
		Set Size
		
		@param		Int		seed value
		@return		Void
		@public
	**/
	public static function setSeed( $s )
	{
		if( (int)$s )
		{
			self::$seed = (int)$s;
		}
		else
		{
			# Generate random seed
			self::$seed = self::Next();
			mt_srand( self::$seed );
		}
	}

	/**
		sRandom
		Initialize w/ seed
		
		@param		Int		seed value
		@return		Int		seed value
		@public
	**/
	public function sRandom( $seed=NULL )
	{
		self::setSeed( $seed );
		return self::getSeed();
	}
	#endregion

	#region RandomInt
	/**
		Next
		Return a random Integer within range.
		Accepts parameters in 3 formats

		#load1
			@params		void
		#load2
			@param		Int		max value
		#load3
			@param		Int		min value
			@param		Int		max value
		#endload
		@return			Int
		@public
	**/
	public static function NextInt( $one=NULL, $two=NULL )
	{
		if( (int)$two )
			return mt_rand( $one, $two );
		elseif( (int)$one )
			return mt_rand( 0, $one );
		else
			return mt_rand();
	}
	#endregion

	#region RandomByte
	/**
		Next Bytes
		Returns an array of intergers between 0 and 255.
		
		@param		Int		number of byte-sized values to return
		@return		Byte[]
		@public
	**/
	public static function NextBytes( $size )
	{
		$bytes = array();

		while( $size > 0 )
		{
			$bytes[] = self::NextInt(255);
			--$size;
		}

		return $bytes;
	}
	#endregion

	#region RandomFloat
	/**
		Next Float
		Returns a pseudo-random number between negative one, or zero, and positive one.

		@param		Bool	True, returned random will be positive only.
		@return		Float
		@public
	**/
	public static function NextFloat( $pos=false )
	{
		static $randmax;
		if( !$randmax )
			$randmax = mt_getrandmax();

		$min = $pos ? 0 : -$randmax;

		return ( mt_rand( $min, $randmax ) / $randmax );
	}
	#endregion

	#region RandomBool
	/**
		Next Bool
		Returns a pseudo-random Boolean

		@return		Bool
		@public
	**/
	public static function NextBool()
	{
		return (bool)round( self::NextFloat(), 0 );
	}
	#endregion
}
?>