using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace Terra
{
	class DiamondSquareGenerator : iHeightMapGenerator
	{
		private Double levelDivisor;
		private Random Random;
		private Double range = 1;

		public const Double LEVEL_DIVISOR = 2;

		public DiamondSquareGenerator() : this( LEVEL_DIVISOR ){}
		public DiamondSquareGenerator( Double levelDivisor )
		{
			// Determines terrain smoothing
			this.levelDivisor = levelDivisor;
		}

		public HeightMap generate( HeightMap map )
		{
			this.Random = new NPack.MersenneTwister( map.seed );

			Int32 pass = map.size;
			Int32 x, y;

			while( pass > 1 )
			{
				x = 0;
				y = 0;
				while( x < map.size )
				{
					while( y < map.size )
					{
						this.diamond( map, x, y, pass );
						this.square( map, x, y, pass );

						y += pass;
					}

					x += pass;
				}

				this.range /= this.levelDivisor;
				pass >>= 1;
			}

			return map;
		}

		private void diamond( HeightMap map, Int32 x, Int32 y, Int32 pass )
		{
			Double[] points = new Double[]{};
			Int32 i = 0;

			// NW
			points.SetValue(
				map.getPoint( x, y ),
				i
			);
			i++;

			//NE
			try
			{
				points.SetValue(
					map.getPoint(
						x + pass,
						y
					),
					i
				);
				i++;
			}
			catch( IndexOutOfRangeException ) { }

			//SW
			try
			{
				points.SetValue(
					map.getPoint(
						x,
						y + pass
					),
					i
				);
				i++;
			}
			catch( IndexOutOfRangeException ) { }

			//SE
			try
			{
				points.SetValue(
					map.getPoint(
						x + pass,
						y + pass
					),
					i
				);
				i++;
			}
			catch( IndexOutOfRangeException ) { }

			// Center
			this.alterMidpoint(
				map,
				x + pass >> 1,
				y + pass >> 1,
				points
			);
		}

		private void square( HeightMap map, Int32 x, Int32 y, Int32 pass )
		{
			Double[] points = new Double[] { };
			Int32 i = 0;

			// N
			try
			{
				points.SetValue(
					map.getPoint(
						x + pass >> 1,
						y
					),
					i
				);
				i++;
			}
			catch( IndexOutOfRangeException ) { }

			//E
			try
			{
				points.SetValue(
					map.getPoint(
						x + pass,
						y + pass >> 1
					),
					i
				);
				i++;
			}
			catch( IndexOutOfRangeException ) { }

			//S
			try
			{
				points.SetValue(
					map.getPoint(
						x + pass >> 1,
						y + pass
					),
					i
				);
				i++;
			}
			catch( IndexOutOfRangeException ) { }

			//W
			try
			{
				points.SetValue(
					map.getPoint(
						x,
						y + pass >> 1
					),
					i
				);
				i++;
			}
			catch( IndexOutOfRangeException ) { }

			// Center
			this.alterMidpoint(
				map,
				x + pass >> 1,
				y + pass >> 1,
				points
			);
		}

		private void alterMidpoint( HeightMap map, Int32 x, Int32 y, Double[] points )
		{
			Double avg = 0;
			Int32 t = 0;
			foreach( Int32 i in points )
			{
				avg += points[i];
				t = i;
			}

			if( t > 0 )
				avg /= t;

			avg += this.Random.NextDouble() * this.range;

			map.setPoint( x, y, avg );
		}
	}
}
