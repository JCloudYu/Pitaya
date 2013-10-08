<?php

	if (!defined('__DEBUG_MODE__')) 				define("__DEBUG_MODE__", FALSE, TRUE);
	if (!defined('__DEBUG_CONSOLE_WIDTH__')) 		define('__DEBUG_CONSOLE_WIDTH__', 200, TRUE);
	if (!defined('__CASE_SENSITIVE_MODULE_NAME__')) define('__CASE_SENSITIVE_MODULE_NAME__', TRUE, TRUE);

	define('__DEFAULT_SERVICE_DEFINED__', defined('__DEFAULT_SERVICE__'), TRUE);
	if (!__DEFAULT_SERVICE_DEFINED__) define('__DEFAULT_SERVICE__', 'index', TRUE);

	define('EOL', (__SYS_WORKING_ENV__ === SYS_NETWORK_ENV) ? '<br />' : "\n", TRUE);