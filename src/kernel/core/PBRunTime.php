<?php
/**
 * 1017.NeighborApp - PBRunTime.php
 * Created by JCloudYu on 2015/02/14 15:42
 */
	using('kernel.basis.PBObject');

	final class PBRunTime extends PBObject
	{
		private static $_CURRENT_ENV = NULL;

		public static function __ImprintEnvironment()
		{
			self::$_CURRENT_ENV = $GLOBALS['RUNTIME_ENV'];
		}

		public static function RunTime()
		{
			static $_SINGLETON = NULL;

			if ( empty($_SINGLETON) ) $_SINGLETON = new PBRunTime();
			return $_SINGLETON;
		}

		private function __construct() {}

		public function __get_runtimeEnv()	{ return SYS_EXEC_ENV; }
		public function __get_isCLI()		{ return SYS_EXEC_ENV == EXEC_ENV_CLI; }
		public function __get_isHTTP()		{ return SYS_EXEC_ENV == EXEC_ENV_HTTP; }

		public function __get_envVar()		{ return self::$_CURRENT_ENV; }
	}
