<?php
	s_define("PITAYA_VERSION_MAJOR", 2, TRUE, TRUE);
	s_define("PITAYA_VERSION_MINOR", 3, TRUE, TRUE);
	s_define("PITAYA_VERSION_BUILD", 0, TRUE, TRUE);
	s_define("PITAYA_VERSION_PATCH", 0, TRUE, TRUE);
	
	s_define("PITAYA_VERSION_ID",		PITAYA_VERSION_MAJOR * 10000 + PITAYA_VERSION_MINOR * 100 + PITAYA_VERSION_BUILD, TRUE, TRUE);
	s_define('PITAYA_VERSION_SHORT',	PITAYA_VERSION_MAJOR . '.' . PITAYA_VERSION_MINOR, TRUE, TRUE);
	s_define('PITAYA_VERSION',		PITAYA_VERSION_MAJOR . '.' . PITAYA_VERSION_MINOR . '.' . PITAYA_VERSION_BUILD, TRUE, TRUE);
	s_define('PITAYA_VERSION_DETAIL', PITAYA_VERSION . '-' . PITAYA_VERSION_PATCH, TRUE, TRUE);



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
	
	

	s_define( 'PITAYA_BOOT_TIME', PITAYA_METRIC_BOOT_TIME|0, TRUE, TRUE );
	s_define( 'PITAYA_ZONE_DIFF', date( 'Z' )|0, TRUE, TRUE );
	s_define( 'SYS_TIMEZONE', 'UTC', TRUE, TRUE );
	date_default_timezone_set( SYS_TIMEZONE );
	
	s_define( 'CLEAR_SCREEN', chr(27)."[2J".chr(27)."[;H", TRUE );