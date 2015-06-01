<?php
/**
 * 0026.litchi - locale.php
 * Created by JCloudYu on 2013/10/05 13:41
 */

	using('kernel.basis.PBObject');
	using('ext.base.misc');

	final class PBLocale extends PBObject implements ArrayAccess
	{
		private static $_sharedBasePackage = NULL;

		public static function basePackage($packagePath = NULL) { self::$_sharedBasePackage = "$packagePath"; }
		public static function Locale()
		{
			static $__locale_singleton = NULL;
			if ($__locale_singleton !== NULL) return $__locale_singleton;

			$__locale_singleton = new PBLocale();
			return $__locale_singleton;
		}
		public static function Translate($token, $param = array())
		{
			if ( !is_array($param) ) $param = array();

			$locale = self::Locale();
			return strtr($locale[$token], $param);
		}

		public static function Linkfy($token, $translation, &$localeAry)
		{
			$localeAry["{$token}"]   = $translation;
			$localeAry[" {$token}"]  = $translation;
			$localeAry["{$token} "]  = $translation;
			$localeAry[" {$token} "] = $translation;
		}

		private $_basePackage 	= '';
		private $_curLocale 	= '';
		private $_storedLocales = array();

		public function __construct()
		{
			$this->_basePackage = self::$_sharedBasePackage;
			$this->_curLocale = 'default';
			$this->_storedLocales = array();
		}

		public function __get_basePackage() { return $this->_basePackage; }
		public function __set_basePackage($value) { $this->_basePackage = "$value"; }

		public function __get_locale() { return $this->_curLocale; }
		public function __set_locale($value)
		{
			$this->_curLocale =  empty($value) ? 'default' : "$value";

			if (isset($this->_storedLocales[$this->_curLocale])) return;

			$leading = empty($this->_basePackage) ? 'service.' : "{$this->_basePackage}.";

			$localePath = "{$leading}{$this->_curLocale}.locale";
			$locale = (available($localePath)) ? Imprint($localePath) : array();

			@$locale = array_key_exists('locale', $locale) ? $locale['locale'] : array();

			$this->_storedLocales[$this->_curLocale] = $locale;
		}

		public function offsetExists($offset) { return TRUE; }

		public function offsetUnset($offset) { unset($this->_storedLocales[$this->_curLocale][$offset]); }

		public function offsetSet($offset, $value) { $this->_storedLocales[$this->_curLocale][$offset] = $value; }
		public function offsetGet($offset) { return @strtr($offset, $this->_storedLocales[$this->_curLocale]); }
	}
