<?php
	/**
	 ** 1043.hipower-store - env.clean.php
	 ** Created by JCloudYu on 2016/09/06 16:20
	 **/

	function DEPRECATION_WARNING( $message, $forceOutput = FALSE ) {
		$trace = debug_backtrace();
		$scopeInfo = $trace[1];
		$message = "{$message} @{$scopeInfo['file']}:{$scopeInfo['line']}";
		
		PBLog::ERRLog( $message, FALSE, 'warning.pblog' );
		if ( $forceOutput )
			echo $message . EOL;
	}


	// INFO: Clean up everything
	unset($GLOBALS['randomCert']);
	unset($GLOBALS['servicePath']);
	unset($GLOBALS['sharePath']);
	unset($GLOBALS['dataPath']);
	unset($GLOBALS['extPath']);
	unset($GLOBALS['RUNTIME_ENV']);
	unset($GLOBALS['RUNTIME_CONF']);
	unset($GLOBALS['RUNTIME_ARGC']);
	unset($GLOBALS['RUNTIME_ARGV']);
	unset($GLOBALS['STANDALONE_EXEC']);
