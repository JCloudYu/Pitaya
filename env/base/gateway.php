<?php
	error_reporting( E_ALL & ~E_NOTICE );
	define( 'ROOT', realpath(dirname("{$_SERVER['SCRIPT_FILENAME']}")) );
	
	
	
   @include_once ROOT . "/pitaya.env.php";
	call_user_func(function(){
		$isWinEnv = (strtoupper(substr( PHP_OS, 0, 3 )) === 'WIN');
	
		$pitayaRootPath = defined( '__PITAYA_PATH' ) ? __PITAYA_PATH : ROOT . '/Pitaya';
		if ( $isWinEnv ) {
			if ( is_file("{$pitayaRootPath}.lnk") ) {
				$lnkPath  = realpath("{$pitayaRootPath}.lnk");
				$shell = new COM('WScript.Shell');
				$shortcut = $shell->createshortcut($lnkPath);
				$pitayaRootPath = $shortcut->targetpath;
			}
		}
		$pitayaRootPath = realpath($pitayaRootPath);
		
		define( '__PITAYA_LIB_PATH', $pitayaRootPath );
	});
	
	
   


	
	// INFO: Runtime configurations
   @include_once ROOT . '/pitaya.php';
   @include_once ROOT . ( (php_sapi_name() == 'cli') ? "/cli.php" : "/config.php" );
   @include_once ROOT . "/common.php";
   
	if ( defined('PITAYA_ENVIRONMENTAL_ATTACH_LEVEL') && !defined('ENV_ATTACH_DEPTH') ) {
		define( 'ENV_ATTACH_DEPTH', PITAYA_ENVIRONMENTAL_ATTACH_LEVEL );
	}
	
	
	
	require_once __PITAYA_LIB_PATH . "/boot.php";
	PBKernel::boot();