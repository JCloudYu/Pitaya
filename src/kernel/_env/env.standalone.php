<?php
	if ( IS_CLI_ENV ) {
		array_shift( $_SERVER['argv'] ); // INFO: Remove script file path
		
		if ( @"{$_SERVER['argv'][0]}" == "-entry" ) {
			array_shift($_SERVER['argv']);
			$GLOBALS['STANDALONE_EXEC'] = array(
				'script' => "{$_SERVER['argv'][0]}",
				'cwd'	 => ROOT
			);
			array_shift( $_SERVER['argv'] );
			
			s_define( 'PITAYA_STANDALONE_EXECUTION_MODE', TRUE );
		}
	}
	
	s_define( 'PITAYA_STANDALONE_EXECUTION_MODE', FALSE );