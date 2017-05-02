<?php
	if ( !file_exists($src) ) {
		fwrite(STDERR, "Source {$src} doesn't exist!" . PHP_EOL);
		return;
	}
	
	if ( file_exists($dest) ) {
		$dest = realpath($dest);
		fwrite(STDERR, "File exists at {$dest}! Please remove it manually!" . PHP_EOL);
		return;
	}
	
	$src = realpath($src);
			
			

	@mkdir( $dest, 0777, TRUE );
				
	if ( is_dir($src) ) {
		FS::CopyDir($src, $dest);
	}
	else {
		$fileName = basename($src);
		copy( $src, "{$dest}/{$fileName}" );
	}