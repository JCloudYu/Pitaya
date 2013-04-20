<?php
/*
 * File: db.php
 * Created by Cloud.
 * DateTime: 13/4/19 PM5:41
 */

class PBDebug
{
	private static $IS_DEBUG = __DEBUG_MODE__;

	public static function VDumpHTML() {

		echo self::VDump(func_get_args(), TRUE);
	}

	public static function VDumpFILE() {

		return self::VDump(func_get_args(), FALSE);
	}

	public static function VDump($args = array(), $forHTML = TRUE) {

		if(!DEBUG::$IS_DEBUG) return '';

		$out = '';
		if($forHTML)
			$out .= '<div class="debugOpt" style="background-color: #fefe00; z-index: 9999; border: solid red; padding: 5px; word-break: break-all; width: 200px;">';

		if(!is_array($args)) $args = array($args);

		if(!$forHTML)
		{
			$indentSpace = "\t";
			$newLine = "\n";
		}
		else
		{
			$indentSpace = "&nbsp;&nbsp;&nbsp;&nbsp;";
			$newLine = "<br />";
		}

		$info = self::BackTrace();

		if($info[1]['class'] == "PBDebug" && ($info[1]['function'] == "VDumpHTML" || $info[1]['function'] == "VDumpFILE"))
			$info = $info[2];
		else
			$info = $info[1];

		if($forHTML) $out .= '<div>';
		$out .= "{$info['file']} : {$info['line']}";
		if($forHTML) $out .= '</div>';
		$out .= $newLine;


		$indent = -1;
		foreach($args as $arg)
		{
			if($indent >= 0) $out .= $newLine;

			$indent = 0;
			foreach(explode("\n", var_export($arg, TRUE)) as $chunk)
			{
				$chunk = trim($chunk);

				if(preg_match('/.*\($/', $chunk))
				{
					$tmp = explode(' ', $chunk);

					foreach($tmp as $tmpItem)
					{
						for($i=0; $i<$indent; $i++) $out .= $indentSpace;

						$out .= $tmpItem.$newLine;
					}
					$indent++;
				}
				else
				{
					if(preg_match('/^.*\).*/', $chunk))
						$indent--;

					for($i=0; $i<$indent; $i++) $out .= $indentSpace;
					$out .= $chunk.$newLine;
				}
			}
		}

		if($forHTML) $out .= '</div>';

		return $out;
	}

	public static function JSLog($outStr) {

		if(!self::$IS_DEBUG) return;

		if(!is_string($outStr))
			throw(new Exception('Input variable must be a string'));

		echo "<script language='javascript'>console.log(".json_encode($outStr).");</script>";
	}

	public static function BackTrace($args = 0) {

		if(!self::$IS_DEBUG) return NULL;

		$info = debug_backtrace($args);
		$depth = count($info);

		$adjusted = array();
		for($i=1;$i<$depth; $i++)
		{
			$adjusted[$i-1] = array();

			$tmp = $info[$i];

			$adjusted[$i-1]['file'] = $info[$i-1]['file'];
			$adjusted[$i-1]['line'] = $info[$i-1]['line'];

			$adjusted[$i-1]['function'] = $tmp['function'];
			$adjusted[$i-1]['class'] = $tmp['class'];
			if(array_key_exists('object', $tmp)) $adjusted[$i-1]['object'] = $tmp['object'];
			$adjusted[$i-1]['type'] = $tmp['type'];
			if(array_key_exists('args', $tmp)) $adjusted[$i-1]['args'] = $tmp['args'];
		}

		return $adjusted;
	}
}

class_alias('PBDebug', 'DB');
class_alias('PBDebug', 'DEBUG');