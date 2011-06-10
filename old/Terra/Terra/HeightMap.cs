using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Linq;
using System.Reflection;
using System.Text;

namespace Terra
{
	public class HeightMap
	{
		public Int32 size { get; set; }
		public Int32 seed { get; set; }

		#region MapAccess
		private Double[][] map;

		public Double getPoint( Int32 x, Int32 y )
		{
			return (Double)this.map.GetValue( x, y );
		}

		public Double setPoint( Int32 x, Int32 y, Double z )
		{
			this.map.SetValue( z, x, y );
			return this.getPoint( x, y );
		}
		#endregion

		private HeightMap( Int32 size ) : this( size, 0 ) {}
		private HeightMap( Int32 size, Int32 seed ) : this( size )
		{
			this.size = size;
			this.map = new Double[size][];
			for( Int32 i = 0; i < size; i++ )
				this.map[i] = new Double[size];

			if( seed == 0 )
				seed = new Random().Next();
			this.seed = seed;
		}

		#region MapGenerate
		public static HeightMap generate( Int32 size )
		{
			return generate( "DiamondSquare", size, 0 );
		}

		public static HeightMap generate( Int32 size, Int32 seed )
		{
			return generate( "DiamondSquare", size, seed );
		}
		
		public static HeightMap generate( Int32 size, Object[] args )
		{
			return generate( "DiamondSquare", size, 0, args );
		}
		
		public static HeightMap generate( Int32 size, Int32 seed, Object[] args )
		{
			return generate( "DiamondSquare", size, seed, args );
		}
		
		public static HeightMap generate( String type, Int32 size, Int32 seed )
		{
			return generate( type, size, seed, null );
		}
		
		public static HeightMap generate( String type, Int32 size, Int32 seed, Object[] args )
		{
			Type concrete = Type.GetType( type, true, true );
			iHeightMapGenerator generator = Activator.CreateInstance( concrete, args ) as iHeightMapGenerator;

			return generator.generate( new HeightMap( size, seed ) );
		}
		#endregion
	}
}