<?php
	/**
	 ** 1028.CSMS-BDF - env.const.php
	 ** Created by JCloudYu on 2015/11/20 15:29
	 **/

	call_user_func(function(){
		if ( __STANDALONE_EXEC_MODE__ )
			s_define( "DEFAULT_SYSTEM_LOG_PACKAGE", $logPath = 'working.plog', TRUE );
		else
		{
			if ( is_writable( $logPath = path( 'data.log' )) )
				s_define( "DEFAULT_SYSTEM_LOG_PACKAGE", $logPath, TRUE );
			else
			if ( is_writable( $logPath = path( 'root.log' )) )
				s_define( "DEFAULT_SYSTEM_LOG_PACKAGE", $logPath, TRUE );
		}

		if ( defined( 'DEFAULT_SYSTEM_LOG_PACKAGE' ) )
			s_define( "DEFAULT_SYSTEM_LOG_DIR",	path( DEFAULT_SYSTEM_LOG_PACKAGE ), TRUE );
		else
			s_define( "DEFAULT_SYSTEM_LOG_DIR", sys_get_temp_dir(), TRUE );
	});
