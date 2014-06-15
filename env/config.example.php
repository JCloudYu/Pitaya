<?php

	// Example of the service path
	// $GLOBALS['servicePath'] = (__OS__ == 'WIN') ? 'E:/path/to/service/folder' : '/path/to/service/folder';

	// This is the default service path, you can assign customized path... with proper permissions
	$GLOBALS['servicePath'] = __DIR__ . '/Services';
	$GLOBALS['sharePath'] = __DIR__ . '/Share';


	//define("__DEFAULT_SERVICE__", 'index', TRUE);
	//define("__DEBUG_MODE__", TRUE, TRUE);
	//define("__DEBUG_CONSOLE_WIDTH__", 350, TRUE);
	//define("__LOG_EXCEPTION__", FALSE, TRUE);
	//define("__THROW_EXCEPTION__", TRUE, TRUE);

	// OS Dependent...
	//define("__CASE_SENSITIVE_MODULE_NAME__", TRUE, TRUE);
