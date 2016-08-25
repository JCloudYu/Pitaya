<?php
	/**
	 ** 1028.CSMS-BDF - env.const.php
	 ** Created by JCloudYu on 2015/11/20 15:29
	 **/

	call_user_func(function(){
		s_define( 'KEEP_PHP_ENVIRONMENTAL_VARIABLES', FALSE, TRUE, FALSE );



		if ( __STANDALONE_EXEC_MODE__ && is_writable( path( $logPackage = 'working' )) )
		{
			@mkdir( path( $logPackage = "{$logPackage}.plog" ), 0777, TRUE );
			s_define( "DEFAULT_SYSTEM_LOG_PACKAGE", $logPackage, TRUE );
		}
		else
		if ( is_writable( path( $logPackage = 'data' )) )
		{
			@mkdir( path( $logPackage = "{$logPackage}.log" ), 0777, TRUE );
			s_define( "DEFAULT_SYSTEM_LOG_PACKAGE", $logPackage, TRUE );
		}
		else
		if ( is_writable( path( $logPackage = 'root' )) )
		{
			@mkdir( path( $logPackage = "{$logPackage}.log" ), 0777, TRUE );
			s_define( "DEFAULT_SYSTEM_LOG_PACKAGE", $logPackage, TRUE );
		}
		

		if ( defined( 'DEFAULT_SYSTEM_LOG_PACKAGE' ) )
			s_define( "DEFAULT_SYSTEM_LOG_DIR",	path( DEFAULT_SYSTEM_LOG_PACKAGE ), TRUE );
		else
			s_define( "DEFAULT_SYSTEM_LOG_DIR", sys_get_temp_dir(), TRUE );
	});
