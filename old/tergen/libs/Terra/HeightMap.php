<?php
/**
 *	HeightMap
 *	Stores an image map of height values in Grayscale format
 *
 *	@version	1.0
 *	@uses		Utilities\Autoload
 *	@uses		Reflection
 *
 *	@notes		- Optimized for tab space = 4
 *			- Drop prefixing for namespacing PHP 5.3
 */
require_once('Utilities/AutoLoad.php');
class Terra_HeightMap
{
	#region Size
	/**
	 *	Size
	 *	Determines the size of the HeightMap
	 *
	 *	@private
	 */
	private $size;

	/**
	 *	Get size
	 *	
	 *	@return		Int
	 *	@public
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 *	Set Size
	 *	
	 *	@param		Int		X coord
	 *	@return		Void
	 *	@throws		Terra_HeightMap_TooSmall_Exception
	 *	@public
	 */
	public function setSize( $s )
	{
		if( !(int)$s )
			throw new Terra_HeightMap_TooSmall_Exception();
		else
			$this->size = (int)$s;
	}
	#endregion

	#region Seed
	/**
	 *	Seed
	 *	Used to seed the randomizer
	 *
	 *	@private
	 */
	private $seed;

	/**
	 *	Get seed
	 *	
	 *	@return		Int
	 *	@public
	 */
	public function getSeed()
	{
		return $this->seed;
	}

	/**
	 *	Set Size
	 *	
	 *	@param		Int		X coord
	 *	@return		Void
	 *	@public
	 */
	public function setSeed( $s )
	{
		if( (int)$s )
			$this->seed = (int)$s;
		else
		{
			# Generate random seed
			$this->seed = Utilities_Random_MersenneTwister::NextInt();
		}
	}
	#endregion

	#region MapAccess
	/**
	 *	Map image resource reference
	 */
	private $map;

	/**
	 *	Get Point
	 *	Get's the value of a point on the map
	 *	
	 *	@param		Int		X coord
	 *	@param		Int		Y coord
	 *	@return		Float	Z value in percentile
	 *	@throws		Terra_HeightMap_OutOfRange_Exception	Coords match a position that is outside the map range
	 *	@public
	 */
	public function getPoint( $x, $y )
	{
		if( !$this->validatePoint( $x, $y ) )
			throw new Terra_HeightMap_OutOfRange_Exception();

		$rgb = @imagecolorat( $this->map, $x, $y );
		if( !$rgb )
			throw new Terra_HeightMap_OutOfRange_Exception();

		$rgb = imagecolorsforindex( $this->map, $rgb );
		$color = $rgb['red'];

		return ( $color / 0xFF );
	}

	/**
	 *	Set Point
	 *	Set's the value of a point on the map
	 *	
	 *	@param		Int		X coord
	 *	@param		Int		Y coord
	 *	@param		Float	Z value in percentile
	 *	@return		Float	Z value in percentile
	 *	@throws		Terra_HeightMap_OutOfRange_Exception	Coords match a position that is outside the map range
	 *	@public
	 */
	public function setPoint( $x, $y, $z )
	{
		if( !$this->validatePoint( $x, $y ) )
			throw new Terra_HeightMap_OutOfRange_Exception();

		static $grayColors = array();

		$z0xFF = (int)( 0xFF * $z );

		if( !isset( $grayColors[ $z0xFF ] ) )
			$grayColors[ $z0xFF ] = imagecolorallocate( $this->map, $z0xFF, $z0xFF, $z0xFF );

		imagesetpixel( $this->map, $x, $y, $grayColors[ $z0xFF ] );

//var_dump( $x, $y, $z, $z0xFF, $this->getPoint( $x, $y ) );
//print '<br/>';

		return $z;
	}

	/**
	 *	Validate Point
	 *	Checks that the X/Y are within the confines of the map
	 *	
	 *	@param		Int		X coord
	 *	@param		Int		Y coord
	 *	@return		Bool
	 *	@public
	 */
	public function validatePoint( $x, $y )
	{
		return (
			$x >= 0
			&& $y >= 0
			&& $x <= $this->size
			&& $y <= $this->size
		);
	}

	/**
	 *	Get Map
	 *	Returns the map in PNG format
	 *
	 *	@param		Bool			If set will output directly to the output buffer
	 *	@return		String\Bool		RAW PNG data or BOOL response from imagepng
	 *	@public
	 */
	public function getImage( $print=false )
	{
		if( !$print )
		{
			# Start new OB
			ob_start();
			imagepng( $this->map );
			return ob_get_clean();
		}
		else
			return imagepng( $this->map );
	}
	#endregion

	#region MapGenerate
	const DEFAULT_GENERATOR = 'DiamondSquare';

	/**
	 *	Generate
	 *	Returns a new Generator as specified by the input parameters
	 *	
	 *	@param		String		Optional: Type of random generator to use; If not set,
	 *							defaults to Generator designated by const DEFAULT_GENERATOR.
	 *							Must be specified to use options overload.
	 *	@params		Mixed		Overloaded, Optional: Any additional parameters to pass to the
	 *							generator class
	 *	@returns	Terra_HeightMapGenerator
	 *	@throws		Terra_HeightMap_InvalidGenerator_Exception
	 *	@public
	 */
	public static function getGenerator()
	{
		$opts = func_get_args();

		if( isset( $opts[0] ) )
		{
			$type = array_shift( $opts );
			$goOpts = true;
		}
		else
		{
			$type = self::DEFAULT_GENERATOR;
			$goOpts = false;
		}
		
		# Verify iGenerator is valid
		if( $type !== self::DEFAULT_GENERATOR && !preg_match( '/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $type ) )
			throw new Terra_HeightMap_InvalidGenerator_Exception();

		try
		{
			$type = 'Terra_HeightMapGenerator_'.$type;

			$params = $goOpts ? $opts : NULL;

			$generator = call_user_func_array( array( new ReflectionClass( $type ), 'newInstance' ), $params );
		}
		catch( Utilities_Autoload_Exception $e )
		{
			throw new Terra_HeightMap_InvalidGenerator_Exception();
		}
		
		if( !$generator instanceof Terra_HeightMapGenerator )
			throw new Terra_HeightMap_InvalidGenerator_Exception();

		return $generator;
	}

	/**
	 *	Terra_HeightMap
	 *	
	 *	@param		Int		Size
	 *	@param		Int		Seed for the randomizer
	 *	@private
	 */
	public function __construct( $size, $seed=NULL )
	{
		$this->setSize( $size );
		$this->setSeed( $seed );
		$this->map = imagecreatetruecolor( $this->size, $this->size );
		//imagefill( $this->map, 0, 0, imagecolorallocate( $this->map, 127, 127, 127 ) );
	}
}

/**
	 *	Exceptions
**/
class Terra_HeightMap_Exception extends Exception {}
class Terra_HeightMap_TooSmall_Exception extends Terra_HeightMap_Exception {}
class Terra_HeightMap_OutOfRange_Exception extends Terra_HeightMap_Exception {}
class Terra_HeightMap_InvalidGenerator_Exception extends Terra_HeightMap_Exception {}
?>