using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace Terra
{
	interface iHeightMapGenerator
	{
		HeightMap generate( HeightMap map );
	}
}
