<?php
	$path = "{$targetPath}/Pitaya";
    if ( !IsValidPath($path) ) {
      CreateLink( LIB_PATH . '/src', $path );
    }


	if ( !empty($structure->basis) ) {
		if ( !is_dir( $structure->basis ) ) {
			fwrite( STDERR, "Referenced pitaya basis directory is invalid!" );
			exit(1);
		}
		
		$src  = @realpath($structure->basis);
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



	if ( !empty($structure->share) ) {
		if ( !is_dir( $structure->share ) ) {
			fwrite( STDERR, "Referenced pitaya share directory is invalid!" );
			exit(1);
		}
		
		$src  = @realpath($structure->share);
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


	if ( !empty($structure->data) ) {
		if ( !is_dir( $structure->data ) ) {
			fwrite( STDERR, "Referenced pitaya data directory is invalid!" );
			exit(1);
		}
		
		$src  = @realpath($structure->data);
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
	
	
	
	if ( !empty($structure->lib) ) {
		if ( !is_dir( $structure->lib ) ) {
			fwrite( STDERR, "Referenced pitaya lib directory is invalid!" );
			exit(1);
		}
		
		$src  = @realpath($structure->lib);
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
