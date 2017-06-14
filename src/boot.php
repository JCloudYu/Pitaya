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
	
	
	s_define( 'PITAYA_BOOT_TIME', PITAYA_METRIC_BOOT_TIME|0, TRUE, TRUE );
	s_define( 'PITAYA_ZONE_DIFF', date( 'Z' )|0, TRUE, TRUE );
	s_define( 'SYS_TIMEZONE', 'UTC', TRUE, TRUE );
	date_default_timezone_set( SYS_TIMEZONE );
	
	require_once PITAYA_ROOT . "/kernel/env.version.php";
	
	// INFO: Runtime configurations
   @include_once ROOT . '/pitaya.php';
   @include_once ROOT . ( IS_CLI_ENV ? "/cli.php" : "/config.php" );
   @include_once ROOT . "/common.php";
	
	require_once PITAYA_ROOT . '/kernel/env.kernel-conf.php';
	require_once PITAYA_ROOT . '/kernel/env.core.php';
	
	require_once PITAYA_ROOT . '/kernel/env.runtime.php';
	require_once PITAYA_ROOT . "/kernel/env.const.php";
	
	

	// INFO: Load system core libraries and prepare system constants
	using( 'kernel.extension.*' );
	using( 'kernel.basis.PBObject' );
	using( 'kernel.basis.*' );
	using( 'kernel.core.*' );
	using( 'kernel.sys' );
	
	
	
	require_once PITAYA_ROOT . "/kernel/env.cleanup.php";
	pb_metric();
