<?php
/**
 * 1003.IMSIS - PBContstant.php
 * Created by JCloudYu on 2014/03/25 15:35
 */
 	using( 'ext.base.misc' );

	final class PBConstant implements ArrayAccess
	{
		private static $_cachedConstants = NULL;
		private static $_singleton = NULL;

		public static function Constant($updateCache = FALSE)
		{
			if ( $updateCache || (self::$_cachedConstants === NULL) )
				self::UpdateCache();

			if ( self::$_singleton === NULL )
				self::$_singleton = new PBConstant();


			return self::$_singleton;
		}
		private static function UpdateCache() {
			self::$_cachedConstants = get_defined_constants();
		}



		// INFO: Content accessors
		public function set( $name, $val, $caseSensitive = TRUE )
		{
			self::UpdateCache();

			if ( array_key_exists($name, self::$_cachedConstants) )
				return FALSE;

			define( $name, (self::$_cachedConstants[ $name ] = $val), $caseSensitive === FALSE );
			return TRUE;
		}
		public function get( $name, $type = 'raw', $default = NULL )
		{
			if ( !array_key_exists($name, self::$_cachedConstants) )
				return $default;

			return CAST( self::$_cachedConstants[$name], $type, $default );
		}
		public function map( $target ) {
			return strtr( "{$target}", self::$_cachedConstants );
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



		// INFO: Private logics
		private function __construct() {}
	}
