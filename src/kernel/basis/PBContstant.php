<?php
/**
 * 1003.IMSIS - PBContstant.php
 * Created by JCloudYu on 2014/03/25 15:35
 */
	final class PBConstant implements ArrayAccess
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

		private static function UpdateCache() { self::$_cachedConstants = get_defined_constants(); }



		public function __construct() {}

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



		// INFO: Magic methods
		public function __get($name) { return $this->get($name); }
		public function __set($name, $val) { $this->set($offset, $val); }
		public function __isset($name) { self::UpdateCache(); return isset($this[$name]); }



		// INFO: Array access
		public function offsetExists($offset) { self::UpdateCache(); return isset(self::$_cachedConstants[$offset]); }
		public function offsetGet($offset) { return $this->get($offset); }
		public function offsetSet($offset, $val) { $this->set($offset, $val); }
		public function offsetUnset($offset) {}
	}