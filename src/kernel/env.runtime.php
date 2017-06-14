<?php
	call_user_func(function(){
		if ( PITAYA_STANDALONE_EXECUTION_MODE && is_writable( path( $logPackage = 'working' )) )
			s_define( "DEFAULT_SYSTEM_LOG_PACKAGE", $logPackage = "{$logPackage}.plog", TRUE );
		else
		if ( is_writable( path( $logPackage = 'data' )) )
			s_define( "DEFAULT_SYSTEM_LOG_PACKAGE", $logPackage = "{$logPackage}.log", TRUE );
		else
		if ( is_writable( path( $logPackage = 'root' )) )
			s_define( "DEFAULT_SYSTEM_LOG_PACKAGE", $logPackage, TRUE );
		


		if ( defined( 'DEFAULT_SYSTEM_LOG_PACKAGE' ) )
			s_define( "DEFAULT_SYSTEM_LOG_DIR",	path( DEFAULT_SYSTEM_LOG_PACKAGE ), TRUE );
		else
			s_define( "DEFAULT_SYSTEM_LOG_DIR", sys_get_temp_dir(), TRUE );
	});

	// INFO: Runtime configurations
	call_user_func(function(){
		// INFO: Error Reporting Control
		s_define( "PITAYA_SUPPRESS_EXPECTED_WARNINGS", FALSE, TRUE, FALSE );
		error_reporting( PITAYA_SUPPRESS_EXPECTED_WARNINGS ? (E_ALL & ~E_STRICT & ~E_NOTICE) : E_ALL );
		

		set_error_handler(function( $errno, $errStr ){
			if ( !PITAYA_SUPPRESS_EXPECTED_WARNINGS ) return FALSE;
			return ( substr( $errStr, 0, 14 ) === 'Declaration of' );
		}, E_WARNING );
	});

	s_define( '__DEBUG_CONSOLE_WIDTH__', 350, TRUE );

	// INFO: Setting Environmental Constants
	call_user_func(function() {
		s_define( '__DEBUG_MODE__', FALSE );
		s_define( '__THROW_EXCEPTION__', TRUE );
		s_define( '__LOG_EXCEPTION__', TRUE );
		s_define( 'FORCE_LOG_POSITION', FALSE );
	});
