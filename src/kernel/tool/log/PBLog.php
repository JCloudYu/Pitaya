<?php

	final class PBLog
	{
		public static function ERRLog($message, $logPos = FALSE, $logFileName = '', $options = array())
		{
			$logPath = path('share.log', (empty($logFileName) ? 'error.log' : $logFileName));
			$stream  = self::LogStream($logPath);
			return self::LogMsg($stream, $message, $logPos, 'ERROR', $options);
		}

		public static function SYSLog($message, $logPos = FALSE, $logFileName = '', $options = array())
		{
			$logPath = path('share.log', (empty($logFileName) ? 'system.log' : $logFileName));
			$stream  = self::LogStream($logPath);
			return self::LogMsg($stream, $message, $logPos, 'SYS', $options);
		}

		public static function Log($message, $logPos = FALSE, $logFileName = '', $options = array())
		{
			$logPath = path('share.log', (empty($logFileName) ? "service.log" : $logFileName));
			$stream  = self::LogStream($logPath);

			$defaultCate = defined('__WORKING_ROOT__') ? "SERVICE \"".__SERVICE__."\"" : "SERVICE";

			return self::LogMsg($stream, $message, $logPos, $defaultCate, $options);
		}

		public static function ShareLog($message, $logPos = FALSE, $logFileName = '', $options = array())
		{
			$logPath = path('share.log', (empty($logFileName) ? 'share.log' : $logFileName));
			$stream  = self::LogStream($logPath);
			return self::LogMsg($stream, $message, $logPos, 'SHARE', $options);
		}

		public static function CustomLog($message, $class = 'CUSTOM', $logPos = FALSE, $logFileName = '', $options = array())
		{
			$logPath = path('share.log', (empty($logFileName) ? 'custom.log' : $logFileName));
			$stream  = self::LogStream($logPath);
			return self::LogMsg($stream, $message, $logPos, empty($class) ? 'CUSTOM' : "{$class}", $options);
		}




		public static function GetLogTag($options = array())
		{
			static $remoteIP = NULL;



			$tags = array();

			if (in_array('REMOTE_IP', $options))
			{
				if ($remoteIP === NULL)
					$remoteIP = RemoteIP(PBRequest::Request()->server, TRUE,TRUE);

				$tags[] = (__SYS_WORKING_ENV__ === SYS_COMMAND_ENV) ? '[CLI]' : "[{$remoteIP}]";
			}

			if (is_array(@$options['tags']))
				foreach ($options['tags'] as $tag) $tags[] = "[{$tag}]";


			return implode('', $tags);
		}

		public static function LogMsg($stream, $message, $logPos = FALSE, $logCate = '', $options = array())
		{
			$position = '';
			if ($logPos)
			{
				$trace = debug_backtrace();
				$position = "({$trace[1]['file']}:{$trace[1]['line']})";
			}



			if (!empty($logCate)) $logCate = "[{$logCate}]";
			$logTime = (empty(@$options['UNIX_TIMESTAMP'])) ? date("Y-m-d G:i:s") : time();



			$additionalTag = self::GetLogTag($options);
			$tags = "[{$logTime}]{$logCate}{$additionalTag}";



			if (!is_string($message)) $message = print_r($message, TRUE);
			$msg = "{$tags} {$message} {$position}\n";
			fwrite($stream, $msg);
			fflush($stream);

			return $msg;
		}

		public static function LogStream($logFilePath)
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
