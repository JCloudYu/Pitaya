<?php
	ini_set('session.use_cookies', '0');
	s_define('ENV_SESSION_NAME',		session_name(), TRUE, TRUE);
	s_define('ENV_SESSION_LIFETIME',	ini_get('session.gc_maxlifetime'), TRUE, TRUE);
	s_define('__DEBUG_CONSOLE_WIDTH__', 200, TRUE);
