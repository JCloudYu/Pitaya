<?php
/*
 * File: css.php
 * Created by Cloud.
 * DateTime: 13/4/28 PM1:55
 */

class CSS
{
	private static $raw = array();
	private static $lib = array();

	public static function AddStyle($style) {

		self::$raw[] = $style;
	}

	public static function AddLib($lib) {

		self::$lib[] = $lib;
	}
	public static function GetData() {

		$content = '';
		foreach(self::$lib as $lib)
			$content .= "<link href=\"{$lib}\" type=\"text/css\" rel=\"stylesheet\" />\n";

		$content .= "<style type=\"text/css\">\n";
		foreach(self::$raw as $raw)
			$content .= "{$raw};\n";

		$content .= "</style>\n";

		return $content;
	}
}