<?php
	function CreateLink( $dest, $link ) {
		if ( IS_WINDOWS ) {
			if ( substr($link, -4) !== '.lnk' )
				$link .= '.lnk';
	
			$dest  = realpath($dest);
			$shell = new COM('WScript.Shell');
			$shortcut = $shell->createshortcut($link);
			$shortcut->targetpath = $dest;
			return $shortcut->save();
		}
	
		return symlink( $dest, $link );
	}
	
	function IsValidPath($path){
		return !(!is_link($path) && !file_exists($path) && !file_exists("{$path}.lnk"));
	}


	$targetPath  = WORKING_DIR;
	$options = (object)[
		'createShare'	=> FALSE,
		'createData'	=> FALSE,
		'noBasis'		=> FALSE,
		'noPitaya'		=> FALSE
	];
	
	while( TRUE ) {
		$item = @array_shift($ARGV);
		switch( $item ) {
			case "-share":
				$options->createShare = $options->createShare || TRUE;
				break;
			
			case "-data":
				$options->createData = $options->createData || TRUE;
				break;
			
			case "-no-pitaya":
				$options->noPitaya = $options->noPitaya || TRUE;
				break;
			
			case "-no-basis":
				$options->noBasis = $options->noBasis || TRUE;
				break;
				
			default:
				break 2;
		}
	}
	
	
	if ( !empty($item) ) {
		@mkdir( $targetPath = "{$targetPath}/{$item}", 0755, TRUE );
	}
	
	
	if ( !$options->noBasis ) {
		$path = "{$targetPath}/Pitaya";
		if ( !IsValidPath($path) ) {
			CreateLink( LIB_PATH . '/src', $path );
		}
	}

	if ( !$options->noBasis ) {
		$path = "{$targetPath}/Basis";
		if ( !IsValidPath($path) ) {
			@mkdir( $path, 0755, TRUE );
		}
	}

	if ( $options->createShare ) {
		$path = "{$targetPath}/Share";
		if ( !IsValidPath($path) ) {
			@mkdir( $path, 0755, TRUE );
		}
	}

	if ( $options->createData ) {
		$path = "{$targetPath}/Data";
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
