<?php
	using('kernel.basis.PBObject');
	using('ext.base.time');
	using('ext.base.misc');



	final class PBCookie extends PBObject implements ArrayAccess
	{
		const EXPIRE_RIGHT_NOW			 = -1;
		const EXPIRE_AFTER_BROWSER_CLOSE =	0;
		const EXPIRE_AFTER_ONE_WEEK		 =	604800;
		const EXPIRE_AFTER_THIRTY_DAYS	 =	2592000;
		const EXPIRE_AFTER_ONE_YEAR		 =	31536000;

		public static function Cookie($path = '', $domain = '')
		{
			static $_singleton = NULL;

			if ($_singleton) return $_singleton;
			$_singleton = new PBCookie($path, $domain);

			return $_singleton;
		}





		// INFO: Object content
		private $_defaultDomain 	= '';
		private $_defaultPath		= '';
		private $_defaultExpire 	= PBCookie::EXPIRE_AFTER_BROWSER_CLOSE;
		private $_defaultSSLOnly	= FALSE;
		private $_defaultServerOnly	= TRUE;

		private function __construct($path = '', $domain = '') { $this->domain = $domain; $this->path = $path; }

		// region [ value access functions ]
		public function set($name, $value, $expire = PBCookie::EXPIRE_AFTER_BROWSER_CLOSE, $path = '', $domain = '', $sslOnly = FALSE, $serverOnly = TRUE)
		{
			if (headers_sent() || empty($name)) return FALSE;

			$args = func_get_args();

			$cookieExpire	  = (count($args) > 2) ? $expire	 : $this->_defaultExpire;
			$cookieSSLOnly	  = (count($args) > 5) ? $sslOnly	 : $this->_defaultSSLOnly;
			$cookieServerOnly = (count($args) > 6) ? $serverOnly : $this->_defaultServerOnly;



			$cookiePath		  = ((count($args) > 3) && !empty($path))	? $path	  : $this->_defaultPath;
			$cookieDomain	  = ((count($args) > 4) && !empty($domain)) ? $domain : $this->_defaultDomain;



			if ($cookieExpire > 0)
			{
				$date = new DateTime();
				$date->setTimezone(new DateTimeZone("GMT"));
				$curTime = strtotime($date->format('Y/m/d H:i:sP'));

				$cookieExpire = $curTime + $cookieExpire;
			}
			else
			if ($cookieExpire < 0)
				$cookieExpire = 1;
			else
				$cookieExpire = 0;

			

			setcookie("{$name}", "{$value}", $cookieExpire, "/{$cookiePath}", $cookieDomain, $cookieSSLOnly, $cookieServerOnly);
			return TRUE;
		}
		public function get($name, $type = 'raw', $default = NULL) {
			if ( !array_key_exists( $name, $_COOKIE ) ) return $default;
				
			$args = func_get_args(); $args[0] = $_COOKIE[$name];
			return call_user_func_array( 'CAST', $args );
		}
		public function is_set($name)	{ return isset($_COOKIE[$name]); }
		public function delete($name)
		{
			if (headers_sent()) return FALSE;
			$this->set($name, '', PBCookie::EXPIRE_RIGHT_NOW);
			unset($_COOKIE[$name]);
			return TRUE;
		}
		// endregion

		// region [ getters and setters ]
		public function __get_domain()		{ return $this->_defaultDomain; }
		public function __get_path()		{ return $this->_defaultPath; }
		public function __get_expire()		{ return $this->_defaultExpire; }
		public function __get_sslOnly()		{ return $this->_defaultSSLOnly; }
		public function __get_serverOnly()	{ return $this->_defaultServerOnly; }

		public function __set_domain($value)	 { $this->_defaultDomain	= (empty($value)) ? PBRequest::Request()->domain : $value; }
		public function __set_path($value)		 { $this->_defaultPath		= preg_replace("/^[\\/\\\\]*/", '', "{$value}"); }
		public function __set_expire($value)	 { $this->_defaultExpire	= $value; }
		public function __set_sslOnly($value)	 { $this->_defaultSSLOnly	= ($value === TRUE); }
		public function __set_serverOnly($value) {$this->_defaultServerOnly = ($value !== FALSE); }
		// endregion

		// region [ array-styled value accessing functions ]
		public function offsetGet($offset) 			{ return $this->get($offset); }
		public function offsetSet($offset, $value)	{ $this->set($offset, $value); }
		public function offsetExists($offset)		{ return $this->is_set($offset); }
		public function offsetUnset($offset)		{ $this->delete($offset); }
		// endregion
	}
