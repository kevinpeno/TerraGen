<?php
/**
	Random
	Provides an interface for expanded randomizer access

	@notes		- Optimized for tab space = 4
				- PHP 5.3
				-- Drop prefixing for namespacing
**/
interface Utilities_Random_Interface
{
	#region Seed
	/**
		Get seed
		
		@return		Int
		@public
	**/
	public static function getSeed();

	/**
		Set Size
		
		@param		Int		seed value
		@return		Void
		@public
	**/
	public static function setSeed( $s );

	/**
		sRandom
		Initialize seed, return seed
		
		@param		Int		seed value
		@return		Int		seed value
		@public
	**/
	public function sRandom( $seed=NULL );
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
	public static function NextInt( $one=NULL, $two=NULL );
	#endregion

	#region RandomByte
	/**
		Next Bytes
		Returns an array of intergers between 0 and 255.
		
		@param		Int		number of byte-sized values to return
		@return		Byte[]
		@public
	**/
	public static function NextBytes( $size );
	#endregion

	#region RandomFloat
	/**
		Next Float
		Returns a pseudo-random number between negative one, or zero, and positive one.

		@param		Bool	True, returned random will be positive only.
		@return		Float
		@public
	**/
	public static function NextFloat( $pos=false );
	#endregion

	#region RandomBool
	/**
		Next Bool
		Returns a pseudo-random Boolean

		@return		Bool
		@public
	**/
	public static function NextBool();
	#endregion
}
?>