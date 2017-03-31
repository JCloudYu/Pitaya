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
		'refPackage'	=> NULL,
		'refShare'		=> NULL,
		'refData'		=> NULL,
		'refBasis'		=> NULL,
		'refLib'		=> NULL,
		
		'createShare'	=> FALSE,
		'createData'	=> FALSE,
		'createLib'		=> FALSE,
		'noBasis'		=> FALSE
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
			
			case "-lib":
				$options->createLib = $options->createLib || TRUE;
				break;
			
			case "-no-basis":
				$options->noBasis = $options->noBasis || TRUE;
				break;
			
			
			
			case "--package":
				$options->refPackage = @trim(@array_shift($ARGV));
				break;
			
			case "--share":
				$options->refShare = @trim(@array_shift($ARGV));
				break;
			
			case "--basis":
				$options->refBasis = @trim(@array_shift($ARGV));
				break;
			
			case "--data":
				$options->refData = @trim(@array_shift($ARGV));
				break;
			
			case "--lib":
				$options->refLib = @trim(@array_shift($ARGV));
				break;
				
			default:
				break 2;
		}
	}
	
	
	if ( empty($item) )
		define( 'TARGET_PATH', $targetPath );
	else {
		define( 'TARGET_PATH', $targetPath = "{$targetPath}/{$item}" );
		@mkdir( TARGET_PATH , 0755, TRUE );
	}



	if ( !empty($options->refPackage) ) {
		require_once __DIR__ . '/boot-proj.php';
	}

	require_once __DIR__ . '/boot-init.php';