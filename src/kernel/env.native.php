<?php
	define( 'DEBUG_BACKTRACE_ENABLED', function_exists( "debug_backtrace" ) );

	if ( !function_exists( "resolveLnk" ) ) {
		function resolveLnk( $lnkPath ) {
			$lnkPath  = realpath($lnkPath);
			$shell = new COM('WScript.Shell');
			$shortcut = $shell->createshortcut($lnkPath);
			$targetPath = $shortcut->targetpath;
			return $targetPath;
		
			/*
				// The following method could be failed on COM generated lnk files
				// Borrowed from http://www.witti.ws/blog/2011/02/21/extract-path-lnk-file-using-php
				$linkContent = file_get_contents( $lnkPath );
				return preg_replace( '@^.*\00([A-Z]:)(?:[\00\\\\]|\\\\.*?\\\\\\\\.*?\00)([^\00]+?)\00.*$@s', '$1\\\\$2', $linkContent );
			*/
		}
	}
	function s_define($name, $value, $sensitive = TRUE, $throwException = FALSE) {
		if ( !defined($name) ) {
			define($name, $value, $sensitive === FALSE);
			return;
		}

		if( $throwException )
			throw(new Exception("Constant {$name} has been defined!"));
	}
	function caller() {
		if ( !DEBUG_BACKTRACE_ENABLED ) return NULL;
	
		DEBUG_WARNING( "caller is designed for debugging! It can be harmful to your system performance" );
	
		$backtrace = debug_backtrace(0);
		return $backtrace[2]['class'];
	}
	function IS_POSITIVE( $value ) {
		static $CANDIDATES = [ TRUE, '1', 'yes', 'on', 'true' ];
		if ( is_string($value) ) {
			$value = strtolower($value);
		}
		return in_array( $value, $CANDIDATES, TRUE );
	}
