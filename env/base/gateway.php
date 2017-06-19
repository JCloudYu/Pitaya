<?php
	define( '__ROOT', realpath(dirname("{$_SERVER['SCRIPT_FILENAME']}")) );
	
	
	
   @include_once __ROOT . "/pitaya.php";
	call_user_func(function(){
		$isWinEnv = (strtoupper(substr( PHP_OS, 0, 3 )) === 'WIN');
	
		$pitayaRootPath = defined( '__PITAYA_PATH' ) ? __PITAYA_PATH : __ROOT.'/Pitaya';
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
	
	
	
	Pitaya([
		'space-root' => __ROOT,
		'packages' => [
			'broot'		=> @constant( '__BASIS_PATH' ) ?: __ROOT.'/Basis',
			'share' 	=> @constant( '__SHARE_PATH' ) ?: __ROOT.'/Share',
			'data'		=> @constant( '__DATA_PATH' ) ?: __ROOT.'/Data',
			'lib'		=> @constant( '__LIB_PATH' )  ?: __ROOT.'/Lib'
		],
		'module-packages' => [
			'data.modules', 'share.modules'
		],
		'boot-scripts' => [
			'broot.boot', 'share.boot', 'root.boot'
		],
		'default-basis'	=> (php_sapi_name() == 'cli') ? 'cli' : 'index',
		'debug-mode' => @constant( 'DEBUG_MODE' ),
		'debug-console-width' => @constant( 'DEBUG_CONSOLE_WIDTH' ),
		'system-timezone' => @constant( 'SYS_TIMEZONE' ),
		'throw-exceptions' => @constant( 'THROW_EXCEPTIONS' ),
		'log-exceptions' => @constant( 'LOG_EXCEPTIONS' ),
		'attach-depth' => @constant( 'ENV_ATTACH_DEPTH' ) ?: @constant( 'PITAYA_ENVIRONMENTAL_ATTACH_LEVEL' ),
		
		
		'leading-modules' => [],
		'tailing-modules' => [],
		'log-dir' => __ROOT . '/Log'
	]);
	
//	require_once __PITAYA_LIB_PATH . "/boot.php";
//	PBKernel::boot();