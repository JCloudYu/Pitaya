<?php

$GLOBALS['invokeTime'] = time();

ini_set('session.use_cookies', '0');

// INFO: Super Global Constants...
(preg_match('/^win|^WIN/', PHP_OS) === 1) ? define('__OS__', 'WIN', TRUE) : define('__OS__', 'UNIX', TRUE);

// INFO: If the document root is assigned by VirtualDocumentRoot, DOCUMENT_ROOT will contain error path... (It's a bug of mod_vhost_alias)
// INFO: So I decide to rewrite the real document root here... since this file is the earliest invoked one!!!
$_SERVER['DOCUMENT_ROOT'] = dirname(dirname($_SERVER["SCRIPT_FILENAME"]));


define('__WEB_ROOT__', $_SERVER['DOCUMENT_ROOT'], TRUE);
define('__ROOT__', __WEB_ROOT__ . ( file_exists(__WEB_ROOT__.'/Pitaya') ? '/Pitaya' : '/pitaya' ), TRUE);


define('ENV_SESSION_NAME',		session_name());
define('ENV_SESSION_LIFETIME',	ini_get('session.gc_maxlifetime'));


define('SYS_COMMAND_ENV', 'CMD', TRUE);
define('SYS_NETWORK_ENV', 'NET', TRUE);

define('__SYS_WORKING_ENV__', (isset($_SERVER['REMOTE_ADDR']) ? SYS_NETWORK_ENV : SYS_COMMAND_ENV), TRUE);
define('EOL', (__SYS_WORKING_ENV__ === SYS_NETWORK_ENV) ? '<br />' : "\n", TRUE);
define('EON', "\n", TRUE);
define('EOB', '<br />', TRUE);


// INFO: Including configuration files
require_once (__ROOT__ . '/cmd.config.php');
require_once (__WEB_ROOT__ . "/config.php");	// ISSUE: We need to verify the configuration data...


srand(time());
$GLOBALS['randomCert'] = md5(rand());


function ____________env_path($token = 'root') {

	static $delegator = NULL;
	if ($delegator === NULL)
	{
		$delegator = call_user_func(function() {

			$_cachedPath = array();

			if (empty($GLOBALS['custPath'])) $GLOBALS['custPath'] = array();
			foreach ($GLOBALS['custPath'] as $identifier => $path)
				$_cachedPath[$identifier] = is_string($path) ? $path : __ROOT__;


			$list = scandir(__ROOT__);
			foreach ($list as $file)
			{
				$absPath = __ROOT__ . "/{$file}";
				if (is_dir($absPath))
					$_cachedPath[strtolower($file)] = $absPath;
			}



			// INFO: service and share are reserved keywords
			if (empty($GLOBALS['servicePath']))
				$GLOBALS['servicePath'] = __WEB_ROOT__ . '/Services';

			if (!is_dir($GLOBALS['servicePath']))
				throw(new Exception("Service root doesn't exist!"));

			$_cachedPath['service'] = "{$GLOBALS['servicePath']}";




			if (empty($GLOBALS['sharePath']))
				$GLOBALS['sharePath'] = __WEB_ROOT__ . '/Share';

			$_cachedPath['share'] = (is_dir($GLOBALS['sharePath'])) ? "{$GLOBALS['sharePath']}" : '';



			$_cachedPath['root'] = __WEB_ROOT__;

			return function($package = 'root') use ($_cachedPath) {
				$pCache = array_key_exists("{$package}", $_cachedPath) ? $_cachedPath[$package] : '';
				return ($package == 'service' && defined('__WORKING_ROOT__')) ? __WORKING_ROOT__ : $pCache;
			};
		});
	}

	return $delegator($token);
}
____________env_path();



/**
 * An alternative file inclusion call
 * @param string $referencingContext The class to include
 * @param bool $important Specified whether the system will stop if the file doesn't exists
 * @comment This function will naturally limit the inclusion file within the working directory of Server's document root
 */
function using($referencingContext = '', $important = true, $output = false) {

	static $registeredInclusions = array();

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
		$completePath = ____________env_path(array_shift($tokens));


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
		$completePath = ____________env_path(array_shift($tokens));

		foreach( $tokens as $token)
			$completePath .= "/{$token}";

		$completePath .= '.php';

		if(file_exists($completePath)) $registeredInclusions[($referencingContext)] = TRUE;
		else $registeredInclusions[($referencingContext)] = FALSE;

		if($important) require($completePath);
		else include($completePath);
	}
}

function package($referencingContext = '', $output = true)
{
	$tokens = explode('.', $referencingContext);
	$tokens = array_reverse($tokens);

	if($tokens[0] == '*')
		throw(new Exception("Star notation is not allowed in function [package]"));
	else
	{
		$tokens = array_reverse($tokens);
		$completePath = ____________env_path(array_shift($tokens));

		foreach( $tokens as $token)
			$completePath .= "/{$token}";

		$completePath .= '.php';

		if ($output) return $completePath;
		elseif(file_exists($completePath)) include($completePath);
	}
}

function available($referencingContext = '', $cache = TRUE) {
	static $registeredInclusions = array();

	if($cache && isset($registeredInclusions[($referencingContext)])) return $registeredInclusions[($referencingContext)];

	$tokens = explode('.', $referencingContext);

	$completePath = ____________env_path(array_shift($tokens));

	foreach( $tokens as $token)
		$completePath .= "/{$token}";
	$completePath .= '.php';

	$result = file_exists($completePath);

	if ($cache)
		$registeredInclusions[($referencingContext)] = $result;

	return $result;
}

function path($referencingContext = '', $appendItem = '') {

	$tokens = explode('.', $referencingContext);
	$completePath = ____________env_path(array_shift($tokens));

	foreach( $tokens as $token)
		$completePath .= "/{$token}";

	$appendItem = trim($appendItem);

	return $completePath . (empty($appendItem) ? '' : "/{$appendItem}");
}

function s_define($name, $value, $sensitive = TRUE, $REPETITIVE_EXCEPTION = FALSE) {
	if (!defined($name))
		define($name, $value, $sensitive);
	else
	if ($REPETITIVE_EXCEPTION)
		throw(new Exception("Constant {$name} has been defined!"));

}

using('kernel.const');
using('kernel.tool.debug.*');
using('kernel.tool.log.*');

package('');


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
	if(is_null($cacheRawRequest)) $cacheRawRequest = preg_replace('/\/+/', '/', preg_replace('/^\/*|\/*$/', '', @$_SERVER['REQUEST_URI']));
	if(is_null($baseEncoding))
	{
		@$baseEncoding = "#|#{$cacheServer['HTTP_USER_AGENT']}#|#{$cacheServer['REMOTE_ADDR']}#|#{$cacheServer['REMOTE_PORT']}#|#".
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
PBRequest::__imprint_constants();

unset($GLOBALS['randomCert']);
unset($GLOBALS['servicePath']);
unset($GLOBALS['sharePath']);
unset($GLOBALS['custPath']);
unset($GLOBALS['invokeTime']);
unset($reg);

// INFO: There's no DEBUG_BACKTRACE_PROVIDE_OBJECT before PHP 5.3.6
if (!defined('DEBUG_BACKTRACE_PROVIDE_OBJECT')) define('DEBUG_BACKTRACE_PROVIDE_OBJECT', TRUE, TRUE);
