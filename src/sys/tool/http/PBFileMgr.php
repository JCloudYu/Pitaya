<?php
/**
 * 0026.litchi - PBFile.php
 * Created by JCloudYu on 2013/10/09 22:08
 */

	final class PBFileMgr
	{
		public static function Chunk($fileInfo, $from, $to = NULL, $packageSize = 1024, $restrict = FALSE)
		{
			if (is_string($fileInfo) && file_exists($filePath))
			{
				$filePath = $fileInfo;
				$fileSize = filesize($filePath);
			}
			else
			if (is_array($fileInfo))
			{
				$filePath = @$fileInfo['path'];
				$fileSize = @$fileInfo['size'];
			}


			if (!file_exists($filePath))
			{
				header("HTTP/1.1 404 Not Found");
				return 404;
			}

			$hFile = fopen($filePath, "rb");
			if (empty($hFile))
			{
				header("HTTP/1.1 429 Too Many Requests");
				return 429;
			}

			header("Accept-Ranges: 0-{$fileSize}");

			if ($from >= $fileSize || $from < 0)
			{
				if ($restrict)
				{
					header("HTTP/1.1 416 Request Range Not Satisfiable");
					return 416;
				}

				$from = 0;
			}

			if ($to === NULL)
				$to = $fileSize - 1;
			else
			if (!is_numeric($to))
			{
				if ($restrict)
				{
					header("HTTP/1.1 416 Request Range Not Satisfiable");
					return 416;
				}

				$to = $fileSize - 1;
			}
			else
			if ($to >= $fileSize || $to < 0 || $to < $from)
			{
				if ($restrict)
				{
					header("HTTP/1.1 416 Request Range Not Satisfiable");
					return 416;
				}

				$to = $fileSize - 1;
			}


			if ($to != ($fileSize - 1))
				header("HTTP/1.1 206 Partial Content");
			else
				header("HTTP/1.1 200 OK");


			fseek($hFile, $from);

			$length = $to - $from + 1;
			header("Content-Range: bytes {$from}-{$to}/{$fileSize}");
			header("Content-Length: {$length}");

			set_time_limit(0);
			while (!feof($hFile) || ($length > 0))
			{
				$readSize = min($length, $packageSize);
				echo fread($hFile, $packageSize);
				flush();
				$length -= $packageSize;
			}

			fclose($hFile);
		}
	}