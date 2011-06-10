<?php
/**
 *	Height Map Generator
 *	Creates a random set of height values and applies it against the
 *	supplied HeightMap object.
 *
 *	@notes		- Optimized for tab space = 4
 *				- Drop prefixing for namespacing PHP 5.3
 */
interface Terra_HeightMapGenerator
{
	/**
	 *	Generate
	 *	Creates a random image with height values stored in Grayscale format
	 *
	 *	@param		HeightMap		Map to perform the randomizing on
	 *	@return		HeightMap		Rondomized map
	 *	@public
	 */
	public function generate( Terra_HeightMap $map );
}
?>