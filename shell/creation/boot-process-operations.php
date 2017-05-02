<?php
	const OPERATION_ACCEPTED_TYPES = [ 'link', 'copy', 'mkdir' ];
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
		
		
			$src = SOURCE_PROJECT_PATH . "/{$opDesc->src}";
			$dest = ESTABLISHED_PATH . "/{$opDesc->dst}";
			
			require COMMAND_DIR . "/operations/{$opDesc->type}.php";
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