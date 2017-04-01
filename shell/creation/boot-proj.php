<?php
	$isDir		= is_dir($options->refPackage);
	$projPath	= $isDir ? "{$options->refPackage}" : dirname("{$options->refPackage}");
	$package	= $isDir ? "package.json" : basename( "{$options->refPackage}" );
	
	
	
	chdir( $projPath );
	define( 'SOURCE_PROJECT_PATH', getcwd() );
	$projPath = SOURCE_PROJECT_PATH;
	
	
	
	$pkgPath = "{$projPath}/{$package}";
	if ( !is_file($pkgPath) || !is_readable($pkgPath) ) {
		fwrite( STDERR,  "Target directory is not a valid pitaya package!" . PHP_EOL );
		exit(1);
	}
	
	
	
	$pkg = @json_decode(@file_get_contents($pkgPath));
	if ( empty($pkg) ) {
		fwrite( STDERR,  "Target package file is invalid!" . PHP_EOL );
		exit(1);
	}
	
	
	
	if ( !empty($pkg->structure->basis) ) {
		$pkg->structure->basis = $path = @realpath($pkg->structure->basis);
		if ( empty($path) ) {
			fwrite( STDERR,  "Target pitaya basis dir is invalid!" . PHP_EOL );
			exit(1);
		}
	}
	
	if ( !empty($pkg->structure->share) ) {
		$pkg->structure->share = $path = @realpath($pkg->structure->share);
		if ( empty($path) ) {
			fwrite( STDERR,  "Target pitaya share dir is invalid!" . PHP_EOL );
			exit(1);
		}
	}
	
	if ( !empty($pkg->structure->data) ) {
		$pkg->structure->data = $path = @realpath($pkg->structure->data);
		if ( empty($path) ) {
			fwrite( STDERR,  "Target pitaya data dir is invalid!" . PHP_EOL );
			exit(1);
		}
	}
	
	if ( !empty($pkg->structure->lib) ) {
		$pkg->structure->lib = $path = @realpath($pkg->structure->lib);
		if ( empty($path) ) {
			fwrite( STDERR,  "Target pitaya lib dir is invalid!" . PHP_EOL );
			exit(1);
		}
	}
	
	
	
	
	$options->refBasis	= @$pkg->structure->basis ?: $options->refBasis;
	$options->refShare	= @$pkg->structure->share ?: $options->refShare;
	$options->refData	= @$pkg->structure->data ?: $options->refData;
	$options->refLib	= @$pkg->structure->lib ?: $options->refLib;
	
	
	chdir(WORKING_DIR);