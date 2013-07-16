<?php
	if (__OS__ === 'WIN')
	{
		define('CMD_MOVE', 'move', TRUE);
		define('CMD_COPY', 'copy', TRUE);
	}
	else
	{
		define('CMD_MOVE', 'mv', TRUE);
		define('CMD_COPY', 'cp', TRUE);
	}

	function fileMove($srcPath, $destPath) { exec(CMD_MOVE." {$srcPath} {$destPath}"); }
	function fileCopy($srcPath, $destPath) { exec(CMD_COPY." {$srcPath} {$destPath}"); }