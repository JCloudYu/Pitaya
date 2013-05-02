<?php
/*
 * File: template.php
 * Created by Cloud.
 * DateTime: 13/4/28 PM4:48
 */

// INFO: Definitions to the available template paths
define('TEMPLATE_PATH', __WORKING_ROOT__."/templates");

// ISSUE: The following search path assignment should be changed
// ISSUE: Since it has too much connection between WebPage module

define('SYSTEM_PATH', __ROOT__."/modules/WebPage/templates");

define('PAGE_PATH', TEMPLATE_PATH."/pages");
define('COMPONENT_PATH', TEMPLATE_PATH."/components");

define('SYS_PAGE_PATH', SYSTEM_PATH."/pages");
define('SYS_COMPONENT_PATH', SYSTEM_PATH."/components");


function tpl($tplName, $variables = array(), $retOpt = FALSE, $exception = TRUE) {

	static $SelectOrder = NULL;
	if($SelectOrder === NULL) $SelectOrder = array(PAGE_PATH, COMPONENT_PATH, SYS_PAGE_PATH, SYS_COMPONENT_PATH);

	foreach($SelectOrder as $path)
	{
		$filePath = "{$path}/{$tplName}.tpl";
		if(file_exists($filePath))
		{
			if($retOpt)
			{
				ob_start();
				__applyTpl($filePath, $variables);
				return ob_get_clean();
			}
			else
			{
				__applyTpl($filePath, $variables);
				return '';
			}
		}
	}

	if($exception)
		throw(new Exception("Cannot locate template file: {$tplName}.tpl"));
	else
		return '';
}

function layout($layoutName, $exception = TRUE) {

	static $SelectOrder = NULL;
	if($SelectOrder === NULL) $SelectOrder = array(__WORKING_ROOT__."/templates/layout", __ROOT__."/modules/WebPage/templates/layout");

	foreach($SelectOrder as $path)
	{
		$filePath = "{$path}/{$layoutName}.format.json";
		if(file_exists($filePath))
			return json_decode(file_get_contents($filePath), TRUE);
	}

	if($exception)
		throw(new Exception("Cannot locate layout file: {$layoutName}.format.json"));
	else
		return NULL;
}

// INFO: The template applying scheme must be as clearer as possible to make the symbol table simple
// INFO: So the template applying mechanism along with variable symbol extraction is isolated from tpl function
function __applyTpl($____315261__escaping_special_var_tplFile__162513____,
					$____315261__escaping_special_var_vars__162513____)
{

	if(is_array($____315261__escaping_special_var_vars__162513____))
		extract($____315261__escaping_special_var_vars__162513____);

	require($____315261__escaping_special_var_tplFile__162513____);
}