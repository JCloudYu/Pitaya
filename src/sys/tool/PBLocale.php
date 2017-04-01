<?php
	using( 'sys.tool.PBScriptCtrl' );

	final class PBLocale extends PBObject implements ArrayAccess {	
		private static $_sharedBasePackage = NULL;
		public static function basePackage($packagePath = NULL) { 
			self::$_sharedBasePackage = "$packagePath";
		}
		public static function Locale() {
			/** @var PBLocale $__locale_singleton */
			static $__locale_singleton = NULL;
			if ($__locale_singleton !== NULL) return $__locale_singleton;

			$__locale_singleton = new PBLocale();
			return $__locale_singleton;
		}
		public static function Translate($token, $param = array()) {
			if ( !is_array($param) ) $param = [];

			$locale = self::Locale();
			return strtr($locale[$token], $param);
		}



		private $_basePackage 	= '';
		private $_curLocale 	= '';
		private $_storedLocales = array();
		public function __construct() {
			$this->_basePackage = self::$_sharedBasePackage;
			$this->_curLocale = 'default';
			$this->_storedLocales = array();
		}



		public function __get_basePackage() {
			return $this->_basePackage;
		}
		public function __set_basePackage($value) {
			$this->_basePackage = "$value";
		}
		public function __get_locale() { 
			return $this->_curLocale;
		}
		public function __set_locale($value) {
			$this->_curLocale = empty($value) ? 'default' : "$value";
			if ( !empty($this->_storedLocales[$this->_curLocale]) ) return;



			$leading	= empty($this->_basePackage) ? 'service.' : "{$this->_basePackage}.";
			$localePath	= path( "{$leading}{$this->_curLocale}", "locale.php" );
			if ( !is_file($localePath) || !is_readable($localePath) ) return;
			
			
			
			$locale	= PBScriptCtrl::Imprint( $localePath );
			$locale	= array_key_exists( 'locale', $locale ) ? $locale['locale'] : [];
			$this->_storedLocales[$this->_curLocale] = $locale;
		}



		public function offsetExists($offset) { 
			return TRUE;
		}
		public function offsetUnset($offset) {
			unset($this->_storedLocales[$this->_curLocale][$offset]);
		}
		public function offsetSet($offset, $value) {
			$this->_storedLocales[$this->_curLocale][$offset] = $value;
		}
		public function offsetGet($offset) {
			if ( is_array($this->_storedLocales[$this->_curLocale][ $offset ]) )
				return $this->_storedLocales[$this->_curLocale][ $offset ];
			return @strtr($offset, $this->_storedLocales[$this->_curLocale]);
		}
	}
	
	function PBLocale(){
		static $_singleton = NULL;
		if ( $_singleton === NULL ) {
			$_singleton = PBLocale::Locale();
		}
		
		return $_singleton;
	}
