<?php
	s_define( 'IS_CLI_ENV', php_sapi_name() === "cli", TRUE, TRUE );
	s_define( 'IS_HTTP_ENV', !IS_CLI_ENV, TRUE, TRUE );
	s_define( 'DEBUG_BACKTRACE_ENABLED', function_exists( "debug_backtrace" ), TRUE, TRUE );



	s_define("PITAYA_VERSION_MAJOR", 2, TRUE, TRUE);
	s_define("PITAYA_VERSION_MINOR", 3, TRUE, TRUE);
	s_define("PITAYA_VERSION_BUILD", 0, TRUE, TRUE);
	s_define("PITAYA_VERSION_PATCH", 0, TRUE, TRUE);
	s_define("PITAYA_VERSION_ID",		PITAYA_VERSION_MAJOR * 10000 + PITAYA_VERSION_MINOR * 100 + PITAYA_VERSION_BUILD, TRUE, TRUE);
	s_define('PITAYA_VERSION_SHORT',	PITAYA_VERSION_MAJOR . '.' . PITAYA_VERSION_MINOR, TRUE, TRUE);
	s_define('PITAYA_VERSION',		PITAYA_VERSION_MAJOR . '.' . PITAYA_VERSION_MINOR . '.' . PITAYA_VERSION_BUILD, TRUE, TRUE);
	s_define('PITAYA_VERSION_DETAIL', PITAYA_VERSION . '-' . PITAYA_VERSION_PATCH, TRUE, TRUE);
	
	

	s_define( 'PITAYA_BOOT_TIME', PITAYA_METRIC_BOOT_TIME|0, TRUE, TRUE );
	s_define( 'PITAYA_ZONE_DIFF', date( 'Z' )|0, TRUE, TRUE );
	s_define( 'SYS_TIMEZONE', 'UTC', TRUE, TRUE );
	date_default_timezone_set( SYS_TIMEZONE );
	
	s_define( 'REQUESTING_METHOD', strtoupper(@"{$_SERVER['REQUEST_METHOD']}"), TRUE, TRUE );
	
	
	
	
	
	
	s_define( 'CLEAR_SCREEN', chr(27)."[2J".chr(27)."[;H" );
	s_define( 'LF',	"\n" );
	s_define( 'CR', "\r" );
	s_define( 'CRLF', "\r\n" );
	s_define( 'BR',	'<br>' );
	s_define( 'EOL', IS_CLI_ENV ? LF : BR );