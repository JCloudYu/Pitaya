<?php
	$GLOBALS['invokeTime'] = $_SERVER['REQUEST_TIME'];


	// DEPRECATED: The constants will be removed in v1.4.0
	define('SYS_ENV_CLI', 'CMD', TRUE);
	define('SYS_ENV_NET', 'NET', TRUE);



	define('EXEC_ENV_CLI',	'CLI', TRUE);
	define('EXEC_ENV_HTTP', 'HTTP', TRUE);

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
		define('SYS_EXEC_ENV',	  EXEC_ENV_CLI, TRUE);
		define('PITAYA_HOST', isset($_ENV['PITAYA_HOST']) ? "{$_ENV['PITAYA_HOST']}" : "", TRUE);

		define('SYS_WORKING_ENV', SYS_ENV_CLI, TRUE); // DEPRECATED: The constants will be removed in v1.4.0

		define('EOL', "\n", TRUE);
	}
	else
	{
		define('__ROOT__', dirname($_SERVER["SCRIPT_FILENAME"]), TRUE);
		define('SYS_EXEC_ENV',	  EXEC_ENV_HTTP, TRUE);
		define('PITAYA_HOST', "{$_SERVER['HOST']}", TRUE);

		define('SYS_WORKING_ENV', SYS_ENV_NET, TRUE); // DEPRECATED: The constants will be removed in v1.4.0

		define('EOL', '<br />', TRUE);
	}

	define('__WEB_ROOT__',	($_SERVER['DOCUMENT_ROOT'] = dirname(__ROOT__)), TRUE);



	// INFO: Read system working environmental configurations
	if ( PITAYA_HOST != "" )
	{
		if ( file_exists(__WEB_ROOT__ . "/config-" . PITAYA_HOST . ".php") )
			require_once __WEB_ROOT__ . "/config-" . PITAYA_HOST . ".php";  // ISSUE: We need to verify the configuration data...
	}
	else
	if ( file_exists(__WEB_ROOT__ . "/config.php") )
		require_once __WEB_ROOT__ . "/config.php"; // ISSUE: We need to verify the configuration data...



	if ( SYS_EXEC_ENV === EXEC_ENV_CLI && file_exists(__WEB_ROOT__ . "/cli.php") )
		require_once __WEB_ROOT__ . "/cli.php";  // ISSUE: We need to verify the configuration data...



	// INFO: System Core APIs ( using, package, path, available and etc... )
	require_once __ROOT__ . '/kernel/runtime.php';
	require_once __ROOT__ . '/kernel/api.core.php';
	require_once __ROOT__ . '/kernel/api.encrypt.php';



	// INFO: Include configurations according working environment
	require_once __ROOT__ . "/kernel/" . ( (SYS_WORKING_ENV == SYS_ENV_CLI) ? "cli.config.php" : "net.config.php" );



	// INFO: Load system core libraries and prepare system constants
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
	unset($GLOBALS['dataPath']);
	unset($GLOBALS['extPath']);
	unset($GLOBALS['invokeTime']);



	// INFO: There's no DEBUG_BACKTRACE_PROVIDE_OBJECT before PHP 5.3.6
	s_define('DEBUG_BACKTRACE_PROVIDE_OBJECT', TRUE, TRUE);
