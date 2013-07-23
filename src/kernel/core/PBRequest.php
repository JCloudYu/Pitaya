<?php
	using('kernel.basis.PBObject');

	final class PBRequest extends PBObject
	{
		private static $_reqInstance = NULL;
		public static function Request()
		{
			if (self::$_reqInstance) return self::$_reqInstance;

			self::$_reqInstance = new PBRequest();
			return self::$_reqInstance;
		}

		private $_incomingRecord = array();
		private function __construct()
		{
			$this->_incomingRecord['rawQuery']				 = $GLOBALS['rawRequest'];
			$this->_incomingRecord['rawData']				 = file_get_contents('php://input');

			$this->_incomingRecord['request']['method']		 = $_SERVER['REQUEST_METHOD'];
			$this->_incomingRecord['request']['query']		 = $GLOBALS['request'];
			$this->_incomingRecord['request']['data']		 = $this->_incomingRecord['rawData'];
			$this->_incomingRecord['request']['service']	 = $GLOBALS['service'];
			$this->_incomingRecord['request']['files']		 = @$_FILES;


			$this->_incomingRecord['environment']['attr']	 = $_ENV;
			$this->_incomingRecord['environment']['server']	 = $_SERVER;
			$this->_incomingRecord['environment']['cookie']  = @$_COOKIE;
			$this->_incomingRecord['environment']['session'] = @$_SESSION;

			// INFO: GET information is not kept since it may contains error parsed parameters
			// INFO: This means that the main module have to parse its own parameters from request
			unset($_GET); 		unset($HTTP_GET_VARS);
			unset($_POST); 		unset($HTTP_POST_VARS);
			unset($_FILES);		unset($HTTP_POST_FILES);
			unset($_ENV); 		unset($HTTP_ENV_VARS);
			unset($_SERVER);	unset($HTTP_SERVER_VARS);

			// NOTE: Check whether the unsetting of cookie and session will influence the returning value~
			unset($_COOKIE); 	unset($HTTP_COOKIE_VARS);
			unset($_SESSION); 	unset($HTTP_SESSION_VARS);

			unset($_REQUEST);
			unset($GLOBALS['rawRequest']);
			unset($GLOBALS['service']);
			unset($GLOBALS['request']);
		}

		// region [ Getters / Setters ]
		public function __get_all() { return $this->_incomingRecord; }

		public function __get_request()		{ return $this->_incomingRecord['request']; }
		public function __get_service() 	{ return $this->_incomingRecord['request']['service']; }
		public function __get_query() 		{ return $this->_parseQuery ? $this->_parseQuery : $this->_incomingRecord['request']['query']; }
		public function __get_data() 		{ return $this->_parsedData ? $this->_parsedData : $this->_incomingRecord['request']['data']; }
		public function __get_files()		{ return $this->_incomingRecord['request']['files']; }
		public function __get_method()		{ return $this->_incomingRecord['request']['method']; }

		public function __get_env()			{ return $this->_incomingRecord['environment']['env']; }
		public function __get_attr()		{ return $this->_incomingRecord['environment']['attr']; }
		public function __get_server()		{ return $this->_incomingRecord['environment']['server']; }
		public function __get_cookie()		{ return $this->_incomingRecord['environment']['cookie']; }
		public function __get_session()		{ return $this->_incomingRecord['environment']['session']; }

		public function __get_baseQuery()	{ return $this->_incomingRecord['request']['query']; }
		public function __get_rawQuery()	{ return $this->_incomingRecord['rawQuery']; }
		public function __get_rawData()		{ return $this->_incomingRecord['rawData']; }
		// endregion

		// region [ Built In Data Preprocessing Functions ]
		private $_parsedData = NULL;
		public function parseData(Closure $dataFunction = NULL)
		{
			if ($this->_parsedData !== NULL) return $this;

			$func = ($dataFunction === NULL) ? function($targetData) {
				$data = PBHTTP::ParseAttribute($targetData);
				return $data;
			} : $dataFunction;

			$this->_parsedData = $func($this->_incomingRecord['request']['data']);

			return $this;
		}

		private $_parseQuery = NULL;
		public function parseQuery(Closure $queryFunction = NULL)
		{
			if ($this->_parseQuery !== NULL) return $this;

			$func = ($queryFunction === NULL) ? function($targetData) {
				$data = PBHTTP::ParseRequest($targetData);
				return $data;
			} : $dataFunction;

			$this->_parseQuery = $func($this->_incomingRecord['request']['query']);

			return $this;
		}

		public function data($name, $type = 'raw', $default = NULL)
		{
			if ($this->_parsedData === NULL) return NULL;
			if (!array_key_exists($name, $this->_incomingRecord)) return $default;

			switch ($type)
			{
				case 'int':		return intval($this->_incomingRecord[$name]);
				case 'float':	return floatval($this->_incomingRecord[$name]);
				case 'string':	return trim($this->_incomingRecord[$name]);
				case 'raw':
				default:
					break;
			}

			return $this->_incomingRecord[$name];
		}
		// endregion
	}