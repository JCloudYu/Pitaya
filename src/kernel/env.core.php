<?php
	// region [ Core Path APIs ]
	// region [ Core Path Resolver ]
	final class __PATH_RESOLVER {
		private static $_path_cache = [];
		public static function Initialize() {
			if ( !is_array(@$GLOBALS['extPath']) ) $GLOBALS['extPath'] = [];
			
			// INFO: Attach custom packages
			foreach( $GLOBALS['extPath'] as $identifier => $path )
				self::$_path_cache[$identifier] = "{$path}";

			// INFO: Attach pitaya root packages
			$list = scandir(PITAYA_ROOT);
			foreach ($list as $dir) {
				$absPath = PITAYA_ROOT . "/{$dir}";
				if ( !is_dir($absPath) ) continue;
				
				self::$_path_cache[strtolower($dir)] = $absPath;
			}

			// INFO: Attach other keywords
			self::$_path_cache[ 'root' ]	= ROOT;
			self::$_path_cache[ 'lib' ]		= defined( "__LIB_PATH" ) ? __LIB_PATH : '/Lib';
			self::$_path_cache[ 'share' ]	= defined( "__SHARE_PATH" ) ? __SHARE_PATH : ROOT . '/Share';
			self::$_path_cache[ 'data' ]	= defined( "__DATA_PATH" ) ? __DATA_PATH : ROOT . '/Data';
			self::$_path_cache[ 'broot' ]	= self::$_path_cache[ 'srvroot' ] = defined( "__BASIS_PATH" ) ? __BASIS_PATH : ROOT . '/Basis';
			self::$_path_cache[ 'basis' ]	= self::$_path_cache[ 'service' ] = self::$_path_cache[ 'broot' ];
			self::$_path_cache[ 'working' ]	= ( empty($GLOBALS['STANDALONE_EXEC']) ) ? self::$_path_cache[ 'basis' ] : $GLOBALS['STANDALONE_EXEC']['cwd'];

			// Resolve to real path if targeted directory is a lnk file
			if ( IS_WIN_ENV ) {
				foreach( self::$_path_cache as $key => $path )
				{
					$linkPath = "{$path}.lnk";
					if ( is_dir( $path ) || !is_file( $linkPath ) ) continue;
					
					self::$_path_cache[ $key ] = resolveLnk( $linkPath );
				}
			}



			define( 'BASIS_ROOT',	self::$_path_cache[ 'broot' ] );
			define( 'SHARE_ROOT',	self::$_path_cache[ 'share' ] );
			define( 'DATA_ROOT',	self::$_path_cache[ 'data' ]  );
		}
		public static function Purge() {
			if ( defined( 'WORKING_ROOT' ) ) {
				self::$_path_cache[ 'basis' ] = self::$_path_cache[ 'service' ] = WORKING_ROOT;
			}
		}
		public static function Resolve( $package ) {
			return empty(self::$_path_cache[$package]) ? '' : self::$_path_cache[$package];
		}
	}
	__PATH_RESOLVER::Initialize();
	// endregion
	
	function path($referencingContext = '', $appendItem = '') {
		$tokens = explode('.', $referencingContext);
		$completePath = __PATH_RESOLVER::Resolve(array_shift($tokens));

		foreach( $tokens as $token)
			$completePath .= "/{$token}";

		$appendItem = trim($appendItem);
		return $completePath . (empty($appendItem) ? '' : "/{$appendItem}");
	}
	function using($referencingContext = '', $important = TRUE) {
		static $registeredInclusions = array();
		if ( func_num_args() == 1 && $referencingContext === TRUE ) return $registeredInclusions;

		$tokens = explode('.', $referencingContext);
		$tokens = array_reverse($tokens);

		if ( isset($registeredInclusions[($referencingContext)]) )
			return $registeredInclusions[($referencingContext)];

		if($tokens[0] == '*')
		{
			array_shift($tokens);
			$tokens = array_reverse($tokens);
			$completePath = __PATH_RESOLVER::Resolve(array_shift($tokens));


			foreach( $tokens as $token)
				$completePath .= "/{$token}";
			$completePath .= '/';

			$dirHandle = file_exists($completePath) ? opendir($completePath) : NULL;

			if($dirHandle === NULL && $important)
				throw(new Exception("Cannot locate package: {$completePath}"));

			if($dirHandle !== NULL)
			while(($entry = readdir($dirHandle)) !== FALSE)
			{
				if($entry == '.' || $entry == '..') continue;
				if(preg_match('/.*php$/', $entry) === 1)
				{
					$givenContainer = substr($referencingContext, 0, -2);
					$validEntry = substr($entry, 0, -4);

					if(isset($registeredInclusions[("$givenContainer.$validEntry")])) continue;

					$targetPath = "$completePath/$entry";

					$registeredInclusions[("$givenContainer.$validEntry")] = TRUE;

					if($important) require($targetPath);
					else include($targetPath);
				}
			}

			$registeredInclusions[($referencingContext)] = $dirHandle !== NULL;
		}
		else
		{
			$tokens = array_reverse($tokens);
			$completePath = __PATH_RESOLVER::Resolve(array_shift($tokens));

			foreach( $tokens as $token)
				$completePath .= "/{$token}";

			$completePath .= '.php';

			if(file_exists($completePath)) $registeredInclusions[($referencingContext)] = TRUE;
			else $registeredInclusions[($referencingContext)] = FALSE;

			if($important) require($completePath);
			else @include($completePath);
		}

		return $registeredInclusions[($referencingContext)];
	}
	// endregion
	
	// region [ Runtime Control APIs ]
	final class Termination {
		const STATUS_SUCCESS			= 0;
		const STATUS_ERROR				= 1;
		const STATUS_INCORRECT_USAGE	= 2;
		const STATUS_NOT_AN_EXECUTABLE	= 126;
		const STATUS_COMMAND_NOT_FOUND	= 127;
		const STATUS_SIGNAL_ERROR		= 128;

		private function __construct(){}

		public static function NORMALLY() {
			exit(self::STATUS_SUCCESS);
		}
		public static function ERROR() {
			exit(self::STATUS_ERROR);
		}
		public static function WITH_STATUS( $errorCode )
		{
			$errorCode = abs($errorCode);

			if ( $errorCode >= self::STATUS_SIGNAL_ERROR )
				$errorCode = $errorCode % self::STATUS_SIGNAL_ERROR;

			exit( $errorCode );
		}
	}
	
	// region [ Supportive APIs ]
	function PB_CODE( $baseCode, $extensionCode = 0, $shift = 1000000 ){
		return $baseCode * $shift + $extensionCode;
	}
	function PB_ERROR_CODE( $baseCode, $extensionCode = 0, $shift = 1000000 ) {
		return -PB_CODE($baseCode, $extensionCode, $shift);
	}
	// endregion
	// endregion
	
	// region [ Performance Evaluation APIs ]
	function pb_metric(){
		static $_prevTime = 0;
		
		$now = microtime(TRUE);
		$memoryUsage = memory_get_usage();
		$result = (object)[
			'memory' => (object)[
				'current' => $memoryUsage,
				'peak'	  => memory_get_peak_usage(),
				'diff'	  => $memoryUsage - (defined( 'PITAYA_METRIC_KERNEL_MEMORY' ) ? PITAYA_METRIC_KERNEL_MEMORY : 0)
			],
			'time' => (object)[
				'now' => $now,
				'dur' => $now - (defined( 'PITAYA_METRIC_BOOT_TIME' ) ? PITAYA_METRIC_BOOT_TIME : 0)
			],
			'diff' => $now - $_prevTime
		];
		
		$_prevTime = $now;
		return $result;
	}
	pb_metric();
	// endregion