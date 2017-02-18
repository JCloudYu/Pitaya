<?php
	// region [ Constant Definitions ]
	define( 'EON',	"\n" );
	define( 'EOR',	"\r" );
	define( 'EORN',	"\r\n" );
	define( 'EOB',	'<br>' );
	
	define( 'LF',	"\n" );
	define( 'CR',	"\r" );
	define( 'CRLF',	"\r\n" );
	define( 'BR',	'<br>' );
	define( 'EXEC_ENV_CLI',	 'CLI' );
	define( 'EXEC_ENV_HTTP', 'HTTP' );
	
	if ( !defined( 'IS_WIN_ENV' ) ) define( 'IS_WIN_ENV', (strtoupper(substr( PHP_OS, 0, 3 )) === 'WIN') );
	define( 'IS_CLI_ENV', php_sapi_name() === "cli" );
	define( 'IS_HTTP_ENV', !IS_CLI_ENV );
	define( 'SYS_EXEC_ENV', IS_CLI_ENV ? EXEC_ENV_CLI : EXEC_ENV_HTTP );
	// endregion
	// region [ Environmental Initialization ]
	call_user_func(function() {
		$GLOBALS[ 'RUNTIME_ENV' ] = [];
		$env = preg_split("/(\n)+|(\r\n)+/", shell_exec( IS_WIN_ENV ? 'set' : 'printenv') );
		foreach ( $env as $envStatement ) {
			if ( ($pos = strpos($envStatement, "=")) === FALSE ) continue;
			
			$var	 = substr( $envStatement, 0, $pos );
			$content = substr( $envStatement, $pos + 1 );
			$GLOBALS['RUNTIME_ENV'][$var] = $content;
		}
	});
	
	if ( IS_CLI_ENV ) {
		define( 'REQUESTING_METHOD',	'' );
		define( 'PITAYA_HOST',			@"{$GLOBALS['RUNTIME_ENV']['PITAYA_HOST']}" );
		define( 'EOL',					"\n" );
		
		
		// region [ CLI Initialization ]
		array_shift( $_SERVER['argv'] ); // INFO: Remove script file path
		if ( "{$_SERVER['argv'][0]}" == "-entry" ) {
			array_shift($_SERVER['argv']);
			$GLOBALS['STANDALONE_EXEC'] = array(
				'script' => "{$_SERVER['argv'][0]}",
				'cwd'	 => ROOT
			);
			array_shift( $_SERVER['argv'] );

			define( '__STANDALONE_EXEC_MODE__', TRUE );
		}
		$_SERVER['argc'] = count($_SERVER['argv']);
		// endregion
	}
	else {
		define('REQUESTING_METHOD',		strtoupper($_SERVER['REQUEST_METHOD']));
		define('PITAYA_HOST',			"{$_SERVER['HTTP_HOST']}");
		define('EOL',					'<br />');
		define( '__STANDALONE_EXEC_MODE__', FALSE );

		$_SERVER['argv'] = []; $_SERVER['argc'] = 0;
	}
	// endregion
	
	// INFO: Change current working environment space root
	chdir( ROOT );



	// DEPRECATED: __WEB_ROOT__ constant will be removed in 2.5.0
	define( '__WEB_ROOT__', ROOT, FALSE ); 

	
