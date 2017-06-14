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
		if ( @"{$_SERVER['argv'][0]}" == "-entry" ) {
			array_shift($_SERVER['argv']);
			$GLOBALS['STANDALONE_EXEC'] = array(
				'script' => "{$_SERVER['argv'][0]}",
				'cwd'	 => ROOT
			);
			array_shift( $_SERVER['argv'] );
			
			define( 'PITAYA_STANDALONE_EXECUTION_MODE', TRUE );
		}
		$_SERVER['argc'] = count($_SERVER['argv']);
		// endregion
	}
	else {
		define('REQUESTING_METHOD',		strtoupper($_SERVER['REQUEST_METHOD']));
		define('PITAYA_HOST',			"{$_SERVER['HTTP_HOST']}");
		define('EOL',					'<br />');

		$_SERVER['argv'] = []; $_SERVER['argc'] = 0;
	}
	
	if ( !defined( 'PITAYA_STANDALONE_EXECUTION_MODE' ) ) define( 'PITAYA_STANDALONE_EXECUTION_MODE', FALSE );
	
	chdir( ROOT ); // INFO: Change working directory to space root
	// endregion
	// region [ Parse Command Line System Arguments ]
	call_user_func(function() {
		$conf = array();
		$argv = $_SERVER['argv'];

		$RUN = TRUE;
		do
		{
			switch ( @"{$argv[0]}" )
			{
				case "--timezone":
				case "-tz":
					array_shift($argv);
					$TZ = @array_shift($argv);

					if ( empty($TZ) )
					{
						error_log("-tz option must be followed with timezone identifier!");
						Termination::WITH_STATUS(Termination::STATUS_INCORRECT_USAGE);
					}

					$conf['TZ'] = $TZ;
					break;

				default:
					$RUN = $RUN && FALSE;
			}
		}
		while( $RUN );



		$_SERVER['argv'] = $GLOBALS['RUNTIME_ARGV'] = $argv;
		$_SERVER['argc'] = $GLOBALS['RUNTIME_ARGC'] = count( $GLOBALS['RUNTIME_ARGV'] );

		$GLOBALS['RUNTIME_CONF'] = $conf;
	});
	// endregion

	



	// DEPRECATED: __WEB_ROOT__ constant will be removed in 2.5.0
	define( '__WEB_ROOT__', ROOT, FALSE ); 