<?php
	using('kernel.basis.PBObject');
	using('ext.base.time');
	using('ext.base.misc');

	final class PBRequest extends PBObject
	{
		// region [ Singleton Controller ]
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

			// INFO: GET information is not kept since it may contains error parsed parameters
			// INFO: This means that the main module have to parse its own parameters from request
			unset($_GET); 		unset($HTTP_GET_VARS);
			unset($_POST); 		unset($HTTP_POST_VARS);
			unset($_FILES);		unset($HTTP_POST_FILES);
			unset($_ENV); 		unset($HTTP_ENV_VARS);
			unset($_SERVER);	unset($HTTP_SERVER_VARS);

			unset($_REQUEST);
			unset($GLOBALS['rawRequest']);
			unset($GLOBALS['service']);
			unset($GLOBALS['request']);
		}
		// endregion

		// region [ Getters / Setters ]
		public function __get_all() { return $this->_incomingRecord; }

		public function __get_request()		{ return $this->_incomingRecord['request']; }
		public function __get_service() 	{ return $this->_incomingRecord['request']['service']; }
		public function __get_query() 		{ return $this->_parsedQuery ? $this->_parsedQuery : $this->_incomingRecord['request']['query']; }
		public function __get_data() 		{ return $this->_parsedData  ? $this->_parsedData  : $this->_incomingRecord['request']['data']; }
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

		// region [ Data Preprocessing Methods ]
		private $_parsedData = NULL;
		private $_dataVariable = NULL;
		private $_dataFlag = NULL;

		/**
		 * Treat and parse the incoming data as the sepcified type.
		 * The given function will be triggered only when the type is 'cust'.
		 * If there's no function given, system will parse the data using system built-in parsing function.
		 *
		 * Note that the input function must return an array with two strin indices, 'data' and 'variable', in which
		 * 'data' represets the result structure and variable indicates the vairables that are stored in the
		 * incoming data, which will be used by function PBRequest::data
		 *
		 * @param string $type the type to which the incoming data will be converted
		 * @param mixed $param the parameters that will be used during parsing process
		 * @param callable $dataFunction the function that will be used to parse system's incoming data
		 *
		 * @return $this the PBRequest instance itself
		 */
		public function parseData($type = 'cust', $param = NULL, Closure $dataFunction = NULL)
		{
			if ($this->_parsedData !== NULL) return $this;

			switch (strtolower($type))
			{
				case 'json':
					$func = function($targetData, $param) {
						$depth = intval(@$param['depth']);
						$data = json_decode($targetData, TRUE, ($depth <= 0) ? 512 : $depth);
						return array('data' => $data, 'variable' => $data, 'flag' => NULL);
					};
					break;

				case 'cust':
				default:
					$func = ($dataFunction === NULL) ? function($targetData) {
						$data = PBRequest::ParseAttribute($targetData);
						return array('data' => $data, 'variable' => $data['variable'], 'flag' => $data['flag']);
					} : $dataFunction;
					break;
			}

			$result = $func($this->_incomingRecord['request']['data'], $param);
			$this->_parsedData = @$result['data'];
			$this->_dataVariable = @$result['variable'];
			$this->_dataFlag = @$result['flag'];

			return $this;
		}


		private $_parsedQuery = NULL;
		private $_queryVariable = NULL;
		private $_queryFlag = NULL;
		/**
		 * Parse the system's incoming query using the given function.
		 * If there's no function given, system will parse the query using system built-in parsing function.
		 * Note that the input function must return an array with two strin indices, 'data' and 'variable', in which
		 * 'data' represets the result query structure and variable indicates the vairables that are stored in the
		 * incoming query, which will be used by function PBRequest::data
		 *
		 * @param callable $queryFunction the function that will be used to parse system's incoming query
		 *
		 * @return $this the PBRequest instance itself
		 */
		public function parseQuery(Closure $queryFunction = NULL)
		{
			if ($this->_parsedQuery !== NULL) return $this;

			$func = ($queryFunction === NULL) ? function($targetData) {
				$data = PBRequest::ParseRequest($targetData);
				return array('data' => $data, 'variable' => $data['attribute']['variable'], 'flag' => $data['attribute']['flag']);
			} : $dataFunction;

			$result = $func($this->_incomingRecord['request']['query']);
			$this->_parsedQuery = @$result['data'];
			$this->_queryVariable = @$result['variable'];
			$this->_queryFlag = @$result['flag'];

			return $this;
		}

		public function data($name, $type = 'raw', $default = NULL)
		{
			$vars = array_merge(is_array($this->_queryVariable) ? $this->_queryVariable : array(),
							    is_array($this->_dataVariable)  ? $this->_dataVariable  : array());

			if (!array_key_exists($name, $vars)) return $default;

			return TO($vars[$name], $type);
		}

		public function flag($name)
		{
			$flags = array_merge(is_array($this->_queryFlag) ? $this->_queryFlag : array(),
								 is_array($this->_dataFlag)  ? $this->_dataFlag  : array());

			$flags = array_unique($flags);
			return in_array($name, $flags) ? TRUE : FALSE;
		}
		// endregion

		// region [ Data Processing API ]
		public static function ParseRequest($rawRequest)
		{
			$rawRequest = explode('?', $rawRequest);

			$request = array('resource' => $rawRequest[0], 'attribute' => NULL);
			if(count($rawRequest) > 1) $request['attribute'] = $rawRequest[1];

			$request['resource'] = explode('/', $request['resource']);
			if($request['resource'][0] === '') $request['resource'] = array();

			$request['attribute'] = PBRequest::ParseAttribute($request['attribute']);

			return $request;
		}

		public static function ParseAttribute($rawAttribute)
		{
			$attributes = explode('&', $rawAttribute);

			if (empty($attributes)) return array();
			$attributeContainer = array('flag' => array(), 'variable' => array());
			foreach($attributes as $attr)
			{
				$buffer = preg_split('/[=:]/', $attr);

				if(count($buffer) <= 1)
				{
					if($buffer[0] !== '') $attributeContainer['flag'][] = $buffer[0];
				}
				else
				{
					if($buffer[0] !== '')
						$attributeContainer['variable'][$buffer[0]] = $buffer[1];
					else
						$attributeContainer['flag'][] = $buffer[1];
				}
			}

			return $attributeContainer;
		}
		// endregion
	}