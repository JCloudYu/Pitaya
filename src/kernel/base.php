<?php
	$GLOBALS[ 'BOOT_TIME' ] = microtime( TRUE );



	// Detect minimum PHP Version
	if ( PHP_VERSION_ID < 50600 )
		die( "The system requires php 5.6.0 or higher!" );


	if ( defined( 'PITAYA_BASE_CORE_INITIALIZED' ) ) return
	define( 'PITAYA_BASE_CORE_INITIALIZED', TRUE );



	define("PITAYA_VERSION_MAJOR",	 1);
	define("PITAYA_VERSION_MINOR",	 4);
	define("PITAYA_VERSION_BUILD",	 4);
	define("PITAYA_VERSION_PATCH",	 0);
	define("PITAYA_VERSION_ID", PITAYA_VERSION_MAJOR * 10000 + PITAYA_VERSION_MINOR * 100 + PITAYA_VERSION_BUILD );
	define('PITAYA_VERSION_SHORT', PITAYA_VERSION_MAJOR . '.' . PITAYA_VERSION_MINOR);
	define('PITAYA_VERSION', PITAYA_VERSION_MAJOR . '.' . PITAYA_VERSION_MINOR . '.' . PITAYA_VERSION_BUILD);
	define('PITAYA_VERSION_DETAIL', PITAYA_VERSION . '-' . PITAYA_VERSION_PATCH);
	


	// DEPRECATED: The constants will be removed in v2.0.0
	define('SYS_ENV_CLI', 'CMD');
	define('SYS_ENV_NET', 'NET');



	define('EXEC_ENV_CLI',	'CLI');
	define('EXEC_ENV_HTTP', 'HTTP');

	define('EON',	"\n");
	define('EOR',	"\r");
	define('EORN',	"\r\n");
	define('EOB',	'<br>');

	define('LF',	"\n");
	define('CR',	"\r");
	define('CRLF',	"\r\n");
	define('BR',	'<br>');



	// INFO:Some special initializations
	call_user_func(function() {
	
		if ( !defined( '__OS__' ) )
			(strtoupper(substr( PHP_OS, 0, 3 )) === 'WIN') ? define('__OS__', 'WIN', TRUE) : define('__OS__', 'UNIX');

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


	if ( !defined( '__ROOT__' ) )
		define('__ROOT__', realpath( dirname($_SERVER["SCRIPT_FILENAME"]) ));


	if ( php_sapi_name() == "cli" )
	{
		if ( !defined( '__SPACE_ROOT__' ) ) define( '__SPACE_ROOT__', getcwd() );


		define('SYS_WORKING_ENV',	SYS_ENV_CLI); // DEPRECATED: The constants will be removed in v2.0.0

		define('SYS_EXEC_ENV',		EXEC_ENV_CLI);
		define('IS_CLI_ENV',		TRUE);
		
		define('REQUESTING_METHOD',	'');
		define('PITAYA_HOST',		 @"{$GLOBALS['RUNTIME_ENV']['PITAYA_HOST']}");
		define('EOL',				"\n");


		// NOTE: Remove script file path
		array_shift( $_SERVER['argv'] );


		// NOTE: Special intialization
		if ( "{$_SERVER['argv'][0]}" == "-entry" )
		{
			array_shift($_SERVER['argv']);
			$GLOBALS['STANDALONE_EXEC'] = array(
				'script' => "{$_SERVER['argv'][0]}",
				'cwd'	 => __SPACE_ROOT__
			);
			array_shift( $_SERVER['argv'] );

			define( '__STANDALONE_EXEC_MODE__', TRUE);
		}


		$_SERVER['argc'] = count($_SERVER['argv']);
	}
	else
	{
		if ( !defined( '__SPACE_ROOT__' ) ) define( '__SPACE_ROOT__', ($_SERVER['DOCUMENT_ROOT'] = dirname(__ROOT__)) );

		define('SYS_WORKING_ENV',	SYS_ENV_NET); // DEPRECATED: The constants will be removed in v2.0.0

		define('SYS_EXEC_ENV',		EXEC_ENV_HTTP);
		define('IS_CLI_ENV',		FALSE);
		
		define('REQUESTING_METHOD',	strtoupper($_SERVER['REQUEST_METHOD']));
		define('PITAYA_HOST', "{$_SERVER['HTTP_HOST']}");

		define('EOL', '<br />');

		$_SERVER['argv'] = array(); $_SERVER['argc'] = 0;
	}


	define( '__WEB_ROOT__', __SPACE_ROOT__, FALSE );

	// INFO: Change current working environment space root
	chdir( __SPACE_ROOT__ );



		
	define('IS_HTTP_ENV',		!IS_CLI_ENV);
	if ( !defined( '__STANDALONE_EXEC_MODE__' ) )
		define( '__STANDALONE_EXEC_MODE__', FALSE);







	require_once __ROOT__ . '/kernel/api.tool.php';



	// ISSUE: We need to verify the configuration data...
	if ( SYS_EXEC_ENV === EXEC_ENV_CLI )
	{
		define( 'CLI_ENV',	TRUE);
		define( 'NET_ENV',	FALSE);

		if ( file_exists(__SPACE_ROOT__ . "/cli.php") )
		{
			require_once __SPACE_ROOT__ . "/cli.php";
			define( 'CONFIG_MODE', 'CLI' );
		}
	}
	else
	{
		define( 'CLI_ENV',	FALSE);
		define( 'NET_ENV',	TRUE);

		if ( PITAYA_HOST != "" && file_exists( __SPACE_ROOT__ . "/config-" . PITAYA_HOST . ".php" ) )
		{
			require_once __SPACE_ROOT__ . "/config-" . PITAYA_HOST . ".php";
			define( 'CONFIG_MODE', 'HOST' );
		}
	}


	if ( !defined( 'CONFIG_MODE' ) )
	{
		if ( file_exists( __SPACE_ROOT__ . "/config.php" ) )
		{
			require_once __SPACE_ROOT__ . "/config.php";
			define( 'CONFIG_MODE', 'DEFAULT' );
		}
		else
		{
			define( 'CONFIG_MODE', 'NONE' );
		}
	}



	// INFO: Common configurations...
	if ( file_exists( __SPACE_ROOT__ . "/common.php" ) )
		require_once __SPACE_ROOT__ . "/common.php";



	// Calculate the time diff from begining... ( Theoretically... 0 )
	$GLOBALS[ 'BOOT_TIME' ] = (microtime( TRUE ) - $GLOBALS[ 'BOOT_TIME' ])| 0;
	require_once __ROOT__ . '/kernel/env.time.php';



	// INFO: System Core APIs ( using, package, path, available and etc... )
	require_once __ROOT__ . '/kernel/runtime.php';
	require_once __ROOT__ . '/kernel/api.core.php';
	require_once __ROOT__ . '/kernel/api.encrypt.php';



	// INFO: Include configurations according working environment
	require_once __ROOT__ . "/kernel/" . ( (SYS_WORKING_ENV == SYS_ENV_CLI) ? "cli.config.php" : "net.config.php" );
	require_once __ROOT__ . "/kernel/env.const.php";



	// INFO: Runtime Configuration Control
	call_user_func(function(){
		// INFO: Error Reporting Control
		s_define( "PITAYA_SUPPRESS_EXPECTED_WARNINGS", TRUE, TRUE, FALSE );
		error_reporting( PITAYA_SUPPRESS_EXPECTED_WARNINGS ? (E_ALL & ~E_STRICT & ~E_NOTICE) : E_ALL );
		
/*
		set_error_handler(function( $errno, $errStr ){
			if ( !PITAYA_SUPPRESS_EXPECTED_WARNINGS ) return FALSE;
			DEBUG::VarDump(func_get_args());
			return (strpos( $errStr, 'Declaration of' ) === 0);
		}, E_WARNING );
*/
	});



	// INFO: Load system core libraries and prepare system constants
	using( 'kernel.php-extension.*' );
	using( 'kernel.basis.PBObject' );
	using( 'kernel.basis.*' );
	using( 'kernel.core.*' );
	using( 'kernel.sys' );
	

	PBSysKernel::__imprint_constants();
	PBRequest::__imprint_constants();
	PBRuntimeCtrl::__ImprintEnvironment();
	
	require_once __ROOT__ . "/kernel/env.cleanup.php";
