<?php
	function fileMove($srcPath, $destPath) { exec("move {$srcPath} {$destPath}"); }