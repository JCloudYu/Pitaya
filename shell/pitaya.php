#!/usr/bin/env php
<?php
	define( 'IS_WINDOWS', (strtoupper(substr( PHP_OS, 0, 3 )) === 'WIN') );
	define( 'LIB_PATH', $LIB_PATH = realpath(__DIR__ . '/../') );
	define( 'WORKING_DIR', $WORKING_DIR = realpath(getcwd()) );
	
	$PORTAL_PATH = "{$LIB_PATH}/src/portal.php";
	if ( !is_file("{$PORTAL_PATH}") || !is_readable("{$PORTAL_PATH}") ) {
		fwrite( STDERR, "Incompatible Execution Environment!" . PHP_EOL );
		exit( 1 );
	}
	
	

	$ARGV = empty($_SERVER[ 'argv' ]) ? [] : $_SERVER[ 'argv' ];
	array_shift( $ARGV ); // remove script path
	
	switch( $mode = @$ARGV[0] ) {
		case "-c":
			array_shift($ARGV);
			require "{$LIB_PATH}/shell/creation/boot.php";
			return;
			
		case "--help":
			$detailedInfo = TRUE;
		case "-h":
			array_shift($ARGV);
			require "{$LIB_PATH}/shell/help/boot.php";
			return;
	}



	if ( is_file($mode) && is_readable($mode) ) { // INFO: Vector Mode
		array_shift($ARGV);
		$mode = escapeshellarg($mode);
		$args = []; foreach( $ARGV as $arg ) $args[] = escapeshellarg($arg);
		$proc = proc_open( "php {$LIB_PATH}/src/portal.php -entry {$mode} " . implode( ' ',  $args ), [ STDIN, STDOUT, STDERR ], $pipes );
		$stat = proc_close($proc);
		exit( $stat );
	}

	$gateway = "{$WORKING_DIR}/gateway.php";
	if ( is_file("{$gateway}") ) {
		$args = []; foreach( $ARGV as $arg ) $args[] = escapeshellarg($arg);
		$proc = proc_open( "php ./gateway.php " . implode( ' ',  $args ), [ STDIN, STDOUT, STDERR ], $pipes );
		$stat = proc_close($proc);
		exit( $stat );
	}


	
	require "{$LIB_PATH}/shell/help/boot.php";
