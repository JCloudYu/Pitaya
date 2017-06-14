<?php
	// region [ Environmental Initialization ]
	if ( IS_CLI_ENV ) {
		define( 'REQUESTING_METHOD',	'' );
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