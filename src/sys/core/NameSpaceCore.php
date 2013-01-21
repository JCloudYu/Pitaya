<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Cloud
 * Date: 13/1/19
 * Time: PM1:38
 */

require_once 'ObjectCore.php';

//	 We should provide some methods to get constants or static variable using string accessor such as...
//	 $Class = SPACE("TEST_NAME_SPACE")->NSClass("TEST_CLASS");
//	 $Val = $Class::TEST_CONSTANT;


class NameSpaceCore extends ObjectCore
{
	private static $namespaces = array();
	public static function SPACE($namespace)
	{
		if(!isset(self::$namespaces[$namespace])) self::$namespaces[$namespace] = new $namespace();
		return self::$namespaces[$namespace];
	}


	public function NSClass($targetName)
	{
		$targetName = $this->ClassPrefix.$targetName;
		return $targetName;
	}

	// This function of naspace accessor is disabled for unsafed reverse reference
	public function &NSVariable($targetName)
	{
		return $this->$targetName;
	}

	public function NSFunction($targetName)
	{
		$targetName = $this->ClassPrefix.$targetName;
		return $targetName;
	}
}

function SPACE($namespace)
{
	return NameSpaceCore::SPACE($namespace);
}