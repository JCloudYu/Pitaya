<?php
/*
 * File: js.php
 * Created by Cloud.
 * DateTime: 13/4/28 PM1:55
 */

class JS
{
	private static $pre  = array('js' => array(), 'lib' => array());
	private static $post = array('js' => array(), 'lib' => array());

	public static function AddScript($content, $previous = FALSE) {

		if($previous) 	$base = &self::$pre['js'];
		else			$base = &self::$post['js'];

		// INFO: JS Syntax Checker is recommendedly required
		$base[] = $content;
	}

	public static function AddLib($lib, $previous = FALSE) {

		if($previous) 	$base = &self::$pre['lib'];
		else			$base = &self::$post['lib'];

		// INFO: JS Syntax Checker is recommendedly required
		$base[] = $lib;
	}

	public static function GetPreData() {

		$content = '';
		self::$pre['lib'] = array_unique(self::$pre['lib']);
		foreach(self::$pre['lib'] as $lib)
			$content .= "<script type=\"text/javascript\" src=\"{$lib}\"></script>\n";

		foreach(self::$pre['js'] as $seg)
			$content .= <<<HTML
				<script type="text/javascript" language="javascript">
					{$seg}
				</script>\n
HTML;

		return $content;
	}

	public static function GetPostData() {

		$content = '';
		self::$post['lib'] = array_unique(self::$post['lib']);
		foreach(self::$post['lib'] as $lib)
			$content .= "<script type=\"text/javascript\" src=\"{$lib}\"></script>\n";

		foreach(self::$post['js'] as $seg)
			$content .= <<<HTML
				<script type="text/javascript" language="javascript">
					{$seg}
				</script>\n
HTML;

		return $content;
	}
}