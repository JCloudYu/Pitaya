<?php
/**
 * 0001.pitaya - PBLog.php
 * Created by JCloudYu on 2014/07/04 17:28
 */

	using('ext.net.ip');

	final class PBLog
	{
		private $_logStream = NULL;
		public function __construct($logPath)
		{
			$this->_logStream = self::ObtainStream($logPath);
		}

		public function logMsg($message, $logPos = FALSE, $logCate = '', $options = array())
		{
			if (!is_string($message)) $message = print_r($message, TRUE);
			if (!is_array(@$options['tags'])) $options['tags'] = array();
			$info = self::PrepLogInfo($logCate, $options);



			// INFO: Process other tags
			$tags = implode('', array_map(function($item) {
				return "[{$item}]";
			}, array_unique($options['tags'])));



			// INFO: Write file stream
			$position = ($logPos) ? " {$info['position']}" : '';
			$msg = "[{$info['time']}][{$info['cate']}][{$info['service']}][{$info['module']}][{$info['route']}]{$tags} {$message}{$position}\n";
			fwrite($this->_logStream, $msg);
			fflush($this->_logStream);

			return $msg;
		}






		// INFO: Global logging API
		public static function Log($message, $logPos = FALSE, $logFileName = '', $options = array())
		{
			$logPath = path('share.log', (empty($logFileName) ? "service.log" : $logFileName));
			$log	 = self::ObtainLog($logPath);

			return $log->logMsg($message, $logPos, '', $options);
		}

		public static function ERRLog($message, $logPos = FALSE, $logFileName = '', $options = array())
		{
			$logPath = path('share.log', (empty($logFileName) ? "error.log" : $logFileName));
			$log	 = self::ObtainLog($logPath);

			return $log->logMsg($message, $logPos, 'ERROR', $options);
		}

		public static function SYSLog($message, $logPos = FALSE, $logFileName = '', $options = array())
		{
			$logPath = path('share.log', (empty($logFileName) ? "system.log" : $logFileName));
			$log	 = self::ObtainLog($logPath);

			return $log->logMsg($message, $logPos, 'SYS', $options);
		}

		public static function ShareLog($message, $logPos = FALSE, $logFileName = '', $options = array())
		{
			$logPath = path('share.log', (empty($logFileName) ? "share.log" : $logFileName));
			$log	 = self::ObtainLog($logPath);

			return $log->logMsg($message, $logPos, 'SHARE', $options);
		}

		public static function CustomLog($message, $cate = 'CUSTOM', $logPos = FALSE, $logFileName = '', $options = array())
		{
			$logPath = path('share.log', (empty($logFileName) ? "custom.log" : $logFileName));
			$log	 = self::ObtainLog($logPath);

			return $log->logMsg($message, $logPos, empty($cate) ? 'CUSTOM' : "{$cate}", $options);
		}






		/**
		 * Produce environmental information
		 *
		 * @param string $logCate
		 * @param array $options
		 *
		 * @return array
		 */
		private static function PrepLogInfo($logCate = '', $options = array())
		{
			static $remoteIp = NULL;

			// INFO: Collect remote ip
			if ($remoteIp === NULL)
				$remoteIp = RemoteIP(PBRequest::Request()->server, TRUE, TRUE);

			// INFO:
			if (__SYS_WORKING_ENV__ === SYS_COMMAND_ENV)
			{
				$route = 'CLI';

				if (function_exists("posix_geteuid") && function_exists("posix_getpwuid"))
				{
					$shellInfo = posix_getpwuid(posix_getuid());
					$route = "CLI({$shellInfo['name']}|{$shellInfo['uid']}|{$shellInfo['gid']})";
				}
			}
			else
				$route = $remoteIp;



			$trace = debug_backtrace();
			array_shift($trace);	// This scope
			array_shift($trace);	// Caller scope


			// INFO: Retrieve the first module in the stack
			$counter = count($trace);
			$module = '';
			while ($counter > 0)
			{
				$inst = @$trace[$counter]['object'];
				if (is_a($inst, 'PBModule')) $module = $inst->class;

				$counter--;
			}


			return array(
				'cate'		=> (empty($logCate) || !is_string($logCate)) ? 'INFO' : "{$logCate}",
				'time'		=> (@empty($options['UNIX_TIMESTAMP'])) ? date("Y-m-d G:i:s") : time(),
				'service'	=> (!defined('__SERVICE__') ? 'Pitaya' : __SERVICE__),
				'module'	=> $module,
				'route'		=> $route,
				'position'	=> "{$trace[0]['file']}:{$trace[0]['line']}"
			);
		}

		/**
		 * Obtain log singleton according to log file path
		 *
		 * @param string $logFilePath
		 *
		 * @return PBLog
		 */
		public static function ObtainLog($logFilePath)
		{
			static $_cachedLog = array();

			$pathKey = md5($logFilePath);
			if (empty($_cachedLog[$pathKey]))
				$_cachedLog[$pathKey] = new PBLog($logFilePath);

			return $_cachedLog[$pathKey];
		}

		/**
		 * Obtain log stream according to log file path
		 *
		 * @param string $logFilePath
		 *
		 * @return Resource
		 */
		private static function ObtainStream($logFilePath)
		{
			static $_fileStream = array();

			$pathKey = md5($logFilePath);

			if (empty($_fileStream[$pathKey]))
			{
				if (is_dir($logFilePath))
					$logFilePath = "{$logFilePath}/default.log";

				$logPath = dirname($logFilePath);
				if (!is_dir($logPath)) mkdir($logPath);



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

				$_fileStream[$pathKey] = fopen($logFilePath, 'a+b');
			}

			return $_fileStream[$pathKey];
		}
	}
