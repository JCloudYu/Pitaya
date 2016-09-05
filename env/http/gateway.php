<?php
	define( '__SPACE_ROOT__', dirname( "{$_SERVER['SCRIPT_FILENAME']}" ), FALSE );
	$pitayaRootPath = __SPACE_ROOT__."/Pitaya";

	if ( strtoupper(substr( PHP_OS, 0, 3 )) === "WIN" )
	{
		if ( !is_dir( $pitayaRootPath ) && is_file( "{$pitayaRootPath}.lnk" ) )
		{
			// Borrowed from http://www.witti.ws/blog/2011/02/21/extract-path-lnk-file-using-php
			$linkData = file_get_contents( "{$pitayaRootPath}.lnk" );
			$pitayaRootPath = preg_replace('@^.*\00([A-Z]:)(?:[\00\\\\]|\\\\.*?\\\\\\\\.*?\00)([^\00]+?)\00.*$@s', '$1\\\\$2', $linkData);
		}
	}
	
	define( '__ROOT__', $pitayaRootPath, FALSE );
	require_once __ROOT__ . "/portal.php";
