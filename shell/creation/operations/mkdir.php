<?php
	if ( file_exists($dest) ) {
		if ( is_dir($dest) ) return;
		
		$dest = realpath($dest);
		fwrite(STDERR, "File exists at {$dest}! Please remove it manually!" . PHP_EOL);
		return;
	}
	
	
	@mkdir( @$dest, 0777, TRUE );