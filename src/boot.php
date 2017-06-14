<?php
	if ( defined( 'PITAYA_BASE_CORE_EXECUTED' ) ) return
	define( 'PITAYA_BASE_CORE_EXECUTED', TRUE );


	define( 'PITAYA_METRIC_BOOT_TIME', microtime(TRUE) );
	require_once __DIR__ . '/kernel/_env/env.native.php';
	s_define( 'PITAYA_ROOT', __DIR__, TRUE, TRUE );
	
	
	
	
	require_once PITAYA_ROOT . "/kernel/env.version.php";
	require_once PITAYA_ROOT . "/kernel/env.boot.php";

	// Detect minimum PHP Version
	if ( PHP_VERSION_ID < 50600 ) {
		die( "The system requires php 5.6.0 or higher!" );
	}
		
	if ( IS_WIN_ENV && !class_exists( 'COM' ) ) {
		die( "COM extension is required in WindowsEnvironment! ( php_com_dotnet.dll )" );
	}
		
		
	if ( !defined( "ROOT" ) ) {
		die( "Required system constants are missing ( ROOT, PITAYA_ROOT )!" );
	}
	
	
	// INFO: Runtime configurations
   @include_once ROOT . '/pitaya.php';
   @include_once ROOT . ( IS_CLI_ENV ? "/cli.php" : "/config.php" );
   @include_once ROOT . "/common.php";
	
	require_once PITAYA_ROOT . '/kernel/env.native.php';
	require_once PITAYA_ROOT . '/kernel/env.kernel-conf.php';
	require_once PITAYA_ROOT . '/kernel/env.core.php';
	
	require_once PITAYA_ROOT . '/kernel/env.runtime.php';
	require_once PITAYA_ROOT . '/kernel/env.time.php';
	require_once PITAYA_ROOT . "/kernel/env.const.php";
	
	

	// INFO: Load system core libraries and prepare system constants
	using( 'kernel.extension.*' );
	using( 'kernel.basis.PBObject' );
	using( 'kernel.basis.*' );
	using( 'kernel.core.*' );
	using( 'kernel.sys' );
	
	
	
	require_once PITAYA_ROOT . "/kernel/env.cleanup.php";
	pb_metric();
