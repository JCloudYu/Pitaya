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
	
	CreateLink( $src, $dest );