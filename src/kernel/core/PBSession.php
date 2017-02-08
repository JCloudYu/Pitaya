<?php
	using('kernel.basis.PBObject');
	using('kernel.core.PBCookie');
	using('ext.base.time');
	using('ext.base.misc');



	final class PBSession extends PBObject implements ArrayAccess
	{
		const PB_SESSION_NAME			= 'PBSESSIONINDENTIFIER';
		const FIELD_SESSION_EXPIRE_TIME	= '________PBSEESION__EXPIRE__TIME__FIELD________';
		const FIELD_SESSION_DOMAIN		= '________PBSEESION_____DOMAIN_____FIELD________';
		const FIELD_SESSION_PATH		= '________PBSEESION______PATH______FIELD________';
		const FIELD_SESSION_SSL_ONLY	= '________PBSEESION___SSL____ONY___FIELD________';

		const SESSION_STATE_RESUME			 = 0;
		const SESSION_STATE_NEW				 = 1;
		const SESSION_STATE_NEW_FROM_EXPIRED = 2;


		private static $_singleton = NULL;
		public static function Session($expire = NULL, $path = NULL, $domain = NULL)
		{
			DEPRECATION_WARNING( "PBSession is marked as deprecated! If you want to set cookie, please refer to PBHTTP::SetCookie api!" );
			
			
			if (self::$_singleton) return self::$_singleton;

			self::$_singleton = new PBSession($expire, $path, $domain);
			return self::$_singleton;
		}
		public static function DestroySession()
		{
			if (empty(self::$_singleton)) return;

			self::$_singleton->__destroySession();
			self::$_singleton = NULL;
		}
		public static function InvalidSession($newExpire = NULL, $newPath = NULL, $newDomain = NULL)
		{
			if (!empty(self::$_singleton)) return self::Session($newExpire, $newPath, $newDomain);

			self::$_singleton->invalid($newExpire, $newPath, $newDomain);
			return self::$_singleton;
		}
		public static function GenerateSessID()
		{
			$privSess = session_id();
			session_write_close();



			session_start();
			session_regenerate_id();
			$id = session_id();
			session_destroy();



			if (!empty($privSess))
			{
				session_id($privSess);
				session_start();
			}

			return $id;
		}





		private $_sessionState		= PBSession::SESSION_STATE_RESUME;
		private $_sessionName		= '';
		private $_sessionId			= '';

		private $_sessionPath		= '';
		private $_sessionDomain		= '';
		private $_sessionExpire		= PBCookie::EXPIRE_AFTER_BROWSER_CLOSE;
		private $_sessionSSLOnly	= FALSE;

		private function __construct($expire = NULL, $path = NULL, $domain = NULL)
		{
			// INFO: Prepare variables
			$cookie		 = PBCookie::Cookie();
			$requestTime = PBRequest::Request()->requestTime;



			$this->_sessionName = PBSession::PB_SESSION_NAME; // NOTE: PBSession Cookie Name
			if (isset($cookie[$this->_sessionName]))
			{
				// INFO: Check and load session with specified id
				$this->_sessionId = $cookie[$this->_sessionName];
				session_id($this->_sessionId); session_start();



				$expireTime = intval(@$_SESSION[self::FIELD_SESSION_EXPIRE_TIME]);
				if (($expireTime <= $requestTime) && ($expireTime != 0))	// NOTE: Session expired
				{
					session_destroy();
					$this->_sessionState = PBSession::SESSION_STATE_NEW_FROM_EXPIRED;
				}
				else
				{
					// INFO: Load session configuration
					$this->_sessionExpire	= (($expireTime == 0) ? 0 : $expireTime - $requestTime);
					$this->_sessionPath		= CAST(@$_SESSION[self::FIELD_SESSION_PATH], 'string');
					$this->_sessionDomain	= CAST(@$_SESSION[self::FIELD_SESSION_DOMAIN], 'string');
					$this->_sessionSSLOnly	= (@$_SESSION[self::FIELD_SESSION_SSL_ONLY] === TRUE);



					// INFO: Check input updaing paramters
					$updateCookie = FALSE;

					if ($expire !== NULL)
					{
						$this->_sessionExpire = CAST($expire, 'int strict');
						$_SESSION[self::FIELD_SESSION_EXPIRE_TIME] = ($this->_sessionExpire == 0) ? 0 : $this->_sessionExpire + $requestTime;
						$updateCookie = $updateCookie || TRUE;
					}

					if ($domain !== NULL)
					{
						$this->_sessionDomain = empty($domain) ? PBRequest::Request()->domain : $domain;
						$_SESSION[self::FIELD_SESSION_DOMAIN] = $this->_sessionDomain;
						$updateCookie = $updateCookie || TRUE;
					}

					if ($path !== NULL)
					{
						$this->_sessionPath = "{$path}";
						$_SESSION[self::FIELD_SESSION_PATH] = $this->_sessionPath;
						$updateCookie = $updateCookie || TRUE;
					}

					if ($updateCookie) $this->__updateCookie();

					return;
				}
			}
			else
			{
				$this->_sessionState = PBSession::SESSION_STATE_NEW;
			}


			$this->_sessionExpire	= ($expire === NULL) ? PBCookie::EXPIRE_AFTER_BROWSER_CLOSE : CAST($expire, 'int strict');
			$this->_sessionPath		= "{$path}";
			$this->_sessionDomain	= empty($domain) ? PBRequest::Request()->domain : $domain;
			$this->_sessionSSLOnly	= FALSE;

			$this->_sessionId = PBSession::GenerateSessID();
			session_id($this->_sessionId); session_start(); session_unset();


			$_SESSION[self::FIELD_SESSION_EXPIRE_TIME]	= ($this->_sessionExpire == 0) ? 0 : $this->_sessionExpire + $requestTime;
			$_SESSION[self::FIELD_SESSION_PATH]			= $this->_sessionPath;
			$_SESSION[self::FIELD_SESSION_DOMAIN]		= $this->_sessionDomain;
			$_SESSION[self::FIELD_SESSION_SSL_ONLY]		= $this->_sessionSSLOnly;

			$this->__updateCookie();
		}

		public function invalid($newExpire = NULL, $newPath = NULL, $newDomain = NULL)
		{
			$requestTime = PBRequest::Request()->requestTime;

			$this->_sessionState = PBSession::SESSION_STATE_NEW_FROM_EXPIRED;
			session_destroy();

			$this->_sessionExpire	= ($newExpire === NULL) ? PBCookie::EXPIRE_AFTER_BROWSER_CLOSE : CAST($newExpire, 'int strict');
			$this->_sessionPath		= "{$newPath}";
			$this->_sessionDomain	= empty($newDomain) ? PBRequest::Request()->domain : $newDomain;
			$this->_sessionSSLOnly	= FALSE;

			$this->_sessionId = PBSession::GenerateSessID();
			session_id($this->_sessionId); session_start(); session_unset();


			$_SESSION[self::FIELD_SESSION_EXPIRE_TIME]	= ($this->_sessionExpire == 0) ? 0 : $this->_sessionExpire + $requestTime;
			$_SESSION[self::FIELD_SESSION_PATH]			= $this->_sessionPath;
			$_SESSION[self::FIELD_SESSION_DOMAIN]		= $this->_sessionDomain;
			$_SESSION[self::FIELD_SESSION_SSL_ONLY]		= $this->_sessionSSLOnly;

			$this->__updateCookie();
		}



		private $_updating = FALSE;
		private function __updateCookie()
		{
			if ($this->_updating) return;

			$cookie = PBCookie::Cookie();
			$cookie->set($this->_sessionName,
						 $this->_sessionId,
						 $this->_sessionExpire,
						 $this->_sessionPath,
						 $this->_sessionDomain,
						 $this->_sessionSSLOnly, TRUE);
		}
		private function __destroySession()
		{
			$this->_sessionExpire = PBCookie::EXPIRE_RIGHT_NOW;
			$this->_sessionId	  = '';
			$this->__updateCookie();

			session_destroy();
		}

		public function START_UPDATING_SESSION_INFO() { $this->_updating = TRUE; }
		public function STOP_UPDATING_SESSION_INFO() { $this->_updating = FALSE; }


		public function __get_state()		{ return $this->_sessionState; }
		public function __get_id()			{ return $this->_sessionId; }
		public function __get_sessionName()	{ return $this->_sessionName; }
		public function __get_domain()		{ return $this->_sessionDomain; }
		public function __get_path()		{ return $this->_sessionPath; }
		public function __get_expire()		{ return $this->_sessionExpire; }
		public function __get_sslOnly()		{ return $this->_sessionSSLOnly; }
		public function __get_serverOnly()	{ return TRUE; }

		public function __set_id($value)		  { session_destroy(); session_start($value); $this->__updateCookie(); }
		public function __set_sessionName($value) { if (empty($value)) $value = PBSession::PB_SESSION_NAME; $this->_sessionName	= $value; $this->__updateCookie(); }
		public function __set_domain($value)	  { $this->_sessionDomain	= (empty($value)) ? PBRequest::Request()->domain : $value; $this->__updateCookie(); }
		public function __set_path($value)		  { $this->_sessionPath		= preg_replace("/^[\\/\\\\]*/", '', "{$value}"); $this->__updateCookie(); }
		public function __set_expire($value)	  { $this->_sessionExpire	= CAST($value, 'int strict'); $this->__updateCookie(); }
		public function __set_sslOnly($value)	  { $this->_sessionSSLOnly	= ($value === TRUE); $this->__updateCookie(); }




		public function __destruct() { session_commit(); }

		public function set($name, $value) { $_SESSION[$name] = $value; }
		public function get($name, $type = 'raw', $default = NULL)
		{
			if (!isset($_SESSION[$name])) return $default;

			return CAST($_SESSION[$name], $type);
		}
		public function is_set($name) { return isset($_SESSION[$name]); }
		public function delete($name) { unset($_SESSION[$name]); }

		public function offsetGet($offset) 			{ return $this->get($offset); }
		public function offsetSet($offset, $value)	{ $this->set($offset, $value); }
		public function offsetExists($offset)		{ return $this->is_set($offset); }
		public function offsetUnset($offset)		{ $this->delete($offset); }
	}
