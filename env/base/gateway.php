<?php
	@define( '__ROOT', realpath(dirname("{$_SERVER['SCRIPT_FILENAME']}")) );
	
	
	
   @include_once __ROOT . "/pitaya.php";
	call_user_func(function(){
		$isWinEnv = (strtoupper(substr( PHP_OS, 0, 3 )) === 'WIN');
	
		$pitayaRootPath = @constant( 'PITAYA_PATH' ) ?: __ROOT.'/pitaya';
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
	
	
   


	
   @include_once __ROOT . "/pitaya.config.php";
	require_once __PITAYA_LIB_PATH . '/pitaya.php';
	require_once __PITAYA_LIB_PATH . '/init-full.php';
	
	Pitaya()->execute();
