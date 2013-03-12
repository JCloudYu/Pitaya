<?php
/*
 * File: config.php
 * Created by Cloud.
 * DateTime: 13/3/12 AM10:18
 */

if(preg_match('/^win/', PHP_OS) === 1) define('__OS__', 'WIN');
else define('__OS__', 'UNIX');

if(__OS__ == 'WIN')
	$GLOBALS['kernelPath'] = 'E:/001.dev/0008.web.service.php.core/src';
else
	$GLOBALS['kernelPath'] = '/Volumes/Personal/001.jcloudyu/001.dev/0008.web.service.php.core/src';