<?php
/**
 * 1017.NeighborApp - PBRunTime.php
 * Created by JCloudYu on 2015/02/14 15:42
 */
	using('kernel.basis.PBObject');

	final class PBRuntimeCtrl extends PBObject
	{
		private static $_CURRENT_ENV	= array();
		private static $_ARGC			= 0;
		private static $_ARGV			= array();
		private static $_RUNTIME_CONF	= array();

		public static function __ImprintEnvironment()
		{
			self::$_CURRENT_ENV		= $GLOBALS['RUNTIME_ENV'];
			self::$_RUNTIME_CONF	= $GLOBALS['RUNTIME_CONF'];
			self::$_ARGC			= $GLOBALS['RUNTIME_ARGC'];
			self::$_ARGV			= $GLOBALS['RUNTIME_ARGV'];
		}
		public static function Runtime()
		{
			static $_SINGLETON = NULL;

			if ( empty($_SINGLETON) ) $_SINGLETON = new PBRunTime();
			return $_SINGLETON;
		}

		private function __construct() {}

		public function __get_runtimeEnv()		{ return SYS_EXEC_ENV; }
		public function __get_isCLI()			{ return SYS_EXEC_ENV == EXEC_ENV_CLI; }
		public function __get_isHTTP()			{ return SYS_EXEC_ENV == EXEC_ENV_HTTP; }

		public function __get_envVar()			{ return self::$_CURRENT_ENV; }
		public function __get_runtimeConf()		{ return self::$_RUNTIME_CONF; }
		public function __get_inputArgs()		{ return self::$_ARGV; }
		public function __get_inputArgsCount()	{ return self::$_ARGC; }
	}

	class_alias( 'PBRuntimeCtrl', 'PBRuntime' );
