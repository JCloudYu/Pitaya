<?php
/**
 * 1017.NeighborApp - os.php
 * Created by JCloudYu on 2015/02/14 16:33
 */

	// INFO: Working operating system
	(preg_match('/^win|^WIN/', PHP_OS) === 1) ? define('__OS__', 'WIN', TRUE) : define('__OS__', 'UNIX', TRUE);

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

