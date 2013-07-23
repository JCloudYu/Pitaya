<?php

// INFO: Super Global Constants...
define('__ROOT__', $_SERVER['DOCUMENT_ROOT'], TRUE);
(preg_match('/^win|^WIN/', PHP_OS) === 1) ? define('__OS__', 'WIN', TRUE) : define('__OS__', 'UNIX', TRUE);

require_once (__ROOT__.'/config.php');
require_once (__ROOT__.'/cmd.config.php');

srand(time());
$GLOBALS['randomCert'] = md5(rand());

/**
 * An alternative file inclusion call
 * @param string $referencingContext The class to include
 * @param bool $important Specified whether the system will stop if the file doesn't exists
 * @comment This function will naturally limit the inclusion file within the working directory of Server's document root
 */
function using($referencingContext = '', $important = true, $output = false) {

	static $registeredInclusions = array();
	static $_cachedKernelPath = NULL;
	static $_cachedServicePath = NULL;
	if(is_null($_cachedKernelPath)) $_cachedKernelPath = $GLOBALS['kernelPath'];
	if(is_null($_cachedServicePath)) $_cachedServicePath = $GLOBALS['servicePath'];

	if($output === TRUE)
		return preg_replace('/\n|\r/', '<br />', var_export($registeredInclusions, TRUE));

	$tokens = explode('.', $referencingContext);
	$tokens = array_reverse($tokens);

	if(isset($registeredInclusions[($referencingContext)]))
		return $registeredInclusions[($referencingContext)];

	if($tokens[0] == '*')
	{
		array_shift($tokens);
		$tokens = array_reverse($tokens);

		switch($tokens[0])
		{
			case 'kernel':
				array_shift($tokens);
				$completePath = $_cachedKernelPath;
				break;
			case 'service':
				array_shift($tokens);
				if(defined('__WORKING_ROOT__'))
					$completePath = __WORKING_ROOT__;
				else
					$completePath = $_cachedServicePath;
				break;
			default:
				$completePath = __ROOT__;
				break;
		}

		foreach( $tokens as $token)
			$completePath .= "/{$token}";
		$completePath .= '/';

		$dirHandle = file_exists($completePath) ? opendir($completePath) : NULL;

		if($dirHandle === NULL && $important)
			throw(new Exception("Cannot locate package: {$completePath}"));

		if($dirHandle !== NULL)
		while(($entry = readdir($dirHandle)) !== FALSE)
		{
			if($entry == '.' || $entry == '..') continue;
			if(preg_match('/.*php$/', $entry) === 1)
			{
				$givenContainer = substr($referencingContext, 0, -2);
				$validEntry = substr($entry, 0, -4);

				if(isset($registeredInclusions[("$givenContainer.$validEntry")])) continue;

				$targetPath = "$completePath/$entry";

				$registeredInclusions[("$givenContainer.$validEntry")] = TRUE;

				if($important) require($targetPath);
				else include($targetPath);
			}
		}

		$registeredInclusions[($referencingContext)] = $dirHandle !== NULL;
	}
	else
	{
		$tokens = array_reverse($tokens);

		switch($tokens[0])
		{
			case 'kernel':
				array_shift($tokens);
				$completePath = $_cachedKernelPath;
				break;
			case 'service':
				array_shift($tokens);

				if(defined('__WORKING_ROOT__'))
					$completePath = __WORKING_ROOT__;
				else
					$completePath = $_cachedServicePath;
				break;
			default:
				$completePath = __ROOT__;
				break;
		}

		foreach( $tokens as $token)
			$completePath .= "/{$token}";

		$completePath .= '.php';

		if(file_exists($completePath)) $registeredInclusions[($referencingContext)] = TRUE;
		else $registeredInclusions[($referencingContext)] = FALSE;

		if($important) require($completePath);
		else include($completePath);
	}
}

using('kernel.const');
using('kernel.tool.debug.*');
using('kernel.tool.log.*');

function available($referencingContext = '') {
	static $registeredInclusions = array();
	static $_cachedKernelPath = NULL;
	static $_cachedServicePath = NULL;
	if(is_null($_cachedKernelPath)) $_cachedKernelPath = $GLOBALS['kernelPath'];
	if(is_null($_cachedServicePath)) $_cachedServicePath = $GLOBALS['servicePath'];

	if(isset($registeredInclusions[($referencingContext)])) return $registeredInclusions[($referencingContext)];

	$tokens = explode('.', $referencingContext);

	switch($tokens[0])
	{
		case 'kernel':
			array_shift($tokens);
			$completePath = $_cachedKernelPath;
			break;
		case 'service':
			array_shift($tokens);
			if(defined('__WORKING_ROOT__'))
				$completePath = __WORKING_ROOT__;
			else
				$completePath = $_cachedServicePath;
			break;
		default:
			$completePath = __ROOT__;
			break;
	}

	foreach( $tokens as $token)
		$completePath .= "/{$token}";

	$completePath .= '.php';

	$registeredInclusions[($referencingContext)] = file_exists($completePath);

	return $registeredInclusions[($referencingContext)];
}

