<?php
/**
 * 1017.NeighborApp - os.php
 * Created by JCloudYu on 2015/02/14 16:33
 */
	// NOTE: Class definitions
	// INFO: Debug
	final class Debug
	{
		private static $_silent = FALSE;
		public static function Silent()	 { self::$_silent = TRUE; }
		public static function Verbose() { self::$_silent = FALSE; }

		public static function IS_SILENT() { return (self::$_silent) || (__DEBUG_MODE__ !== TRUE); }
		public static function VarDumpParent() {

			echo self::VDump(func_get_args(), (SYS_EXEC_ENV == EXEC_ENV_HTTP), TRUE);
		}
		public static function VarDump() {

			echo self::VDump(func_get_args(), (SYS_EXEC_ENV == EXEC_ENV_HTTP));
		}
		public static function VarDumpParentString() {

			return self::VDump(func_get_args(), FALSE, TRUE);
		}
		public static function VarDumpString() {

			return self::VDump(func_get_args(), FALSE);
		}
		public static function VDump($args = array(), $forHTML = TRUE, $getParentPos = FALSE) {

			if ( self::IS_SILENT() ) return '';



			$width = ( defined('__DEBUG_CONSOLE_WIDTH__') ) ? intval(__DEBUG_CONSOLE_WIDTH__) : 0;

			$out = '';
			if($forHTML)
				$out .= "<div class='debugOpt' style='background-color: #fefe00; z-index: 9999; border: solid red; margin-bottom: 10px; padding: 5px; word-break: break-all; width: {$width}px;'>";

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

			$info = self::BackTrace();

			if((array_key_exists('class', $info[1]) && $info[1]['class'] == __CLASS__) && (preg_match('/^VarDump.*/', $info[1]['function']) > 0))
				$locator = 2;
			else
				$locator = 1;

			if($getParentPos)
				$locator += 1;

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
		public static function JSLog($outStr) {

			if ( self::IS_SILENT() ) return;

			if(!is_string($outStr))
				throw(new Exception('Input variable must be a string'));

			echo "<script language='javascript'>console.log(".json_encode($outStr).");</script>";
		}
		public static function BackTrace($args = 0) {

			if ( self::IS_SILENT() ) return NULL;

			$info = debug_backtrace($args);
			$depth = count($info);

			$adjusted = array();
			for( $i=1; $i<$depth; $i++)
			{
				$adjusted[$i-1] = array();

				$tmp = $info[$i];

				$adjusted[$i-1]['file'] = $info[$i-1]['file'];
				$adjusted[$i-1]['line'] = $info[$i-1]['line'];

				$adjusted[$i-1]['function'] = $tmp['function'];

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
		public static function IS_DEBUG_MODE() { return __DEBUG_MODE__ === TRUE; }
	}

	// INFO: Runtime control
	final class Termination
	{
		const STATUS_SUCCESS			= 0;
		const STATUS_ERROR				= 1;
		const STATUS_INCORRECT_USAGE	= 2;
		const STATUS_NOT_AN_EXECUTABLE	= 126;
		const STATUS_COMMAND_NOT_FOUND	= 127;
		const STATUS_SIGNAL_ERROR		= 128;

		private function __construct(){}

		public static function NORMALLY()		 { exit( self::STATUS_SUCCESS ); }
		public static function WITH_STATUS( $errorCode = self::STATUS_ERROR )
		{
			$errorCode = abs($errorCode);

			if ( $errorCode >= self::STATUS_SIGNAL_ERROR )
				$errorCode = $errorCode % self::STATUS_SIGNAL_ERROR;

			exit( $errorCode );
		}
	}







	// NOTE: Parse System Arguments
	call_user_func(function() {
		$conf = array();
		$argv = $_SERVER['argv'];

		$RUN = TRUE;
		do
		{
			switch ( @"{$argv[0]}" )
			{
				case "--timezone":
				case "-tz":
					array_shift($argv);
					$TZ = @array_shift($argv);

					if ( empty($TZ) )
					{
						error_log("-tz option must be followed with timezone identifier!");
						Termination::WITH_STATUS(Termination::STATUS_INCORRECT_USAGE);
					}

					$conf['TZ'] = $TZ;
					break;

				default:
					$RUN = $RUN && FALSE;
			}
		}
		while( $RUN );



		$_SERVER['argv'] = $GLOBALS['RUNTIME_ARGV'] = $argv;
		$_SERVER['argc'] = $GLOBALS['RUNTIME_ARGC'] = count( $GLOBALS['RUNTIME_ARGV'] );

		$GLOBALS['RUNTIME_CONF'] = $conf;
	});






	if (__OS__ === 'WIN')
	{
		define('CMD_MOVE', 'move');
		define('CMD_COPY', 'copy');
	}
	else
	{
		define('CMD_MOVE', 'mv');
		define('CMD_COPY', 'cp');
	}



	// INFO: Setting Environmental Constants
	call_user_func(function(){

		if ( !defined('__DEBUG_MODE__') )
		{
			$ENV_DEBUG_MODE = IS_POSITIVE( @$GLOBALS['RUNTIME_ENV']['PITAYA_EXEC_DEBUG_MODE'] );
			define( '__DEBUG_MODE__', $ENV_DEBUG_MODE || FALSE);
		}




		if ( !defined('__THROW_EXCEPTION__') )
		{
			$ENV_THROW_EXCEPTION = IS_POSITIVE( @$GLOBALS['RUNTIME_ENV']['PITAYA_EXEC_THROW_EXCEPTION'] );
			define( '__THROW_EXCEPTION__', $ENV_THROW_EXCEPTION || FALSE);
		}


		if ( !defined('__LOG_EXCEPTION__') )
			define( '__LOG_EXCEPTION__', TRUE );
	});






	if ( !defined('__EVENT_IDENTIFIER_LEN__') )	define('__EVENT_IDENTIFIER_LEN__',	16);
	define('CONFIG_SESSION_STORAGE_PATH', ini_get('session.save_path'));






	// INFO: Error handling supportive apis
	function PB_CODE( $baseCode, $extensionCode = 0, $shift = 1000000 ){
		return $baseCode * $shift + $extensionCode;
	}
	function PB_ERROR_CODE( $baseCode, $extensionCode = 0, $shift = 1000000 ) {
		return -PB_CODE($baseCode, $extensionCode, $shift);
	}
