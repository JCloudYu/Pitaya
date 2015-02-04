<?php
/**
 * 1017.NeighborApp - PBStdio.php
 * Created by JCloudYu on 2015/02/04 15:00
 */
	final class PBStdIO
	{
		public static function STDERR($msg, $newLine = TRUE)
		{
			if ( $newLine ) $msg = "{$msg}\n";
			fwrite(STDERR, $msg);
		}

		public static function STDOUT($msg, $newLine = TRUE)
		{
			if ( $newLine ) $msg = "{$msg}\n";
			fwrite(STDOUT, $msg);
		}

		public static function READ($msg = "", $isPassword = FALSE, $starts = FALSE)
		{
			if ( !empty($msg) ) self::STDOUT("{$msg}", FALSE);

			if ( !$isPassword ) return fgets(STDIN);

			// Get current style
			$oldStyle = shell_exec('stty -g');

			if ($stars === FALSE)
			{
				shell_exec('stty -echo');
				$password = fgets(STDIN);
			}
			else
			{
				shell_exec('stty -icanon -echo min 1 time 0');

				$password = '';
				while( TRUE )
				{
					$char = fgetc(STDIN);

					if ( $char === "\n")
						break;
					else
					if ( ord($char) === 127 )
					{
						if (strlen($password) > 0)
						{
							fwrite(STDOUT, "\x08 \x08");
							$password = substr($password, 0, -1);
						}
					}
					else
					if ( ord($char) === 8 )
					{
						if (strlen($password) > 0)
						{
							fwrite(STDOUT, "\x08 \x08");
							$password = substr($password, 0, -1);
						}
					}
					else
					{
						fwrite(STDOUT, "*");
						$password .= $char;
					}
				}
			}

			// Reset old style
			shell_exec('stty ' . $oldStyle);

			// Return the password
			return $password;
		}
	}
