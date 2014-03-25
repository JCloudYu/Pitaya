<?php
/**
 * 1003.IMSIS - PBContstant.php
 * Created by JCloudYu on 2014/03/25 15:35
 */
	final class PBConstant
	{
		private static $_cachedConstants = NULL;

		public static function Constant($updateCache = FALSE)
		{
			static $_singleton = NULL;

			if ($updateCache || (self::$_cachedConstants === NULL))
				self::UpdateCache();

			if ($_singleton)
				return $_singleton;

			$_singleton = new PBConstant();
			return $_singleton;
		}

		private function UpdateCache() { self::$_cachedConstants = get_defined_constants(); }

		public function __construct() {}

		public function __get($name) { return $this->get($name); }

		public function __set($name, $val) { return $this->set($name, $val); }

		public function set($name, $val, $caseSensitive = TRUE)
		{
			self::UpdateCache();

			if (!empty(self::$_cachedConstants[$name]))
				return FALSE;

			define($name, $val, !empty($caseSensitive));
			return TRUE;
		}

		public function get($name, $type = 'raw', $default = NULL)
		{
			return (array_key_exists($name, self::$_cachedConstants)) ? TO(self::$_cachedConstants[$name], $type) :
																		$default;
		}
	}