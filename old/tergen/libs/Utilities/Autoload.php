<?php
/**
	Autoload
	http://us2.php.net/manual/en/language.oop5.autoload.php

 	@param 		string	Requested unloaded classname
	@notes		- Optimized for tab space = 4
				- Replace prefixing, "_", for namespacing, "\", PHP 5.3
**/
class Utilities_Autoload
{
	public static function autoload( $classname )
	{
		try
		{
			self::Load( $classname );
			return $classname;
		}
		catch( Utilities_Autoload_Exception $e )
		{
			return false;
		}
	}

	public static function Load( $classname )
	{
		$file = str_replace( '_', DIRECTORY_SEPARATOR, $classname ).'.php';
		
		# http://bugs.php.net/bug.php?id=48173
		//if( !file_exists( $file ) || include( $file ) === FALSE )
//			throw new Utilities_Autoload_FileNotFound_Exception();
		require( $file );

		if( !class_exists( $classname ) )
			throw new Utilities_Autoload_ClassNotFound_Exception();
	}
}
spl_autoload_register( array( 'Utilities_Autoload', 'autoload' ) );

class Utilities_Autoload_Exception extends Exception { }
class Utilities_Autoload_FileNotFound_Exception extends Utilities_Autoload_Exception { }
class Utilities_Autoload_ClassNotFound_Exception extends Utilities_Autoload_Exception { }
?>