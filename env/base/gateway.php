<?php
	error_reporting( E_ALL & ~E_NOTICE );

	define( 'ROOT', realpath(dirname("{$_SERVER['SCRIPT_FILENAME']}")) );
	define( '__IS_CLI_ENV', php_sapi_name() == 'cli' );
	define( '__IS_WIN_ENV', (strtoupper(substr( PHP_OS, 0, 3 )) === 'WIN') );
	function resolveLnk( $lnkPath ) {
		$lnkPath  = realpath($lnkPath);
		$shell = new COM('WScript.Shell');
		$shortcut = $shell->createshortcut($lnkPath);
		$targetPath = $shortcut->targetpath;
		return $targetPath;
	
		/*
			// The following method could be failed on COM generated lnk files
			// Borrowed from http://www.witti.ws/blog/2011/02/21/extract-path-lnk-file-using-php
			$linkContent = file_get_contents( $lnkPath );
			return preg_replace( '@^.*\00([A-Z]:)(?:[\00\\\\]|\\\\.*?\\\\\\\\.*?\00)([^\00]+?)\00.*$@s', '$1\\\\$2', $linkContent );
		*/
	}
	
	
	
   @include_once ROOT . "/pitaya.env.php";
	$pitayaRootPath = defined( '__PITAYA_PATH' ) ? __PITAYA_PATH : ROOT . '/Pitaya';
	if ( __IS_WIN_ENV && !is_dir( $pitayaRootPath ) && is_file( "{$pitayaRootPath}.lnk" ) ) {
		$pitayaRootPath = resolveLnk( "{$pitayaRootPath}.lnk" );
	}
	$pitayaRootPath = realpath($pitayaRootPath);
	
	define( '__PITAYA_ROOT', $pitayaRootPath );


	
	// INFO: Runtime configurations
   @include_once ROOT . '/pitaya.php';
   @include_once ROOT . ( __IS_CLI_ENV ? "/cli.php" : "/config.php" );
   @include_once ROOT . "/common.php";
	
	
	
	require_once __PITAYA_ROOT . "/portal.php";