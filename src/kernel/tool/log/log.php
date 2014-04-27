<?php

	final class PBLog
	{
		const LOG_DIR = '/Log';
		public static function SYSLog($message, $logPos = FALSE, $logFileName = '')
		{
			static $_FILE = NULL;
			if ($_FILE === NULL)
			{
				$logPath = __WEB_ROOT__ . self::LOG_DIR;
				if (!is_dir($logPath)) mkdir($logPath);

				$logPath .= "/" . (empty($logFileName) ? 'core.log' : $logFileName);
				if (is_file($logPath))
				{
					$today = strtotime(date('Y-m-d'));
					$fileTime = filemtime($logPath);

					if ($fileTime <= $today)
					{
						$fileTime = date('Ymd', filemtime($logPath));
						fileMove($logPath, "{$logPath}-{$fileTime}");
					}
				}

				$_FILE = fopen($logPath, 'a+b');
			}

			// INFO: Unexpected error
			if ($_FILE === FALSE) return;

			$position = '';
			if ($logPos)
			{
				$trace = debug_backtrace();
				$position = "({$trace[0]['file']}:{$trace[0]['line']})";
			}

			$now = '[' . date("Y-m-d g:i:s") . "][SYS]";

			if (!is_string($message)) $message = print_r($message, TRUE);
			$msg = "$now $message {$position}\n";
			fwrite($_FILE, $msg);
			fflush($_FILE);
		}

		public static function Log($message, $logPos = FALSE, $logFileName = '')
		{
			static $_srvLogPath = NULL;
			static $_FILE = NULL;
			static $_logName = 'SYS';

			if ($_srvLogPath === NULL)
			{
				if (defined('__WORKING_ROOT__'))
				{
					$logPath = __WORKING_ROOT__ . self::LOG_DIR;
					if (!is_dir($logPath)) mkdir($logPath);

					$_srvLogPath = $logPath .= '/' . (empty($logFileName) ? 'service.log' : $logFileName);
					if ($_FILE) fclose($_FILE);
					$_FILE = NULL;
					$_logName = __SERVICE__;
				}
				else
				{
					$logPath = __WEB_ROOT__ . self::LOG_DIR;
					if (!is_dir($logPath)) mkdir($logPath);

					$logPath .= '/' . (empty($logFileName) ? 'core.log' : $logFileName);
				}

				if (is_file($logPath))
				{
					$today = strtotime(date('Y-m-d'));
					$fileTime = filemtime($logPath);

					if ($fileTime <= $today)
					{
						$fileTime = date('Ymd', filemtime($logPath));
						fileMove($logPath, "{$logPath}-{$fileTime}");
					}
				}

				$_FILE = fopen($logPath, 'a+b');
			}

			// INFO: Unexpected error
			if ($_FILE === FALSE) return;

			$position = '';
			if ($logPos)
			{
				$trace = debug_backtrace();
				$position = "({$trace[0]['file']}:{$trace[0]['line']})";
			}

			$now = '[' . date("Y-m-d g:i:s") . "][{$_logName}]";

			if (!is_string($message)) $message = print_r($message, TRUE);
			$msg = "$now $message {$position}\n";
			fwrite($_FILE, $msg);
			fflush($_FILE);
		}

		public static function ShareLog($message, $logPos = FALSE, $logFileName = '')
		{
			static $_FILE = NULL;
			if ($_FILE === NULL)
			{
				$logPath = path('share.logs');
				if (!is_dir($logPath)) mkdir($logPath);

				$logPath .= "/" . (empty($logFileName) ? 'share.log' : $logFileName);
				if (is_file($logPath))
				{
					$today = strtotime(date('Y-m-d'));
					$fileTime = filemtime($logPath);

					if ($fileTime <= $today)
					{
						$fileTime = date('Ymd', filemtime($logPath));
						fileMove($logPath, "{$logPath}-{$fileTime}");
					}
				}

				$_FILE = fopen($logPath, 'a+b');
			}

			// INFO: Unexpected error
			if ($_FILE === FALSE) return;

			$position = '';
			if ($logPos)
			{
				$trace = debug_backtrace();
				$position = "({$trace[0]['file']}:{$trace[0]['line']})";
			}

			$now = '[' . date("Y-m-d g:i:s") . "][SHARE]";

			if (!is_string($message)) $message = print_r($message, TRUE);
			$msg = "$now $message {$position}\n";
			fwrite($_FILE, $msg);
			fflush($_FILE);
		}
	}