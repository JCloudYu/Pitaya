<?php
/**
 * 0002.pitaya.workbench - PBFile.php
 * Created by JCloudYu on 2014/07/09 02:36
 */

	final class PBStream
	{
		public static function ChunkStream($oStream, $iStream, $range, $packageSize = 1024, $restrict = FALSE)
		{
			$from = $range['from']; $to = $range['to'];
			$length = ($to - $from) + 1;

			fseek($iStream, $from);
			set_time_limit(0);

			while (!feof($iStream) && ($length > 0))
			{
				$readSize = min($length, $packageSize);
				fwrite($oStream, fread($iStream, $readSize));
				fflush($oStream);
				$length -= $readSize;
			}
		}
	}
