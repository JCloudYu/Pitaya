<?php

	using('kernel.basis.PBObject');
	using('ext.base.math');

	define('AngleKM', 0.009026867286858, TRUE);
	
	final class GPSEarthModelID
	{
		const WGS84 = 'WGS84';
		const NAD27 = 'NAD27';
		const International = 'International';
		const Krasovsky = 'Krasovsky';
		const Bessel = 'Bessel';
		const WGS72 = 'WGS72';
		const WGS66 = 'WGS66';
	};
	
	global $EarthEllipse;
	$GLOBALS['EarthEllipse'] = array(
		'WGS84'			=> array('a' => 3443.918466523, 'invf' => 298.257223563, 'radius' => 6378.137),
		'NAD27'			=> array('a' => 3443.955939525, 'invf' => 294.9786982138, 'radius' => 6378.2064),
		'International'	=> array('a' => 3444.053995680, 'invf' => 297.0, 'radius' => 6378.388),
		'Krasovsky'		=> array('a' => 3443.976781857, 'invf' => 298.3, 'radius' => 6378.245),
		'Bessel'		=> array('a' => 3443.518982181, 'invf' => 299.1528, 'radius' => 6377.397155),
		'WGS72'			=> array('a' => 3443.917386609, 'invf' => 298.26, 'radius' => 6378.135),
		'WGS66'			=> array('a' => 3443.922786177, 'invf' => 298.25, 'radius' => 6378.145));
	
	//N is positive, E is positive
	final class GPSPos extends PBObject
	{
		private $mLatitude;
		private $mLongitude;

		public function __construct($Latitude, $Longitude)
		{
			$this->__set_latitude($Latitude);
			$this->__set_longitude($Longitude);
		}

		public function __clone()
		{
			return new GPSPos($this->mLatitude, $this->mLongitude);
		}

		public function __get_latitude(){return $this->mLatitude;}
		public function __get_longitude(){return $this->mLongitude;}

		public function __set_latitude($value)
		{
			if($value>90.0) $value = 90.0;
			else if($value<-90.0) $value = -90.0;

			$this->mLatitude = $value;
		}

		public function __set_longitude($value)
		{
			if($value>180.0) $value = 180.0;
			else if($value<-180.0) $value = -180.0;

			$this->mLongitude = $value;
		}

		public function __get_dirLat(){return $this->mLatitude >= 0 ? 'NORTH':'SOUTH';}
		public function __get_dirLng(){return $this->mLongitude >= 0 ? 'EAST':'WEST';}
	};
	
	
	function GPSGetDistance($pos1, $pos2, $ModelID = GPSEarthModelID::WGS84)
	{	
//		$rt = array('DISTANCE' => 0.0, 'COURSE' => 0.0);
		$pt1 = array('LAT' => 0.0, 'LNG' => 0.0);
		$pt2 = array('LAT' => 0.0, 'LNG' => 0.0);
		
		if(!is_a($pos1, 'GPSPos') || !is_a($pos2, 'GPSPos') || !is_string($ModelID)) return 0;/*return $rt;*/
		
		$r = 0.0; $tu1 = 0.0; $tu2 = 0.0; $cu1 = 0.0; $su1 = 0.0; $cu2 = 0.0; $s1 = 0.0; $b1 = 0.0; $f1 = 0.0;
		$x = 0.0; $sx = 0.0; $cx = 0.0; $sy = 0.0; $cy = 0.0; $y = 0.0; $sa = 0.0; $c2a = 0.0; $cz = 0.0; $e = 0.0; $c = 0.0; $d = 0.0;
		$EPS = 0.00000000005;
		$distanceFactor = 1.852;
		global $EarthEllipse;
		
		$a = $EarthEllipse[$ModelID]['a'];
		$f = 1.0/$EarthEllipse[$ModelID]['invf'];

		$pt1['LAT'] = $pos1->latitude * DEG2RAD;
		$pt1['LNG'] = -$pos1->longitude * DEG2RAD;
		$pt2['LAT'] = $pos2->latitude * DEG2RAD;
		$pt2['LNG'] = -$pos2->longitude * DEG2RAD;
		
		if($pt1['LAT']+$pt2['LAT'] == 0.0 && abs($pt1['LNG'] - $pt2['LNG']) == PI)
			$pt1['LAT'] = $pt2['LAT'] + 0.00001;
		
		if($pt1['LAT']==$pt2['LAT'] && ($pt1['LNG'] == $pt2['LNG'] || abs(abs($pt1['LNG'] - $pt2['LNG']) - 2 * PI) < $EPS))
			return 0; /*return $rt;*/
		
		$r = 1 - $f;
		$tu1 = $r * tan($pt1['LAT']);
		$tu2 = $r * tan($pt2['LAT']);
		
		$cu1 = 1.0 / sqrt(1.0 + $tu1 * $tu1);
		$su1 = $cu1 * $tu1;
		$cu2 = 1.0 / sqrt(1.0 + $tu2 * $tu2);
		
		$s1 = $cu1 * $cu2;
		$b1 = $s1 * $tu2;
		$f1 = $b1 * $tu1;
		
		$x = $pt2['LNG'] - $pt1['LNG'];
		$d = $x + 1.0;
		
		$MaxIter = 100;
		$iter = 1;
		
		while((abs($d - $x) > $EPS) && ($iter < $MaxIter))
		{
			$iter++;
			
			$sx = sin($x);
			$cx = cos($x);
			
			$tu1 = $cu2 * $sx;
			$tu2 = $b1 - $su1 * $cu2 * $cx;
			$sy = sqrt($tu1 * $tu1 + $tu2 * $tu2);
			$cy = $s1 * $cx + $f1;
			$y = atan2($sy, $cy);
			
			$sa = $s1 * $sx / $sy;
			$c2a = 1.0 - $sa * $sa;
			$cz = $f1 + $f1;
			
			if($c2a > 0.0)
				$cz = $cy - $cz/$c2a;
			
			$e = $cz * $cz * 2 - 1.0;
			$c = ((-3.0 * $c2a + 4.0) * $f + 4.0) * $c2a * $f / 16.0;
			$d = $x;
			$x = (($e * $cy * $c + $cz) * $sy * $c + $y) * $sa;
			$x = (1.0 - $c) * $x * $f + $pt2['LNG'] - $pt1['LNG'];
		}
		$faz = atan2($tu1, $tu2) - (2.0 * PI) * floor(atan2($tu1, $tu2) / (2.0 * PI));
		
		$x = sqrt((1.0 / ($r * $r) - 1.0) * $c2a + 1.0);
		$x += 1.0;
		$x = ($x - 2.0) / $x;
		$c = 1.0 - $x;
		$c = ($x * $x / 4.0 + 1.0) / $c;
		$d = (0.375 * $x * $x - 1.0) * $x; 
		
		$x = $e * $cy;
		$s = (((($sy * $sy * 4.0 - 3.0)*(1.0 - $e - $e) * $cz * $d / 6.0 - $x) * $d/4.0 + $cz) * $sy * $d + $y) * $c * $a * $r;
		
//		$rt['DISTANCE'] = $s * $distanceFactor;
//		$rt['COURSE'] = $faz * RAD2DEG;
		
//		return $rt;
		return $s * $distanceFactor;
	}

	//record angle per km
	final class GPSUnitAngles
	{
		const NumSections = 91;
		
		public static function Generate($ModelID = GPSEarthModelID::WGS84)
		{
			$container = array('LAT' => array(), 'LNG' => array());
			$container['LAT'][90] = $container['LNG'][90] = 0;
			
			$point1 = new GPSPos(0, 0);
			$point2 = new GPSPos(0, 0);
			
			for($i=0; $i<90; $i++)
			{
				$point1->latitude = $i;		$point1->longitude = 0;
				$point2->latitude = $i+1;	$point2->longitude = 0;
				$obj = GPSGetDistance($point1, $point2, $ModelID);
				
//				$result = $container['LAT'][$i] = $obj['DISTANCE'];
				$result = $container['LAT'][$i] = $obj;
				
				$point1->latitude = $i;		$point1->longitude = 0;
				$point2->latitude = $i;		$point2->longitude = 1;
				$obj = GPSGetDistance($point1, $point2, $ModelID);
				
//				$result = $container['LNG'][$i] = $obj['DISTANCE'];
				$result = $container['LNG'][$i] = $obj;
			}
			
			return $container;
		}
	};