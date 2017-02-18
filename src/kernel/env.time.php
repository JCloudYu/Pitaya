<?php
	// INFO: Set timezone
	call_user_func(function() {
		$result = FALSE;


		if ( defined('__SYSTEM_TIMEZONE__') )
			$result = date_default_timezone_set( __SYSTEM_TIMEZONE__ );
		else
		if ( isset( $GLOBALS['RUNTIME_CONF']['TZ'] ) )
			$result = date_default_timezone_set( @$GLOBALS['RUNTIME_CONF']['TZ'] );
		else
		if ( isset($GLOBALS['RUNTIME_ENV']['PITAYA_SYSTEM_TIMEZONE']) )
			$result = date_default_timezone_set( @$GLOBALS['RUNTIME_ENV']['PITAYA_SYSTEM_TIMEZONE'] );
		else
		if ( isset($GLOBALS['RUNTIME_ENV']['TZ']) )
			$result = date_default_timezone_set( @$GLOBALS['RUNTIME_ENV']['TZ'] );
		else
		if ( ini_get('date.timezone') != "" )
			$result = date_default_timezone_set( ini_get('date.timezone') );


		if ( $result === FALSE ) date_default_timezone_set('UTC');
	});

	define( 'PITAYA_BOOT_TIME',		time() - ((microtime( TRUE ) - PITAYA_METRIC_BOOT_TIME) | 0) );
	define( 'PITAYA_ZONE_DIFF',		date( 'Z' ) | 0 );
	define( 'PITAYA_BOOT_TIMEZONE', date_default_timezone_get() );
