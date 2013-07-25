<?php
	using('ext.base.time');
	using('ext.base.misc');

	final class PBSession
	{
		// region [ Singleton Control ]
		private static $_sessionInst = NULL;
		private static $_confTime = 0;
		private static $_confSessionId = '';
		private static $_confDomain = '';

		public static function Session()
		{
			if (self::$_sessionInst) return self::$_sessionInst;
			self::$_sessionInst = new PBSession(self::$_confTime, self::$_confSessionId, self::$_confDomain);
			return self::$_sessionInst;
		}

		public static function SessionTime($time = 0)
		{
			self::$_confTime  = (is_array($time)) ? TO(@$time['day'], 	 'int') * DAY_SEC +
				TO(@$time['hour'],	 'int') * HOUR_SEC +
				TO(@$time['minute'], 'int') * MINUTE_SEC +
				TO(@$time['second'], 'int')
				: TO($time, 'int');
		}

		public static function SessionId($sessionId = '') { self::$_confSessionId = $sessionId; }
		public static function SessionDomain($domain = '') { self::$_confDomain = $domain; }




		private $_sessionStatus = FALSE;
		private $_sessionId = '';
		private function __construct($time = 0, $sessionId = '', $domain = '')
		{
			if (!empty($sessionId)) session_id($sessionId);

			$time = time() + TO($time, 'int');

			session_set_cookie_params($time, '/' . __SERVICE__, $domain);
			$this->_sessionStatus = session_start();
			if (empty($this->_sessionId)) $this->_sessionId = session_id();
		}
		public function __destruct() { session_write_close(); }
		// endregion

		// region [ Information Functions ]
		public function status() { return $this->_sessionStatus; }
		public function session_id() { return $this->_sessionId; }
		// endregion

		// region [ Getters and Setters ]
		public function __unset($name) { unset($_SESSION[$name]); }
		public function __isset($name) { return array_key_exists($name, $_SESSION); }

		public function __get($name) { return @$_SESSION[$name]; }
		public function __set($name, $value) { $_SESSION[$name] = $value; }

		public function get($name, $type, $default)
		{
			if (!array_key_exists($name, $_SESSION)) return $default;

			return TO($_SESSION[$name], $type);
		}
		// endregion
	}