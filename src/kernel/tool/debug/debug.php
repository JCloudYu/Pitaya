<?php
/*
 * File: debug.php
 * Created by Cloud.
 * DateTime: 13/4/19 PM5:41
 */

class Debug
{
	public static function VarDumpParent() {

		echo self::VDump(func_get_args(), TRUE, TRUE);
	}

	public static function VarDump() {

		echo self::VDump(func_get_args(), TRUE);
	}

	public static function VarDumpParentString() {

		return self::VDump(func_get_args(), FALSE, TRUE);
	}

	public static function VarDumpString() {

		return self::VDump(func_get_args(), FALSE);
	}

	public static function VDump($args = array(), $forHTML = TRUE, $getParentPos = FALSE) {

		if (__DEBUG_MODE__ !== TRUE) return '';
		$width = intval(__DEBUG_CONSOLE_WIDTH__);

		$out = '';
		if($forHTML)
			$out .= "<div class='debugOpt' style='background-color: #fefe00; z-index: 9999; border: solid red; margin-bottom: 10px; padding: 5px; word-break: break-all; width: {$width}px;'>";

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

		if((array_key_exists('class', $info[1]) && $info[1]['class'] == __CLASS__) && (preg_match('/^VarDump.*/', $info[1]['function']) > 0))
			$locator = 2;
		else
			$locator = 1;

		if($getParentPos)
			$locator += 1;

		$info = @$info[$locator];

		if($locator >= count($info))
		{
			$info['file'] = 'PHP System Call';
			$info['line'] = 'Unavailable';
		}

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
					if(preg_match('/^\).*/', $chunk))
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

		if (__DEBUG_MODE__ !== TRUE) return;

		if(!is_string($outStr))
			throw(new Exception('Input variable must be a string'));

		echo "<script language='javascript'>console.log(".json_encode($outStr).");</script>";
	}

	public static function BackTrace($args = 0) {

		if (__DEBUG_MODE__ !== TRUE) return NULL;

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

			if(array_key_exists('class',  $tmp)) $adjusted[$i-1]['class']  = $tmp['class'];
			if(array_key_exists('object', $tmp)) $adjusted[$i-1]['object'] = $tmp['object'];
			if(array_key_exists('type',	  $tmp)) $adjusted[$i-1]['type']   = $tmp['type'];
			if(array_key_exists('args',	  $tmp)) $adjusted[$i-1]['args']   = $tmp['args'];
		}

		$item = array_pop($info);
		unset($item['class']);
		unset($item['object']);
		unset($item['type']);
		unset($item['args']);
		array_push($adjusted,$item);

		return $adjusted;
	}

	public static function IS_DEBUG_MODE() {

		return __DEBUG_MODE__ === TRUE;
	}
}