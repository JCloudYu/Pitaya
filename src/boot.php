<?php
	if ( defined( 'PITAYA_BASE_CORE_EXECUTED' ) ) return
	define( 'PITAYA_BASE_CORE_EXECUTED', TRUE );

	// Detect minimum PHP Version
	if ( PHP_VERSION_ID < 50600 ) {
		die( "The system requires php 5.6.0 or higher!" );
	}



	define( 'PITAYA_METRIC_BOOT_TIME', microtime(TRUE) );
	require_once __DIR__ . '/kernel/_env/env.native.php';
	
	s_define( 'PITAYA_ROOT', __DIR__, TRUE, TRUE );
	s_define( 'ROOT', realpath(getcwd()), TRUE );
	
	
	
	require_once PITAYA_ROOT . "/kernel/_env/env.const.php";
	require_once PITAYA_ROOT . "/kernel/_env/env.standalone.php"; // For standalone exec mode only
	
	// INFO: Runtime configurations
   @include_once ROOT . '/pitaya.php';
   @include_once ROOT . ( IS_CLI_ENV ? "/cli.php" : "/config.php" );
   @include_once ROOT . "/common.php";
	
	require_once PITAYA_ROOT . '/kernel/env.core.php';
	require_once PITAYA_ROOT . '/kernel/env.runtime.php';
	
	

	// INFO: Load system core libraries and prepare system constants
	using( 'kernel.extension.*' );
	using( 'kernel.basis.PBObject' );
	using( 'kernel.basis.*' );
	using( 'kernel.core.*' );
	
	
	
	require_once PITAYA_ROOT . "/kernel/_env/env.cleanup.php";
	pb_metric();
