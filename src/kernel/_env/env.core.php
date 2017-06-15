<?php
	// region [ Core Path APIs ]
	final class PBPathResolver {
		private static $_path_cache = [];
		private static $_kernel_cache = [];
		public static function Initialize() {
			static $_initialized = FALSE;
			if ( $_initialized ) return;
			
			

			// INFO: Attach pitaya root packages
			$list = scandir(PITAYA_ROOT);
			foreach ($list as $dir) {
				$absPath = PITAYA_ROOT . "/{$dir}";
				if ( !is_dir($absPath) ) continue;
				
				self::$_kernel_cache[strtolower($dir)] = $absPath;
			}

			// INFO: Attach other keywords
			self::$_kernel_cache[ 'root' ]		= ROOT;
			self::$_kernel_cache[ 'lib' ]		= defined( "__LIB_PATH" ) ?   __LIB_PATH   : ROOT.'/Lib';
			self::$_kernel_cache[ 'share' ]		= defined( "__SHARE_PATH" ) ? __SHARE_PATH : ROOT.'/Share';
			self::$_kernel_cache[ 'data' ]		= defined( "__DATA_PATH" ) ?  __DATA_PATH  : ROOT.'/Data';
			self::$_kernel_cache[ 'broot' ]		= defined( "__BASIS_PATH" ) ? __BASIS_PATH : ROOT.'/Basis';
			self::$_kernel_cache[ 'working' ]	= self::$_kernel_cache[ 'basis' ] = self::$_kernel_cache[ 'broot' ];

			// Resolve to real path if targeted directory is a lnk file
			if ( IS_WIN_ENV ) {
				foreach( self::$_kernel_cache as $key => $path )
				{
					$linkPath = "{$path}.lnk";
					if ( is_dir( $path ) || !is_file( $linkPath ) ) continue;
					
					self::$_kernel_cache[ $key ] = __resolve_lnk( $linkPath );
				}
			}
			
			
			self::$_path_cache = self::$_kernel_cache;
			$_initialized = TRUE;
		}
		public static function Purge() {
			static $_purged = FALSE;
			if ( $_purged ) return;
		
			if ( defined( 'WORKING_ROOT' ) ) {
				self::$_kernel_cache[ 'working' ] = self::$_path_cache[ 'basis' ] = WORKING_ROOT;
				$_purged = TRUE;
			}
		}
		public static function Register( $map = [] ) {
			if ( !is_array($map) ) return TRUE;
			
			foreach( $map as $key => $path ) {
				if ( IS_WIN_ENV ) {
					$linkPath = "{$path}.lnk";
					if ( !is_dir( $path ) && is_file( $linkPath ) ) {
						$path = __resolve_lnk( $linkPath );
					}
				}
				
				self::$_path_cache[ $key ] = $path;
			}
			
			
			
			foreach( self::$_kernel_cache as $key => $path ) {
				self::$_path_cache[ $key ] = $path;
			}
		}
		public static function Resolve( $package ) {
			return empty(self::$_path_cache[$package]) ? '' : self::$_path_cache[$package];
		}
	}
	PBPathResolver::Initialize();
	
	function path($referencingContext = '', $appendItem = '') {
		$tokens = explode('.', $referencingContext);
		$completePath = PBPathResolver::Resolve(array_shift($tokens));

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
			$completePath = PBPathResolver::Resolve(array_shift($tokens));


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
			$completePath = PBPathResolver::Resolve(array_shift($tokens));

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
	
	final class DEBUG {
		public static function VarDump(...$args) {
			echo self::VDump($args, IS_HTTP_ENV);
		}
		public static function VDump($args = array(), $forHTML = TRUE) {
			$G_CONF = PBStaticConf( 'pitaya-env' );
			
			if ( !$G_CONF[ 'debug-mode' ] ) return '';


			$width = intval($G_CONF[ 'debug-console-width' ]);

			$out = '';
			if($forHTML)
				$out .= "<div class='debugOpt' style='background-color:#fefe00; z-index:9999; border:solid red; margin-bottom:10px; padding:5px; word-break:break-all; width:{$width}px; color:#000; position:relative;'>";

			if(!is_array($args)) $args = array($args);

			if(!$forHTML)
			{
				$indentSpace = "\t";
				$newLine = "\n";
			}
			else
			{
				$indentSpace = "&nbsp;&nbsp;&nbsp;&nbsp;";
				$newLine = "<br />";
			}


			if ( DEBUG_BACKTRACE_ENABLED ) {
				$info = self::BackTrace();
	
				if((array_key_exists('class', $info[1]) && $info[1]['class'] == __CLASS__) && (preg_match('/^VarDump.*/', $info[1]['function']) > 0))
					$locator = 2;
				else
					$locator = 1;
	
				$info = @$info[$locator];
	
				if($locator >= count($info))
				{
					$info['file'] = 'PHP System Call';
					$info['line'] = 'Unavailable';
				}
	
				if($forHTML) $out .= '<div>';
				$out .= "{$info['file']} : {$info['line']}";
				if($forHTML) $out .= '</div>';
				$out .= $newLine;
			}



			$indent = -1;
			foreach($args as $arg)
			{
				if($indent >= 0) $out .= $newLine;

				$indent = 0;
				foreach(explode("\n", var_export($arg, TRUE)) as $chunk)
				{
					$chunk = trim($chunk);

					if(preg_match('/.*\($/', $chunk))
					{
						$tmp = explode(' ', $chunk);

						foreach($tmp as $tmpItem)
						{
							for($i=0; $i<$indent; $i++) $out .= $indentSpace;

							$out .= $tmpItem.$newLine;
						}
						$indent++;
					}
					else
					{
						if(preg_match('/^\).*/', $chunk))
							$indent--;

						for($i=0; $i<$indent; $i++) $out .= $indentSpace;
						$out .= $chunk.$newLine;
					}
				}
			}

			if($forHTML) $out .= '</div>';

			return $out;
		}
		
		
		public static function BackTrace($args = 0) {
			if ( !DEBUG_BACKTRACE_ENABLED ) return NULL;



			$info = debug_backtrace($args);
			$depth = count($info);

			$adjusted = array();
			for ( $i=1; $i<$depth; $i++ ) {
				$adjusted[$i-1] = array();

				$tmp = $info[$i];

				@$adjusted[$i-1]['file'] = @$info[$i-1]['file'];
				@$adjusted[$i-1]['line'] = @$info[$i-1]['line'];

				@$adjusted[$i-1]['function'] = @$tmp['function'];

				if(array_key_exists('class',  $tmp)) $adjusted[$i-1]['class']  = $tmp['class'];
				if(array_key_exists('object', $tmp)) $adjusted[$i-1]['object'] = $tmp['object'];
				if(array_key_exists('type',	  $tmp)) $adjusted[$i-1]['type']   = $tmp['type'];
				if(array_key_exists('args',	  $tmp)) $adjusted[$i-1]['args']   = $tmp['args'];
			}

			$item = array_pop($info);
			unset($item['class']);
			unset($item['object']);
			unset($item['type']);
			unset($item['args']);
			array_push($adjusted,$item);

			return $adjusted;
		}
	}
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
	function pb_metric(){
		static $_prevTime = 0, $_prevMem = 0;
		
		$now = microtime(TRUE);
		$memoryUsage = memory_get_usage();
		$result = (object)[
			'memory' => (object)[
				'current' => $memoryUsage,
				'peak'	  => memory_get_peak_usage(),
				'diff'	  => $memoryUsage - $_prevMem
			],
			'time' => (object)[
				'now' => $now,
				'dur' => $now - PITAYA_METRIC_BOOT_TIME
			],
			'diff' => $now - $_prevTime
		];
		
		$_prevTime	= $now;
		$_prevMem	= $memoryUsage;
		return $result;
	}