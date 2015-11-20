<?php
	/**
	 ** 1028.CSMS-BDF - env.const.php
	 ** Created by JCloudYu on 2015/11/20 15:29
	 **/

	s_define( "DEFAULT_SYSTEM_LOG_PACKAGE", __STANDALONE_EXEC_MODE__ ? 'working.plog' : 'data.log', TRUE );
	s_define( "DEFAULT_SYSTEM_LOG_DIR",	path( SYSTEM_LOG_PACKAGE ), TRUE );
