<?php
/*
 * File: PBNameSpace.php
 * Created by JCloudYu.
 * DateTime: 13/2/9 PM3:45
 */

//	 We should provide some methods to get constants or static variable using string accessor such as...
//	 $Class = SPACE("TEST_NAME_SPACE")->NSClass("TEST_CLASS");
//	 $Val = $Class::TEST_CONSTANT;

class PBNameSpace extends PBObject
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
	return PBNameSpace::SPACE($namespace);
}