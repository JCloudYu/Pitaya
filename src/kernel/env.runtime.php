<?php
	call_user_func(function(){
		if ( PITAYA_STANDALONE_EXECUTION_MODE && is_writable( path( $logPackage = 'working' )) ) {
			s_define( "DEFAULT_SYSTEM_LOG_PACKAGE", $logPackage = "{$logPackage}.plog", TRUE );
		}
		else
		if ( is_writable( path( $logPackage = 'data' )) ) {
			s_define( "DEFAULT_SYSTEM_LOG_PACKAGE", $logPackage = "{$logPackage}.log", TRUE );
		}
		else
		if ( is_writable( path( $logPackage = 'root' )) ) {
			s_define( "DEFAULT_SYSTEM_LOG_PACKAGE", $logPackage, TRUE );
		}
		


		if ( defined( 'DEFAULT_SYSTEM_LOG_PACKAGE' ) ) {
			s_define( "DEFAULT_SYSTEM_LOG_DIR",	path( DEFAULT_SYSTEM_LOG_PACKAGE ), TRUE );
		}
		else {
			s_define( "DEFAULT_SYSTEM_LOG_DIR", sys_get_temp_dir(), TRUE );
		}
	});

	s_define( 'DEBUG_CONSOLE_WIDTH', 350 );
	s_define( 'DEBUG_MODE', FALSE );
	s_define( 'THROWN_EXCEPTIONS', TRUE );
	s_define( 'LOG_EXCEPTIONS', TRUE );
