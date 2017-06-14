<?php
	// region [ Environmental Initialization ]
	if ( IS_CLI_ENV ) {
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
	
	if ( !defined( 'PITAYA_STANDALONE_EXECUTION_MODE' ) ) define( 'PITAYA_STANDALONE_EXECUTION_MODE', FALSE );
	// endregion