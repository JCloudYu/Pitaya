<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Cloud
 * Date: 13/1/19
 * Time: PM1:52
 * To change this template use File | Settings | File Templates.
 */

// Constant path declaration
define(__ROOT__, $_SERVER['DOCUMENT_ROOT']);

/**
 * An alternative file inclusion call
 * @param string $referencingContext The class to include
 * @param bool $important Specified whether the system will stop if the file doesn't exists
 * @comment This function will limit the inclusion file within the working directory
 */
function using($referencingContext = '', $important = true)
{
	$tokens = explode('.', $referencingContext);
	$completePath = __ROOT__;

	foreach( $tokens as $token)
		$completePath .= "/{$token}";

	$completePath .= '.php';

	if($important) require_once($completePath);
	else include_once($completePath);
}

// Inclusion of the core libraries
using('sys.core.ObjectCore');
using('sys.core.NameSpaceCore');