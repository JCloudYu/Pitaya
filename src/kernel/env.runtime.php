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

	// INFO: Setting Environmental Constants
	call_user_func(function() {
		if ( !defined('__DEBUG_MODE__') ) {
			$value = @$GLOBALS['RUNTIME_ENV']['PITAYA_EXEC_DEBUG_MODE'];
			if ( $value === NULL ) {
				define( '__DEBUG_MODE__', FALSE );
			}
			else {
				define( '__DEBUG_MODE__', IS_POSITIVE($value) );
			}
		}




		if ( !defined('__THROW_EXCEPTION__') ) {
			$value = @$GLOBALS['RUNTIME_ENV']['PITAYA_EXEC_THROW_EXCEPTION'];
			if ( $value === NULL ) {
				define( '__THROW_EXCEPTION__', TRUE );
			}
			else {
				define( '__THROW_EXCEPTION__', IS_POSITIVE($value) );
			}
		}


		if ( !defined('__LOG_EXCEPTION__') ) {
			define( '__LOG_EXCEPTION__', TRUE );
		}
	});
