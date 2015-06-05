<?php
	define("PITAYA_VERSION_MAJOR",	"1");
	define("PITAYA_VERSION_MINOR",	"3");
	define("PITAYA_VERSION_BUILD",	"7");
	define("PITAYA_VERSION_PATCH",	"0");
	define('PITAYA_VERSION', PITAYA_VERSION_MAJOR . '.' . PITAYA_VERSION_MINOR . '.' . PITAYA_VERSION_BUILD);
	define('PITAYA_VERSION_DETAIL', PITAYA_VERSION . '-' . PITAYA_VERSION_PATCH);



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



	// INFO:Some special initializations
	call_user_func(function() {

		// Detect operating system information
		(preg_match('/^win|^WIN/', PHP_OS) === 1) ? define('__OS__', 'WIN', TRUE) : define('__OS__', 'UNIX', TRUE);

		$GLOBALS['RUNTIME_ENV'] = array();

		$env = shell_exec( ( __OS__ == "WIN" ) ? 'set' : 'printenv');
		$env = preg_split("/(\n)+|(\r\n)+/", $env);
		foreach ( $env as $envStatement )
		{
			if ( ($pos = strpos($envStatement, "=")) === FALSE ) continue;

			$var	 = substr( $envStatement, 0, $pos );
			$content = substr( $envStatement, $pos + 1 );
			$GLOBALS['RUNTIME_ENV'][$var] = $content;
		}
	});


	// INFO: Execution environment
	if ( php_sapi_name() == "cli" )
	{
		define('SYS_WORKING_ENV',	SYS_ENV_CLI, TRUE); // DEPRECATED: The constants will be removed in v1.4.0

		define('__ROOT__',			realpath( getcwd() ), TRUE);
		define('SYS_EXEC_ENV',		EXEC_ENV_CLI, TRUE);
		define('PITAYA_HOST',		 @"{$GLOBALS['RUNTIME_ENV']['PITAYA_HOST']}", TRUE);
		define('EOL',				"\n", TRUE);



		// NOTE: Remove script file
		array_shift( $_SERVER['argv'] );
		$_SERVER['argc'] = count($_SERVER['argv']);
	}
	else
	{
		define('__ROOT__', realpath( dirname($_SERVER["SCRIPT_FILENAME"]) ), TRUE);
		define('SYS_EXEC_ENV',	  EXEC_ENV_HTTP, TRUE);
		define('PITAYA_HOST', "{$_SERVER['HTTP_HOST']}", TRUE);

		define('SYS_WORKING_ENV', SYS_ENV_NET, TRUE); // DEPRECATED: The constants will be removed in v1.4.0

		define('EOL', '<br />', TRUE);

		$_SERVER['argv'] = array(); $_SERVER['argc'] = 0;
	}

	define('__WEB_ROOT__',	($_SERVER['DOCUMENT_ROOT'] = dirname(__ROOT__)), TRUE);
	chdir( __WEB_ROOT__ );






	// ISSUE: We need to verify the configuration data...
	if ( SYS_EXEC_ENV === EXEC_ENV_CLI )
	{
		define( 'CLI_ENV',	TRUE,	TRUE );
		define( 'NET_ENV',	FALSE,	TRUE );

		if ( file_exists(__WEB_ROOT__ . "/cli.php") )
		{
			require_once __WEB_ROOT__ . "/cli.php";
			define( 'CONFIG_MODE', 'CLI' );
		}
	}
	else
	{
		define( 'CLI_ENV',	FALSE,	TRUE );
		define( 'NET_ENV',	TRUE,	TRUE );

		if ( PITAYA_HOST != "" && file_exists( __WEB_ROOT__ . "/config-" . PITAYA_HOST . ".php" ) )
		{
			require_once __WEB_ROOT__ . "/config-" . PITAYA_HOST . ".php";
			define( 'CONFIG_MODE', 'HOST' );
		}
	}


	if ( !defined( 'CONFIG_MODE' ) )
	{
		if ( file_exists( __WEB_ROOT__ . "/config.php" ) )
		{
			require_once __WEB_ROOT__ . "/config.php";
			define( 'CONFIG_MODE', 'DEFAULT' );
		}
		else
		{
			define( 'CONFIG_MODE', 'NONE' );
		}
	}



	// INFO: Common configurations...
	if ( file_exists( __WEB_ROOT__ . "/common.php" ) )
		require_once __WEB_ROOT__ . "/common.php";





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
	PBRunTime::__ImprintEnvironment();

	// INFO: Clean up everything
	unset($GLOBALS['randomCert']);
	unset($GLOBALS['servicePath']);
	unset($GLOBALS['sharePath']);
	unset($GLOBALS['dataPath']);
	unset($GLOBALS['extPath']);
	unset($GLOBALS['invokeTime']);
	unset($GLOBALS['RUNTIME_ENV']);
	unset($GLOBALS['RUNTIME_CONF']);
	unset($GLOBALS['RUNTIME_ARGC']);
	unset($GLOBALS['RUNTIME_ARGV']);


	// INFO: There's no DEBUG_BACKTRACE_PROVIDE_OBJECT before PHP 5.3.6
	s_define('DEBUG_BACKTRACE_PROVIDE_OBJECT', TRUE, TRUE);
