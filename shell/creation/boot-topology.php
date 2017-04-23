<?php
	if ( !file_exists($options->refTopology) ) {
		fwrite( STDERR,  "Referenced topology path is invalid" . PHP_EOL );
		exit(1);
	}

	$options->refTopology = @realpath($options->refTopology);



	$isDir		= is_dir($options->refTopology);
	$projPath	= $isDir ? "{$options->refTopology}" : dirname("{$options->refTopology}");
	$topology	= $isDir ? "topology.json" : basename( "{$options->refTopology}" );
	
	// region [ Load topology descriptor ]
	$topPath = "{$projPath}/{$topology}";
	if ( !is_file($topPath) || !is_readable($topPath) ) {
		fwrite( STDERR,  "Topology descriptor (topology.json) is invalid!" . PHP_EOL );
		exit(1);
	}
		
	$top = @json_decode(@file_get_contents($topPath));
	if ( empty($top) ) {
		fwrite( STDERR,  "Topology descriptor (topology.json) is invalid!" . PHP_EOL );
		exit(1);
	}
	// endregion
	
	
	
	chdir( $projPath );
	define( 'SOURCE_PROJECT_PATH', $projPath = getcwd() );
	
	
	if ( !empty($top->spaces) ) {
		if ( !is_array($top->spaces) ) {
			fwrite( STDERR,  "spaces field must be an array!" . PHP_EOL );
			exit(1);
		}
	
		foreach($top->spaces as $space_structure) {
			$root = ESTABLISHED_PATH . @"/{$space_structure->root}";
			@mkdir( $root, 0755, TRUE );
			
			
			
			ReadStructure($space_structure, SOURCE_PROJECT_PATH);
			
			$structure = (object)[];
			$structure->basis	= @$space_structure->basis	?: $options->refBasis;
			$structure->share	= @$space_structure->share	?: $options->refShare;
			$structure->data	= @$space_structure->data	?: $options->refData;
			$structure->lib		= @$space_structure->lib	?: $options->refLib;
			
			$targetPath = $root;
			require COMMAND_DIR . '/boot-build-space.php';
		}
	}
	else
	if ( !empty($top->structure) ) {
		ReadStructure($top->structure, SOURCE_PROJECT_PATH);
	
		$structure = (object)[];
		$structure->basis	= @$top->structure->basis	?: $options->refBasis;
		$structure->share	= @$top->structure->share	?: $options->refShare;
		$structure->data	= @$top->structure->data	?: $options->refData;
		$structure->lib		= @$top->structure->lib		?: $options->refLib;
		
		$targetPath = ESTABLISHED_PATH;
		require COMMAND_DIR . '/boot-build-space.php';
	}
	
	
	
	const OPERATION_ACCEPTED_TYPES = [ 'link', 'copy' ];
	if ( is_array(@$top->operations) ) {
		foreach( $top->operations as $opDesc ) {
			if ( !in_array($opDesc->type, OPERATION_ACCEPTED_TYPES) )
				continue;
		
			if ( property_exists($opDesc, 'mode') ) {
				if ( $options->releaseMode ) {
					if ( $opDesc->mode != "release" )
						continue;
				}
				else {
					if ( $opDesc->mode != "debug" )
						continue;
				}
			}
		
			$source = SOURCE_PROJECT_PATH . "/{$opDesc->src}";
			if ( !file_exists($source) ) {
				fwrite(STDERR, "Source {$source} doesn't exist!" . PHP_EOL);
				continue;
			}
			
			$dest = ESTABLISHED_PATH . "/{$opDesc->dst}";
			if ( file_exists($dest) ) {
				$dest = realpath($dest);
				fwrite(STDERR, "File exists at {$dest}! Please remove it manually!" . PHP_EOL);
				continue;
			}
			
			$source = realpath($source);
			
			
			
			if ( $opDesc->type == "link" ) {
				CreateLink( $source, $dest );
			}
			else {
				@mkdir( $dest, 0777, TRUE );
				
				if ( is_dir($source) ) {
					FS::CopyDir($source, $dest);
				}
				else {
					$fileName = basename($source);
					copy( $source, "{$dest}/{$fileName}" );
				}
			}
		}
	}
	else
	if ( is_array(@$top->copy) ) {
		foreach( $top->copy as $copyDesc ) {
			$source = SOURCE_PROJECT_PATH . "/{$copyDesc->src}";
			if ( !file_exists($source) ) {
				fwrite(STDERR, "Source {$source} doesn't exist!" . PHP_EOL);
				continue;
			}
			
			$dest = ESTABLISHED_PATH . "/{$copyDesc->dst}";
			if ( file_exists($dest) ) {
				$dest = realpath($dest);
				fwrite(STDERR, "File exists at {$dest}! Please remove it manually!" . PHP_EOL);
				continue;
			}
			
			$source = realpath($source);
			
			
			
			@mkdir( $dest, 0777, TRUE );
			
			if ( is_dir($source) ) {
				FS::CopyDir($source, $dest);
			}
			else {
				$fileName = basename($source);
				copy( $source, "{$dest}/{$fileName}" );
			}
		}
	}
	
	
	
	
	function ReadStructure(stdClass $structure, $refDir) {
		if ( !empty($structure->basis) ) {
			$structure->basis = $path = @realpath("{$refDir}/" . $structure->basis);
			if ( empty($path) ) {
				fwrite( STDERR,  "Target pitaya basis dir is invalid!" . PHP_EOL );
				exit(1);
			}
		}
		if ( !empty($structure->share) ) {
			$structure->share = $path = @realpath("{$refDir}/" . $structure->share);
			if ( empty($path) ) {
				fwrite( STDERR,  "Target pitaya share dir is invalid!" . PHP_EOL );
				exit(1);
			}
		}
		if ( !empty($structure->data) ) {
			$structure->data = $path = @realpath("{$refDir}/" . $structure->data);
			if ( empty($path) ) {
				fwrite( STDERR,  "Target pitaya data dir is invalid!" . PHP_EOL );
				exit(1);
			}
		}
		if ( !empty($structure->lib) ) {
			$structure->lib = $path = @realpath("{$refDir}/" . $structure->lib);
			if ( empty($path) ) {
				fwrite( STDERR,  "Target pitaya lib dir is invalid!" . PHP_EOL );
				exit(1);
			}
		}
	}