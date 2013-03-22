<?php

if(__OS__ == 'WIN')
{
	$GLOBALS['kernelPath'] = 'E:/001.dev/0008.web.service.php.core/src/kernel';
	$GLOBALS['servicePath'] =  'E:/001.dev/0008.web.service.php.core/src/services';
}
else
{
	$GLOBALS['kernelPath'] = '/Volumes/Personal/001.jcloudyu/001.dev/0008.web.service.php.core/src/kernel';
	$GLOBALS['servicePath'] =  '/Volumes/Personal/001.jcloudyu/001.dev/0008.web.service.php.core/src/services';
}