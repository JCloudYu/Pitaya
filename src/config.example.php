<?php
	
	if (__OS__ == 'WIN')
	{
		// INFO: Windows environmental constants
		$GLOBALS['kernelPath'] = 'E:/001.dev/0008.web.service.php.core/src/kernel';
		$GLOBALS['servicePath'] =  'E:/001.dev/0009.php.web.services';
	}
	else
	{
		// INFO: Unix-like environmental constants
		$GLOBALS['kernelPath'] = '/var/www/hosts/Core/kernel';
		$GLOBALS['servicePath'] =  '/var/www/hosts/Services';
	}

	//define("__DEFAULT_SERVICE__", 'index', TRUE);
	//define("__DEBUG_MODE__", TRUE, TRUE);
	//define("__DEBUG_CONSOLE_WIDTH__", 350, TRUE);