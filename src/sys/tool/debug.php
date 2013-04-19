<?php
/*
 * File: debug.php
 * Created by Cloud.
 * DateTime: 13/4/19 PM5:41
 */

class DEBUG
{
	private static $IS_DEBUG = __DEBUG_MODE__;

	public static function DUMP()
	{
		if(DEBUG::$IS_DEBUG) return '';

		$args = func_get_args();

		$out = '';
		foreach($args as $arg)
		{

			$indent = 0;
			foreach(explode("\n", var_export($arg, TRUE)) as $chunk)
			{
				$chunk = trim($chunk);

				if(preg_match('/.*\($/', $chunk))
				{
					$tmp = explode(' ', $chunk);

					foreach($tmp as $tmpItem)
					{
						for($i=0; $i<$indent; $i++)
							$out .= "&nbsp;&nbsp;&nbsp;&nbsp;";

						$out .= $tmpItem."<br />";
					}
					$indent++;
				}
				else
				{
					if(preg_match('/^.*\).*/', $chunk))
					{
						$indent--;

						for($i=0; $i<$indent; $i++)
							$out .= "&nbsp;&nbsp;&nbsp;&nbsp;";
					}
					else
					{
						for($i=0; $i<$indent; $i++)
							$out .= "&nbsp;&nbsp;&nbsp;&nbsp;";
					}
					$out .= $chunk."<br />";
				}
			}

			$out .= "<br />";
		}
	}
}