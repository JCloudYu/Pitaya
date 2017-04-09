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
	final class FS {
		public static function CopyDir( $sourceDir, $targetDir ) {
			$jobQueue = [];
			$jobQueue[] = (object)[
				'src' => basename($sourceDir),
				'dst' => basename($targetDir)
			];
			$sourceDir = dirname($sourceDir);
			$targetDir = dirname($targetDir);
			
			while ( count($jobQueue) > 0 ) {
				$job = array_shift($jobQueue);
				$dirSrc = $job->src;
				$dirDst = empty($job->dst) ? $dirSrc : $job->dst;
				$dirList = self::__COPY_DIR( "{$sourceDir}/{$dirSrc}", "{$targetDir}/{$dirDst}" );
				foreach ( $dirList as $dir ) {
					$jobQueue[] = (object)[ 'src' => "{$job->src}/{$dir}" ];
				}
			}
		}
		public static function __COPY_DIR( $srcDir, $destDir ) {
			static $SKIPPED_FILES = [ '.', '..' ];
		
			@mkdir( $destDir );
			if ( !is_dir($destDir) || !is_writable($destDir) ) {
				fwrite( STDERR,  "Insufficient privileges to access destination dir! ({$destDir})" . PHP_EOL );
				exit(1);
			}
		
			$hDir = @opendir( $srcDir );
			if ( empty($hDir) ) {
				fwrite( STDERR,  "Cannot open source dir! ({$srcDir})" . PHP_EOL );
				exit(1);
			}
			
			
			
			$dirList = [];
			while( ($file = @readdir($hDir)) !== FALSE ) {
				if ( in_array($file, $SKIPPED_FILES) ) continue;
				$srcFile = "{$srcDir}/{$file}";
				if ( is_dir($srcFile) ) {
					$dirList[] = $file;
					continue;
				}
				
				$destFile = "{$destDir}/{$file}";
				$result = @copy( $srcFile, $destFile );
				if ( $result === FALSE ) {
					fwrite( STDERR,  "File '{$srcFile}' is not able to be copied into '{$destFile}'!" . PHP_EOL );
					exit(1);
				}
			}
			
			@closedir($hDir);
			return $dirList;
		}
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