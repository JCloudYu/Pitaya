<?php
	if ( defined( 'PITAYA_BASE_CORE_EXECUTED' ) ) return
	define( 'PITAYA_BASE_CORE_EXECUTED', TRUE );


	define( 'PITAYA_METRIC_BOOT_TIME',		microtime( TRUE ) );
	define( 'PITAYA_METRIC_BOOT_MEMORY',	memory_get_usage() );
	require_once __DIR__ . "/env.version.php";
	require_once __DIR__ . "/env.boot.php";

	// Detect minimum PHP Version
	if ( PHP_VERSION_ID < 50600 )
		die( "The system requires php 5.6.0 or higher!" );
		
		
	if ( !defined( "ROOT" ) || !defined( "PITAYA_ROOT" ) ) {
		die( "Required system constants are missing ( ROOT, PITAYA_ROOT )!" );
	}
	
	
	// INFO: Runtime configurations
   @include_once ROOT . ( IS_CLI_ENV ? "/cli.php" : "/config.php" );
   @include_once ROOT . "/common.php";

	require_once PITAYA_ROOT . '/kernel/env.native.php';
	require_once PITAYA_ROOT . '/kernel/env.core.php';
	require_once PITAYA_ROOT . '/kernel/env.path.php';
	require_once PITAYA_ROOT . '/kernel/api.path.php';
	require_once PITAYA_ROOT . '/kernel/env.time.php';
	require_once PITAYA_ROOT . '/kernel/env.runtime.php';
	require_once PITAYA_ROOT . "/kernel/env.const.php";
	

	// INFO: Load system core libraries and prepare system constants
	using( 'kernel.php-extension.*' );
	using( 'kernel.basis.PBObject' );
	using( 'kernel.basis.*' );
	using( 'kernel.core.*' );
	using( 'kernel.sys' );
	
	require_once PITAYA_ROOT . "/kernel/env.cleanup.php";	
	define( 'PITAYA_METRIC_KERNEL_TIME',	microtime( TRUE ) );
	define( 'PITAYA_METRIC_KERNEL_MEMORY',	memory_get_usage() );
