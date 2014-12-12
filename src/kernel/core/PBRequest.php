<?php
	using('kernel.basis.PBObject');
	using('ext.base.time');
	using('ext.base.math');
	using('ext.base.misc');
	using('ext.base.array');

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


		private static $_invokedTime = 0;
		public static function __imprint_constants()
		{
			self::$_invokedTime = $GLOBALS['invokeTime'];
		}

		private $_incomingRecord = array();
		private function __construct()
		{
			if ( SYS_WORKING_ENV == SYS_ENV_CLI)
			{
				$this->_parsedQuery = array();
				$this->_parsedData = array();
			}

			$this->_incomingRecord['command']                = array('argc' => @$_SERVER['argc'], 'argv' => @$_SERVER['argv']);
			$this->_incomingRecord['rawQuery']				 = @$GLOBALS['rawRequest'];

			$inputCache = tmpfile();
			$rawDataStream = fopen('php://input', "rb");
			stream_copy_to_stream($rawDataStream, $inputCache);
			fclose($rawDataStream);

			$this->_incomingRecord['rawDataStream']			 = $inputCache;

			$this->_incomingRecord['request']['method']		 = strtoupper(@"{$_SERVER['REQUEST_METHOD']}");
			$this->_incomingRecord['request']['query']		 = @$GLOBALS['request'];
			$this->_incomingRecord['request']['data']		 = NULL;
			$this->_incomingRecord['request']['service']	 = @$GLOBALS['service'];
			$this->_incomingRecord['request']['files']		 = @$_FILES;
			$this->_incomingRecord['request']['post']		 = $_POST;
			$this->_incomingRecord['request']['get']		 = $_GET;


			$this->_incomingRecord['environment']['env']	 = $_ENV;
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

		// region [ Static data parsing ]
		private function __parseLocale($localeInfo = '')
		{
			$userLocales = explode(',', $localeInfo);

			$localeInfo = array();
			foreach ($userLocales as $localeContent)
			{
				$attr = explode(';', trim($localeContent));
				$lang = $country = '';
				$quality = 0;

				// INFO: language part
				if (!empty($attr[0]))
				{
					$buff = preg_split('/(^[a-zA-Z]+$)|^([a-zA-Z]+)-([a-zA-Z]+)$/', $attr[0], -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
					$lang = @"{$buff[0]}"; $country = @"{$buff[1]}";
				}
				else
					$lang = $country = '';


				// INFO: quality part
				if (!empty($attr[1]))
				{
					list($quality) = sscanf("{$attr[1]}", "q=%f");
					if (empty($quality)) $quality = 0;
				}
				else
					$quality = 1;


				if (empty($quality) || empty($lang)) continue;

				$localeInfo[] = array('lang' => strtolower($lang), 'country' => strtolower($country), 'quality' => $quality);
			}

			usort($localeInfo, function(array $a, array $b) {
				if (@$a['quality'] > $b['quality']) return -1;
				if (@$a['quality'] == $b['quality']) return 0;
				if (@$a['quality'] < $b['quality']) return 1;
			});

			return $localeInfo;
		}
		// endregion

		// region [ Getters / Setters ]
		public function __get_localePrefer()
		{
			static $localeInfo = NULL;

			if (!empty($localeInfo)) return $localeInfo;

			$info = @$this->_incomingRecord['environment']['server']['HTTP_ACCEPT_LANGUAGE'];
			$localeInfo = $this->__parseLocale(empty($info) ? '' : $info);

			return $localeInfo;
		}
		public function __get_range()
		{
			static $requestedRange = NULL;
			if ($requestedRange !== NULL) return $requestedRange;

			$requestedRange = array();
			list(,$range) = @explode('=', "{$this->_incomingRecord['environment']['server']['HTTP_RANGE']}");
			$range = trim($range);


			$range = (empty($range)) ? array() : explode(',', $range);
			foreach ($range as $rangeToken)
			{
				$rangeToken = explode('-', $rangeToken);
				$rangeToken[0] = trim($rangeToken[0]);
				$rangeToken[1] = trim($rangeToken[1]);

				$buff = array();
				$buff['from'] = (EXPR_INT($rangeToken[0])) ? intval($rangeToken[0]) : NULL;
				$buff['to']	  = (EXPR_INT($rangeToken[1])) ? intval($rangeToken[1]) : NULL;

				if (!empty($buff)) $requestedRange[] = $buff;
			}

			return $requestedRange;
		}

		public function __get_rangeUnit()
		{
			static $reqRangeType = NULL;
			if ($reqRangeType !== NULL) return $reqRangeType;

			list($reqRangeType, $range) = @explode('=', "{$this->_incomingRecord['environment']['server']['HTTP_RANGE']}");
			return $reqRangeType;
		}

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
		public function __get_rawData()		{

			$stream = $this->rawDataStream;
			$data = '';
			while ( !feof($stream) )
			{
				$buff = fread($stream, 1024);
				$data.= $buff;
			}

			return $data;
		}

		public function __get_rawDataStream() { fseek($this->_incomingRecord['rawDataStream'], 0); return $this->_incomingRecord['rawDataStream']; }

		public function __get_argv()        { return $this->_incomingRecord['command']['argv']; }
		public function __get_argc()        { return $this->_incomingRecord['command']['argc']; }
		public function __get_command()     { return $this->_incomingRecord['command']; }

		public function __get_domain() { return @"{$this->server['SERVER_NAME']}"; }
		public function __get_ssl() { return $this->is_ssl(); }

		public function is_ssl($checkStdPorts = FALSE)
		{
			static $is_https = NULL;

			if ($is_https !== NULL) return $is_https;

			$server = $this->server;

			if (in_array(@"{$server['HTTPS']}", array('on', '1')))
				return ($is_https = TRUE);
			else
				if ($checkStdPorts && (@"{$server['SERVER_PORT']}" == '443'))
					return ($is_https = TRUE);

			return ($is_https = FALSE);
		}

		public function __get_port() { return TO($this->server['SERVER_PORT'], 'int'); }

		public function __get_requestTime()
		{
			$netRequestTime = $this->_incomingRecord['environment']['server']['REQUEST_TIME'];
			return empty($netRequestTime) ? PBRequest::$_invokedTime : $netRequestTime;
		}
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

			$func = NULL;

			switch (strtolower($type))
			{
				case 'json':
					$func = function($stream, $param) {
						$targetData = stream_get_contents($stream);

						$depth = intval(@$param['depth']);
						$data = json_decode($targetData, TRUE, ($depth <= 0) ? 512 : $depth);
						return array('data' => $data, 'variable' => $data, 'flag' => NULL);
					};
					break;

				case 'cust':
					$func = $dataFunction;
				case 'raw':
				default:
					if($func === NULL) $func =  function($stream) {
						$targetData = stream_get_contents($stream);

						$data = PBRequest::ParseAttribute($targetData);
						return array('data' => $data, 'variable' => $data['variable'], 'flag' => $data['flag']);
					};
					break;
			}

			$result = $func($this->rawDataStream, $param);

			$buff = $this->recursiveDecode($result);

			$this->_parsedData = @$buff['data'];
			$this->_dataVariable = @$buff['variable'];
			$this->_dataFlag = @$buff['flag'];

			return $this;
		}

		private function recursiveDecode($content)
		{
			if (!is_array($content))
				return @$this->decodeData($content, $this->server['CONTENT_TYPE']);

			$buff = array();
			foreach ($content as $idx => $value)
			{
				if (is_array($value))
					$buff[$idx] = $this->recursiveDecode($value);
				else
					@$buff[$idx] = $this->decodeData($value, $this->server['CONTENT_TYPE']);
			}

			return $buff;
		}

		private function decodeData($data, $encType)
		{
			static $dataInfo = NULL;

			if ($dataInfo === NULL)
			{
				$dataInfo = array();
				$encType = explode(';', $encType);
				foreach ($encType as $token)
				{
					$token = strtolower(trim($token));

					// content-type
					if (preg_match('/^.*\/.*$/', $token))
						$dataInfo['type'] = $token;
					else
					if (preg_match('/^charset=.*/', $token))
						$dataInfo['charset'] = $token;
				}
			}

			if (array_key_exists('charset', $dataInfo))
			{
				// ISSUE: There convert charset here....
			}

			if (array_key_exists('type', $dataInfo))
			{
				switch ($dataInfo['type'])
				{
					case 'application/x-www-form-urlencoded':
						$data = iTrans($data, 'urlencoded');
						break;
					case 'application/base64':
						$data = iTrans($data, 'base64');
						break;
				}
			}

			return $data;
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

		public function flag($name, $matchCase = TRUE, $compareMode = IN_ARY_MODE_OR)
		{
			$flags = array_merge(is_array($this->_queryFlag) ? $this->_queryFlag : array(),
								 is_array($this->_dataFlag)  ? $this->_dataFlag  : array());

			$flags = array_unique($flags);
			return ary_flag($flags, $name, $matchCase, $compareMode);
		}


		public function post($name, $type = 'raw', $default = NULL)
		{
			$var = $this->_incomingRecord['request']['post'];

			if (!array_key_exists($name, $var)) return $default;

			return TO($var[$name], $type);
		}
		// endregion

		// region [ Data Processing API ]
		public static function ParseRequest($rawRequest)
		{
			$rawRequest = explode('?', $rawRequest);

			$request = array('resource' => $rawRequest[0], 'attribute' => NULL);
			if(count($rawRequest) > 1) $request['attribute'] = $rawRequest[1];

			$request['resource'] = explode('/', $request['resource']);
			if ($request['resource'][0] === '') $request['resource'] = array();

			// INFO: In some cases with only one element, there will be a tailling empty string...
			if (end($request['resource']) === '') array_pop($request['resource']);
			reset($request['resource']);

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