function acquiring($referencingContext = '', $param = NULL) {

	static $_cachedKernelPath = NULL;
	if(is_null($_cachedKernelPath)) $_cachedKernelPath = $GLOBALS['kernelPath'];

	$tokens = explode('.', $referencingContext);

	$completePath = $tokens[0] == 'kernel' ? __ROOT__ : $_cachedKernelPath;

	foreach( $tokens as $token) $completePath .= "/{$token}";

	$completePath .= '.php';

	require($completePath);
}

function caller() {
	$backtrace = debug_backtrace(0);
	return $backtrace[2]['class'];
}

function encode($appendInfo = NULL, $referenceBase = NULL) {

	static $cacheServer = NULL;
	static $cacheRawRequest = NULL;
	static $baseEncoding = NULL;
	static $mergeDelegate = NULL;

	if(is_null($cacheServer)) $cacheServer = $_SERVER;
	if(is_null($cacheRawRequest)) $cacheRawRequest = preg_replace('/\/+/', '/', preg_replace('/^\/*|\/*$/', '', $_SERVER['REQUEST_URI']));
	if(is_null($baseEncoding))
	{
		$baseEncoding = "#|#{$cacheServer['HTTP_USER_AGENT']}#|#{$cacheServer['REMOTE_ADDR']}#|#{$cacheServer['REMOTE_PORT']}#|#".
						"{$cacheRawRequest}#|#{$cacheServer['REQUEST_TIME']}#|#".uniqid("", TRUE)."#|#";
		$baseEncoding = hash('sha512', $baseEncoding);
	}

	if(is_null($mergeDelegate)) $mergeDelegate = "___{$GLOBALS['randomCert']}__sys_merge";

	if(!is_array($appendInfo))
		$appendInfo = array(rand(), $appendInfo);
	else
		$appendInfo[] = rand();

	$extended = '|';
	foreach($appendInfo as $key => $value) $extended.="$key|$value|";

	$extended = hash('sha512', $extended);

	$base = is_null($referenceBase) ? $baseEncoding : $referenceBase;

	return $mergeDelegate($base, $extended);
}

eval(<<<INVISIBLE
function ___{$GLOBALS['randomCert']}__sys_merge(\$base, \$extended)
{
	\$base = substr(\$base, 0, 64).strrev(substr(\$base, 64));
	\$extended = substr(\$extended, 0, 17).strrev(substr(\$extended, 17));

	\$result = '';

	for(\$i=0; \$i<128; \$i++)
		\$result.=\$base[\$i].\$extended[\$i];

	return \$result;
}

function ___{$GLOBALS['randomCert']}__sys_divide(\$hash)
{
	\$base = '';
	\$extended = '';

	for(\$i=0;\$i<128;\$i++)
	{
		\$base .= \$hash[2 * \$i];
		\$extended .= \$hash[2 * \$i + 1];
	}

	\$base = substr(\$base, 0, 64).strrev(substr(\$base, 64));
	\$extended = substr(\$extended, 0, 17).strrev(substr(\$extended, 17));

	return array('base' => \$base, 'extended' => \$extended);
}
INVISIBLE
);

function divide($sys_hash)
{
	static $divideDelegate = NULL;
	if(is_null($divideDelegate)) $divideDelegate = "___{$GLOBALS['randomCert']}__sys_divide";

	return $divideDelegate($sys_hash);
}

// INFO: Force the encode function to cache the system parameters
$reg = encode('');
divide($reg);

// Trigger the functions to cache the paths
available('');

// Inclusion of the core libraries
using('kernel.basis.PBObject');
using('kernel.basis.*');
using('kernel.core.*');
using('kernel.sys');

SYS::__imprint_constants();

unset($GLOBALS['randomCert']);
unset($GLOBALS['kernelPath']);
unset($GLOBALS['servicePath']);
unset($reg);

// INFO: There's no DEBUG_BACKTRACE_PROVIDE_OBJECT before PHP 5.3.6
if (!defined('DEBUG_BACKTRACE_PROVIDE_OBJECT')) define('DEBUG_BACKTRACE_PROVIDE_OBJECT', TRUE, TRUE);