<?php
	final class PBLog
	{
		const LOG_INFO_TIME		= 1;
		const LOG_INFO_CATE		= 2;
		const LOG_INFO_SERVICE	= 4;
		const LOG_INFO_MODULE	= 8;
		const LOG_INFO_ROUTE	= 16;
		const LOG_INFO_ALL		= 0xFFFFFFFF;
	
		private $_logStream = NULL;
		public function __construct($logPath) {
			$this->_logStream = self::ObtainStream($logPath);
		}
		public function genLogMsg( $message, $logPos = FALSE, $logCate = '', $options = [] ) {
			if ( !is_array($options) ) $options = [];
			
			$LOG_INFO = array_key_exists( 'info-level', $options ) ? $options['info-level'] | 0 : self::LOG_INFO_ALL;
			if ( !is_string($message) ) $message = print_r( $message, TRUE );
			if ( !is_array(@$options['tags']) ) $options['tags'] = array();
			$info = self::PrepLogInfo( $logCate );



			// INFO: Process other tags
			$tags = implode('', array_map(function($item) {
				return "[{$item}]";
			}, array_unique($options['tags'])));



			// INFO: Write file stream
			$position = ($logPos) ? " {$info['position']}" : '';
			
			$timeInfo = '';
			if ( $LOG_INFO & self::LOG_INFO_TIME )
			{
				$timeInfo = in_array( 'UNIX_TIMESTAMP', $options ) ? $info['time'] : $info['timestamp'];
				$timeInfo = "[{$timeInfo}]";
			}
			
			$cateInfo	= ( $LOG_INFO & self::LOG_INFO_CATE ) ? "[{$info['cate']}]" : '';
			$basisInfo	= ( $LOG_INFO & self::LOG_INFO_SERVICE ) ? "[{$info['service']}]" : '';
			$moduleInfo	= ( $LOG_INFO & self::LOG_INFO_SERVICE ) ? "[{$info['module']}]" : '';
			$routeInfo	= ( $LOG_INFO & self::LOG_INFO_ROUTE) ? "[{$info['route']}]" : '';
			
			
			
			$msg = "{$timeInfo}{$cateInfo}{$basisInfo}{$moduleInfo}{$routeInfo}{$tags} {$message}{$position}";
			return $msg;
		}
		public function logMsg( $message, $logPos = FALSE, $logCate = '', $options = [] ) {
			if ( empty($this->_logStream) ) return FALSE;


			$msg = ( @$options[ 'row-output' ] === TRUE ) ? $message : $this->genLogMsg( $message, $logPos, $logCate, $options );
			fwrite( $this->_logStream, "{$msg}\n" );
			fflush( $this->_logStream );
			return $msg;
		}



		public static function Log($message, $logPos = FALSE, $logFileName = '', $options = array())
		{
			$logPath = DEFAULT_SYSTEM_LOG_DIR . "/" . (empty($logFileName) ? "service.pblog" : $logFileName);
			$log	 = self::ObtainLog($logPath);

			return $log->logMsg($message, $logPos, '', $options);
		}
		public static function ERRLog($message, $logPos = FALSE, $logFileName = '', $options = array())
		{
			$logPath = DEFAULT_SYSTEM_LOG_DIR . "/" . (empty($logFileName) ? "error.pblog" : $logFileName);
			$log	 = self::ObtainLog($logPath);

			error_log( $msg = $log->genLogMsg( $message, $logPos, 'ERROR', array_merge($options, [ 'info-level' => self::LOG_INFO_ALL & ~self::LOG_INFO_TIME ]) ) );
			return $log->logMsg( $message, $logPos, 'ERROR', $options );
		}
		public static function SYSLog($message, $logPos = FALSE, $logFileName = '', $options = array())
		{
			$logPath = DEFAULT_SYSTEM_LOG_DIR . "/" . (empty($logFileName) ? "system.pblog" : $logFileName);
			$log	 = self::ObtainLog($logPath);

			return $log->logMsg($message, $logPos, 'SYS', $options);
		}
		public static function ShareLog($message, $logPos = FALSE, $logFileName = '', $options = array())
		{
			$logPath = DEFAULT_SYSTEM_LOG_DIR . "/" . (empty($logFileName) ? "share.pblog" : $logFileName);
			$log	 = self::ObtainLog($logPath);

			return $log->logMsg($message, $logPos, 'SHARE', $options);
		}
		public static function CustomLog($message, $cate = 'CUSTOM', $logPos = FALSE, $logFileName = '', $options = array())
		{
			$logPath = DEFAULT_SYSTEM_LOG_DIR . "/" . (empty($logFileName) ? "custom.pblog" : $logFileName);
			$log	 = self::ObtainLog($logPath);

			return $log->logMsg($message, $logPos, empty($cate) ? 'CUSTOM' : "{$cate}", $options);
		}



		public static function ObtainLog($logFilePath)
		{
			static $_cachedLog = array();

			$pathKey = md5($logFilePath);
			if (empty($_cachedLog[$pathKey]))
				$_cachedLog[$pathKey] = new PBLog($logFilePath);

			return $_cachedLog[$pathKey];
		}
		private static function PrepLogInfo( $logCate = '' ) {

			$trace = debug_backtrace();
			array_shift($trace);	// This scope
			array_shift($trace);	// Caller scope


			// INFO: Retrieve the first module in the stack
			$counter = count($trace);
			$module = '';
			while ($counter > 0)
			{
				$inst = @$trace[$counter]['object'];
				if ( is_a($inst, PBModule::class) ) $module = $inst->class;

				$counter--;
			}


			$curTime = time();
			return array(
				'cate'		=> (empty($logCate) || !is_string($logCate)) ? 'INFO' : "{$logCate}",
				'time'		=> $curTime,
				'timestamp' => date("Y-m-d G:i:s", $curTime),
				'service'	=> (!defined('__SERVICE__') ? 'Pitaya' : __SERVICE__),
				'module'	=> $module,
				'route'		=> (SYS_EXEC_ENV === EXEC_ENV_CLI) ? 'CLI' : 'NET',
				'position'	=> "{$trace[0]['file']}:{$trace[0]['line']}"
			);
		}
		private static function ObtainStream($logFilePath)
		{
			static $_fileStream = array();

			$pathKey = md5($logFilePath);

			if (empty($_fileStream[$pathKey]))
			{
				if (is_dir($logFilePath))
					return NULL;

				$logPath = dirname($logFilePath);
				if (!is_dir($logPath)) @mkdir($logPath);



				if (is_file($logFilePath))
				{
					$today = strtotime(date('Y-m-d'));
					$fileTime = filemtime($logFilePath);

					if ($fileTime <= $today)
					{
						$fileTime = date('Ymd', filemtime($logFilePath));
						fileMove($logFilePath, "{$logFilePath}-{$fileTime}");
					}
				}


				chmod( $logFilePath, 0777 );
				$hLog = @fopen($logFilePath, 'a+b');
				if ( empty( $hLog ) ) return NULL;



				$_fileStream[$pathKey] = $hLog;
			}

			return $_fileStream[$pathKey];
		}
	}
