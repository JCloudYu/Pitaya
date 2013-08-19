<?php

	final class PBLog
	{
		const LOG_DIR = '/Log';
		public static function SYSLog($message, $logPos = FALSE)
		{
			static $_FILE = NULL;
			if ($_FILE === NULL)
			{
				$logPath = __WEB_ROOT__ . self::LOG_DIR;
				if (!is_dir($logPath)) mkdir($logPath);

				$logPath .= '/core_log';
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

			$now = '[' . date("Y-m-d g:i:s") . "]{$position}";
			$msg = "$now $message\n";
			fwrite($_FILE, $msg);
			fflush($_FILE);
		}

		public static function log($message, $logPos = FALSE)
		{
			static $_srvLogPath = NULL;
			static $_FILE = NULL;
			if ($_srvLogPath === NULL)
			{
				if (defined('__WORKING_ROOT__'))
				{
					$logPath = __WORKING_ROOT__ . self::LOG_DIR;
					if (!is_dir($logPath)) mkdir($logPath);

					$_srvLogPath = $logPath .= '/service_log';
					if ($_FILE) fclose($_FILE);
					$_FILE = NULL;
				}
				else
				{
					$logPath = __WEB_ROOT__ . self::LOG_DIR;
					if (!is_dir($logPath)) mkdir($logPath);

					$logPath .= '/core_log';
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

			$now = '[' . date("Y-m-d g:i:s") . "]{$position}";
			$msg = "$now $message\n";
			fwrite($_FILE, $msg);
			fflush($_FILE);
		}
	}