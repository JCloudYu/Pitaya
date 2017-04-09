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


	define( 'COMMAND_DIR', __DIR__ );
	$options = (object)[
		'refTopology'	=> NULL,
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
			
			
			
			case "--topology":
				$options->refTopology = @realpath(@trim(@array_shift($ARGV)));
				break;
			
			case "--share":
				$options->refShare = @realpath(@trim(@array_shift($ARGV)));
				break;
			
			case "--basis":
				$options->refBasis = @realpath(@trim(@array_shift($ARGV)));
				break;
			
			case "--data":
				$options->refData = @realpath(@trim(@array_shift($ARGV)));
				break;
			
			case "--lib":
				$options->refLib = @realpath(@trim(@array_shift($ARGV)));
				break;
				
			default:
				break 2;
		}
	}
	
	
	if ( empty($item) ) {
		define( 'ESTABLISHED_PATH', WORKING_DIR );
	}
	else {
		define( 'ESTABLISHED_PATH', WORKING_DIR . "/{$item}" );
		@mkdir( ESTABLISHED_PATH , 0755, TRUE );
	}


	
	if ( !empty($options->refTopology) ) {
		require COMMAND_DIR . '/boot-topology.php';
	}
	else {
		$targetPath = ESTABLISHED_PATH;
		$structure = (object)[
			'basis' => @$options->refBasis,
			'share' => @$options->refShare,
			'data'	=> @$options->refData,
			'lib'	=> @$options->refLib
		];
		require COMMAND_DIR . '/boot-build-space.php';
	}