<?php
	error_reporting( E_ALL & ~E_NOTICE );

	if ( !defined( 'IS_WIN_ENV' ) )  {
		define( 'IS_WIN_ENV', (strtoupper(substr( PHP_OS, 0, 3 )) === 'WIN') );
	}
	
	define( 'IS_CLI_ENV', php_sapi_name() === "cli" );
	define( 'IS_HTTP_ENV', !IS_CLI_ENV );

	if ( IS_CLI_ENV ) {
		if ( !defined( 'ROOT' ) ) define( "ROOT", realpath(getcwd()) );
		if ( !defined( 'PITAYA_ROOT' ) ) define( 'PITAYA_ROOT', realpath(__DIR__ . '/../') );
	}
