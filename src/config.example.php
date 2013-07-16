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
		$GLOBALS['kernelPath'] = '/var/www/hosts/Purimize/Core/kernel';
		$GLOBALS['servicePath'] =  '/var/www/hosts/Purimize/Services';
	}

	//define("__DEFAULT_SERVICE__", 'index', TRUE);