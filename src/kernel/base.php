<?php
	$GLOBALS['invokeTime'] = $_SERVER['REQUEST_TIME'];


	// INFO: Environmental independent constants
	define('SYS_ENV_CLI', 'CMD', TRUE);
	define('SYS_ENV_NET', 'NET', TRUE);

	define('EON',	"\n",	TRUE);
	define('EOR',	"\r",	TRUE);
	define('EORN',	"\r\n",	TRUE);
	define('EOB',	'<br>',	TRUE);

	define('LF',	"\n",	TRUE);
	define('CR',	"\r",	TRUE);
	define('CRLF',	"\r\n",	TRUE);
	define('BR',	'<br>',	TRUE);



	// INFO: Execution environment
	if ( isset($_SERVER['SHELL']) )
	{
		array_shift($_SERVER['argv']);
		$_SERVER['argc'] = count($_SERVER['argv']);

		define('__ROOT__', getcwd(), TRUE);
		define('__SYS_WORKING_ENV__', SYS_ENV_CLI, TRUE);
		define('EOL', "\n", TRUE);
	}
	else
	{
		define('__ROOT__', dirname($_SERVER["SCRIPT_FILENAME"]), TRUE);
		define('__SYS_WORKING_ENV__', SYS_ENV_NET, TRUE);
		define('EOL', '<br />', TRUE);
	}

	define('__WEB_ROOT__',	($_SERVER['DOCUMENT_ROOT'] = dirname(__ROOT__)), TRUE);



	// INFO: Working operating system
	(preg_match('/^win|^WIN/', PHP_OS) === 1) ? define('__OS__', 'WIN', TRUE) : define('__OS__', 'UNIX', TRUE);

	if (__OS__ === 'WIN')
	{
		define('CMD_MOVE', 'move', TRUE);
		define('CMD_COPY', 'copy', TRUE);
	}
	else
	{
		define('CMD_MOVE', 'mv', TRUE);
		define('CMD_COPY', 'cp', TRUE);
	}



	// INFO: System Core APIs ( using, package, path, available and etc... )
	require_once __ROOT__ . '/kernel/api.core.php';
	require_once __ROOT__ . '/kernel/api.encrypt.php';



	// INFO: Including configuration files
	$ENV_CONF_FILE = __WEB_ROOT__ . "/config.php";
	if ( file_exists($ENV_CONF_FILE) )
		require_once $ENV_CONF_FILE; // ISSUE: We need to verify the configuration data...
	unset($ENV_CONF_FILE);



	s_define("__DEBUG_MODE__",		FALSE,	TRUE);
	s_define("__LOG_EXCEPTION__",	TRUE,	TRUE);
	s_define("__THROW_EXCEPTION__", FALSE,	TRUE);

	s_define('__DEFAULT_SERVICE_DEFINED__', defined('__DEFAULT_SERVICE__'), TRUE);
	s_define('__DEFAULT_SERVICE__', 		'index', 						TRUE);



	// INFO: Include configurations according working environment
	require_once __ROOT__ . "/kernel/" . ( (__SYS_WORKING_ENV__ == SYS_ENV_CLI) ? "cli.config.php" : "net.config.php" );



	// INFO: Load system core libraries and prepare system constants
	using('kernel.tool.debug.*');
	using('kernel.basis.PBObject');
	using('kernel.basis.*');
	using('kernel.core.*');
	using('kernel.sys');

	SYS::__imprint_constants();
	PBRequest::__imprint_constants();

	// INFO: Clean up everything
	unset($GLOBALS['randomCert']);
	unset($GLOBALS['servicePath']);
	unset($GLOBALS['sharePath']);
	unset($GLOBALS['custPath']);
	unset($GLOBALS['invokeTime']);



	// INFO: There's no DEBUG_BACKTRACE_PROVIDE_OBJECT before PHP 5.3.6
	s_define('DEBUG_BACKTRACE_PROVIDE_OBJECT', TRUE, TRUE);
