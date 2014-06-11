<?php

	final class PBLog
	{
		public static function SYSLog($message, $logPos = FALSE, $logFileName = '')
		{
			$logPath = path('share.log', (empty($logFileName) ? 'system.log' : $logFileName));
			$stream  = self::LogStream($logPath);
			self::LogMsg($stream, $message, $logPos, 'SYS');
		}

		public static function Log($message, $logPos = FALSE, $logFileName = '')
		{
			$logPath = path('share.log', (empty($logFileName) ? "service.log" : $logFileName));
			$stream  = self::LogStream($logPath);

			$defaultCate = defined('__WORKING_ROOT__') ? "SERVICE \"".__SERVICE__."\"" : "SERVICE";

			self::LogMsg($stream, $message, $logPos, $defaultCate);
		}

		public static function ShareLog($message, $logPos = FALSE, $logFileName = '')
		{
			$logPath = path('share.log', (empty($logFileName) ? 'share.log' : $logFileName));
			$stream  = self::LogStream($logPath);
			self::LogMsg($stream, $message, $logPos, 'SHARE');
		}

		public static function LogMsg($stream, $message, $logPos = FALSE, $logCate = '')
		{
			$position = '';
			if ($logPos)
			{
				$trace = debug_backtrace();
				$position = "({$trace[1]['file']}:{$trace[1]['line']})";
			}

			if (!empty($logCate))
				$logCate = "[{$logCate}]";

			$now = '[' . date("Y-m-d g:i:s") . "]{$logCate}";

			if (!is_string($message)) $message = print_r($message, TRUE);
			$msg = "$now $message {$position}\n";
			fwrite($stream, $msg);
			fflush($stream);
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
