<?php
	if ( !empty($options->refBasis) ) {
		if ( !is_dir( $options->refBasis ) ) {
			fwrite( STDERR, "Referenced pitaya basis directory is invalid!" );
			exit(1);
		}
		
		$src  = @realpath($options->refBasis);
		$path = "{$targetPath}/Basis";
		if ( !IsValidPath($path) ) {
			CreateLink($src, $path);
		}
	}
	else
	if ( !$options->noBasis ) {
		$path = "{$targetPath}/Basis";
		if ( !IsValidPath($path) ) {
			@mkdir( $path, 0755, TRUE );
		}
	}



	if ( !empty($options->refShare) ) {
		if ( !is_dir( $options->refShare ) ) {
			fwrite( STDERR, "Referenced pitaya share directory is invalid!" );
			exit(1);
		}
		
		$src  = @realpath($options->refShare);
		$path = "{$targetPath}/Share";
		if ( !IsValidPath($path) ) {
			CreateLink($src, $path);
		}
	}
	else
	if ( $options->createShare ) {
		$path = "{$targetPath}/Share";
		if ( !IsValidPath($path) ) {
			@mkdir( $path, 0755, TRUE );
		}
	}


	if ( !empty($options->refData) ) {
		if ( !is_dir( $options->refData ) ) {
			fwrite( STDERR, "Referenced pitaya data directory is invalid!" );
			exit(1);
		}
		
		$src  = @realpath($options->refData);
		$path = "{$targetPath}/Data";
		if ( !IsValidPath($path) ) {
			CreateLink($src, $path);
		}
	}
	else
	if ( $options->createData ) {
		$path = "{$targetPath}/Data";
		if ( !IsValidPath($path) ) {
			@mkdir( $path, 0755, TRUE );
		}
	}
	
	
	
	if ( !empty($options->refLib) ) {
		if ( !is_dir( $options->refLib ) ) {
			fwrite( STDERR, "Referenced pitaya lib directory is invalid!" );
			exit(1);
		}
		
		$src  = @realpath($options->refLib);
		$path = "{$targetPath}/Lib";
		if ( !IsValidPath($path) ) {
			CreateLink($src, $path);
		}
	}
	else
	if ( $options->createLib ) {
		$path = "{$targetPath}/Lib";
		if ( !IsValidPath($path) ) {
			@mkdir( $path, 0755, TRUE );
		}
	}




	$srcPath  = LIB_PATH . '/env/base';
	$itemList = scandir( $srcPath );
	foreach( $itemList as $item ) {
		if ( $item == "." || $item == ".." ) continue;
		
		
		$destName = ( substr($item, -12 ) == ".example.php" ) ? substr( $item, 0, -11 ) . 'php' : $item;
		if (file_exists( $path = "{$targetPath}/{$destName}" )) continue;
		
		$status = copy( ($sourcePath = "{$srcPath}/{$item}"), $path );
		if ( empty($status) ) {
			fwrite( STDERR,  "Cannot copy file {$sourcePath}!" . PHP_EOL );
		}
		else {
			if ( $item == "space.sh" ) {
				@chmod( $path, 0755 );
			}
		}
	}
