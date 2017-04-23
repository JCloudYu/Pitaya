<?php
	// INFO: Runtime configurations
	call_user_func(function(){
		// INFO: Error Reporting Control
		s_define( "PITAYA_SUPPRESS_EXPECTED_WARNINGS", TRUE, TRUE, FALSE );
		error_reporting( PITAYA_SUPPRESS_EXPECTED_WARNINGS ? (E_ALL & ~E_STRICT & ~E_NOTICE) : E_ALL );
		

		set_error_handler(function( $errno, $errStr ){
			if ( !PITAYA_SUPPRESS_EXPECTED_WARNINGS ) return FALSE;
			return ( substr( $errStr, 0, 14 ) === 'Declaration of' );
		}, E_WARNING );
	});

	s_define( '__DEBUG_CONSOLE_WIDTH__',		200, TRUE );

	// INFO: Parse System Arguments
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

	// INFO: Setting Environmental Constants
	call_user_func(function(){

		if ( !defined('__DEBUG_MODE__') )
		{
			$ENV_DEBUG_MODE = IS_POSITIVE( @$GLOBALS['RUNTIME_ENV']['PITAYA_EXEC_DEBUG_MODE'] );
			define( '__DEBUG_MODE__', $ENV_DEBUG_MODE || FALSE );
		}




		if ( !defined('__THROW_EXCEPTION__') )
		{
			$ENV_THROW_EXCEPTION = IS_POSITIVE( @$GLOBALS['RUNTIME_ENV']['PITAYA_EXEC_THROW_EXCEPTION'] );
			define( '__THROW_EXCEPTION__', $ENV_THROW_EXCEPTION || FALSE );
		}


		if ( !defined('__LOG_EXCEPTION__') ) {
			define( '__LOG_EXCEPTION__', TRUE );
		}
	});
