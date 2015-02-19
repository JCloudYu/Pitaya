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



	// INFO: Environmental constants
	if ( !defined('__DEBUG_MODE__') )		define('__DEBUG_MODE__',		FALSE, TRUE);
	if ( !defined('__LOG_EXCEPTION__') )	define('__LOG_EXCEPTION__',		TRUE,  TRUE);
	if ( !defined('__THROW_EXCEPTION__') )	define('__THROW_EXCEPTION__',	FALSE, TRUE);



	// INFO: Runtime control
	final class Termination
	{
		const STATUS_SUCCESS			= 0;
		const STATUS_ERROR				= 1;
		const STATUS_INCORRECT_USAGE	= 2;
		const STATUS_NOT_AN_EXECUTABLE	= 126;
		const STATUS_COMMAND_NOT_FOUND	= 127;
		const STATUS_SIGNAL_ERROR		= 128;

		private function __construct(){}

		public static function NORMALLY()		 { exit( self::STATUS_SUCCESS ); }
		public static function WITH_STATUS( $errorCode = self::STATUS_ERROR )
		{
			$errorCode = abs($errorCode);

			if ( $errorCode >= self::STATUS_SIGNAL_ERROR )
				$errorCode = $errorCode % self::STATUS_SIGNAL_ERROR;

			exit( $errorCode );
		}
	}

