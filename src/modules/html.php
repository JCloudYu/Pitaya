<?php
/*
 * File: html.php
 * Created by Cloud.
 * DateTime: 13/4/20 PM11:10
 */

using('kernel.core.PBModule');
using('sys.tool.web.*');

class html extends PBModule
{
	public function __construct() {

	}

	public function exec($param) {

		// INFO: Extract required information for HTML.tpl
		$tpl = $param['pageTpl']; unset($param['pageTpl']);

		// INFO: Prepare variables that are directly sent to WebPage
		$page = array();
		foreach($param as $key => $value) $page[$key] = $value;

		$vars = array('meta' 		=> META::GetData(),
					  'css' 		=> CSS::GetData(),
					  'preJS' 		=> JS::GetPreData(),
					  'postJS' 		=> JS::GetPostData(),
					  'pageTpl'		=> $tpl,
					  'page' 		=> $page);

		tpl("html", $vars);
	}

	public static function render($item, $retOpt = FALSE) {

		@$template = $item['template'];
		unset($item['template']);

		@$content = $item['content'];
		unset($item['content']);

		$contentStr = '';

		if(is_array($content))
		{
			foreach($content as $subItem)
				$contentStr .= self::render($subItem, TRUE);
		}
		else
		if($content !== NULL)
		{
			// INFO: Force the $content into a string
			$contentStr .= $content;
		}

		$vars = array('content' => $contentStr);
		foreach($item as $key => $value) $vars[$key] = $value;

		return tpl($template, $vars, $retOpt);
	}
}