<?php
	using('kernel.basis.PBObject');
	using('ext.base.time');
	using('ext.base.math');
	using('ext.base.misc');
	using('ext.base.array');
	using('ext.net.ip');

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

		public static function __imprint_constants() {
			self::GetIncomingHeaders( $_SERVER );
		}
		
		private function __construct() { }



		private static $_initialized = FALSE;
		private $_incomingRecord = array();
		public function __initialize()
		{
			if ( self::$_initialized ) return;
			self::$_initialized = TRUE;
		
		
		
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

			$this->_incomingRecord['request']['method']		 = REQUESTING_METHOD;
			$this->_incomingRecord['request']['query']		 = @$GLOBALS['request'];
			$this->_incomingRecord['request']['data']		 = NULL;
			$this->_incomingRecord['request']['service']	 = @$GLOBALS['service'];
			$this->_incomingRecord['request']['files']		 = @$_FILES;
			$this->_incomingRecord['request']['post']		 = $_POST;
			$this->_incomingRecord['request']['get']		 = $_GET;


			$this->_incomingRecord['environment']['env']	 = $_ENV;
			$this->_incomingRecord['environment']['server']	 = $_SERVER;
			$this->_incomingRecord['environment']['attachment'] = [
				'level' => PITAYA_ENVIRONMENTAL_ATTACH_LEVEL,
				'anchor' => $GLOBALS[ 'attachPoint' ]
			];



			if ( KEEP_PHP_ENVIRONMENTAL_VARIABLES == FALSE )
			{
				// INFO: GET information is not kept since it may contains error parsed parameters
				// INFO: This means that the main module have to parse its own parameters from request
				unset($_GET); 		unset($HTTP_GET_VARS);
				unset($_POST); 		unset($HTTP_POST_VARS);
				unset($_FILES);		unset($HTTP_POST_FILES);
				unset($_ENV); 		unset($HTTP_ENV_VARS);
				unset($_SERVER);	unset($HTTP_SERVER_VARS);
				unset($_REQUEST);
			}


			unset($GLOBALS['rawRequest']);
			unset($GLOBALS['service']);
			unset($GLOBALS['request']);
			unset($GLOBALS['attachPoint']);
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
			@list(,$range) = @explode('=', "{$this->_incomingRecord['environment']['server']['HTTP_RANGE']}");
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
		public function __get_headers()		{ return self::GetIncomingHeaders(); }
		public function __get_request()		{ return $this->_incomingRecord['request']; }
		public function __get_service() 	{ return $this->_incomingRecord['request']['service']; }
		public function __get_query() 		{ return $this->_parsedQuery ? $this->_parsedQuery : $this->_incomingRecord['request']['query']; }
		public function __get_data() 		{ return $this->_parsedData  ? $this->_parsedData  : $this->_incomingRecord['request']['data']; }

		private $_filesCache = NULL;
		public function __get_files()		{
			if ( $this->_filesCache !== NULL ) return $this->_filesCache;

			$this->_filesCache = array();
			$files = CAST( $this->_incomingRecord['request']['files'], 'array' );
			if ( !empty( $files ) )
			{
				foreach ( $files as $uploadName => $fileContent )
				foreach ( $fileContent as $fieldName => $fieldValue )
				{
					if ( !is_array($fieldValue) )
						$fieldValue = array( $fieldValue );

					foreach ( $fieldValue as $id => $value )
					{
						$value = ( $fieldName == "name" ) ? urldecode( $value ) : $value;
						$this->_filesCache[ $uploadName ][ $id ][ $fieldName ] = $value;
					}
				}
			}
			return $this->_filesCache;
		}
		
		public function __get_method()		{ return $this->_incomingRecord['request']['method']; }
		public function __get_method_upper(){ return strtoupper( "{$this->_incomingRecord['request']['method']}" ); }
		public function __get_method_lower(){ return strtolower( "{$this->_incomingRecord['request']['method']}" ); }
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
		public function __get_attachLevel() { return $this->_incomingRecord['environment']['attachment']['level']; }
		public function __get_attachAnchor() { static $anchor = NULL; if ( $anchor === NULL ) $anchor = $this->attachAnchor(); return $anchor; }
		public function __get_domain() { return @"{$this->server[ 'SERVER_NAME' ]}"; }
		public function __get_httpHost() { return  @"{$this->server[ 'HTTP_HOST' ]}"; }
		public function __get_httpProtocol() {
			return $this->is_ssl() ? 'https' : 'http';
		}
		public function __get_httpFullHost() {
			return "{$this->httpProtocol}://{$this->httpHost}";
		}
		public function __get_ssl() { return $this->is_ssl(); }
		public function __get_remoteIP() { return @$this->_incomingRecord['environment']['server']['REMOTE_ADDR']; }
		public function __get_nativeGet(){
			return $this->_incomingRecord['request']['get'];
		}
		public function __get_nativePost(){
			return $this->_incomingRecord['request']['post'];
		}
		public function __get_nativeFiles(){
			return $this->_incomingRecord['request']['files'];
		}
		public function __get_nativeEnv(){
			return $this->_incomingRecord['environment']['env'];
		}
		public function __get_nativeServer(){
			return $this->_incomingRecord['environment']['server'];
		}
		public function __get_port() { return CAST( $this->server['SERVER_PORT'], 'int strict', -1 ); }
		public function __get_requestTime()
		{
			$netRequestTime = @$this->_incomingRecord['environment']['server']['REQUEST_TIME'];
			return empty($netRequestTime) ? PITAYA_BOOT_TIME : $netRequestTime;
		}
		
		private $_contentType = NULL;
		public function __get_contentType() {
			return ( $this->_contentType !== NULL ) ? $this->_contentType : ($this->_contentType = self::ParseContentType( @$this->server['CONTENT_TYPE'] ));
		}



		public function attachAnchor( $traceBack = 0 ) {
			$anchor = $this->_incomingRecord['environment']['attachment']['anchor'];
			
			if ( $traceBack > 0 )
				$anchor = @array_slice( $anchor, 0, -$traceBack );
				
			return empty($anchor) ? '' : '/' . implode( '/', $anchor );
		}
		public function is_ssl( $checkStdPorts = TRUE, $checkForward = TRUE )
		{
			static $is_https = NULL;

			if ($is_https !== NULL) return $is_https;

			$_SERVER = $this->server;
			
			
			
			$isForwardedHttp = ( !!$checkForward ) && ( !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' );
			$isForwardedSSL  = ( !!$checkForward ) && ( !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on' );
			
			$isHttps		 = in_array( strtolower( @"{$_SERVER['HTTPS']}" ), [ "on", "1" ] );
			$isPort443		 = ( !!$checkStdPorts ) ? ( $this->port === 443 ) : FALSE;
			
			
			
			return ( $is_https = $isForwardedHttp || $isForwardedSSL || $isHttps || $isPort443 );
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
		 
		 
		public function parseData($type = 'cust', $param = NULL, Closure $dataFunction = NULL) {
			static $parsers = NULL;
		
			if ( $this->_parsedData !== NULL ) return $this;
			if ( $parsers === NULL ) {
			
				$nativePost = $this->_incomingRecord[ 'request' ][ 'post' ];
				$currMethod = strtoupper( "{$this->_incomingRecord['request']['method']}" );
				
				
			
				$parsers = [
					'json' => function( $stream, $param, $typeOpt ) {
						$forceAssocArray = in_array( 'force-array', $typeOpt );
						
						$targetData = stream_get_contents($stream);
						$depth		= intval(@$param['depth']);
						$inconiming	= json_decode( $targetData, $forceAssocArray, ($depth <= 0) ? 512 : $depth );
						
						return [ 'data' => $inconiming, 'variable' => $inconiming, 'flag' => [] ];
					},
					'no-cast' => function($stream) {
						$targetData = stream_get_contents($stream);
						return [ 'data' => $targetData, 'variable' => [], 'flag' => [] ];
					},
					'base64' => function($stream) {
						$targetData = stream_get_contents($stream);
						$data = @base64_decode( $targetData );
						return [ 'data' => $data, 'variable' => [], 'flag' => [] ];
					},
					'urlencoded' => function($stream) {
						$targetData = stream_get_contents($stream);
						$data = PBRequest::ParseQueryAttributes( $targetData, TRUE );
						return [ 'data' => $data, 'variable' => $data[ 'variable' ], 'flag' => $data[ 'flag' ] ];
					},
					'form-multipart' => function($stream) use($currMethod, $nativePost) {
						if ( $currMethod === "POST" )
							return [ 'data' => $nativePost, 'variable' => $nativePost, 'flag' => [] ];

						return [ 'data' => [], 'variable' => [], 'flag' => [] ];
					}
				];
			}

			


			$typeOpt = explode( ' ', strtolower("{$type}") );
			$type = array_shift( $typeOpt );
			
			switch ( $type ) {
			
				case 'json':
					$func = $parsers[ 'json' ];
					break;

				case 'raw':
					$func = $parsers[ 'no-cast' ];
					break;

				default:
					$func = $dataFunction;
					break;
			}
			
			if ( $func === NULL )
			{
				switch ( strtolower( @"{$this->contentType[ 'type' ]}" ) ) {
				
					case "application/x-www-form-urlencoded":
						$func = $parsers[ 'urlencoded' ];
						break;

					case "application/base64":
						$func = $parsers[ 'base64' ];
						break;
						
					case "application/json":
						$func = $parsers[ 'json' ];
						break;

					case "multipart/form-data":
						$func = $parsers[ 'form-multipart' ];
						break;

					default:
						$func = $parsers[ 'no-cast' ];
						break;
				}
			}
			
			
			
			
			
			
			$result = @call_user_func( $func, $this->rawDataStream, $param , $typeOpt );
			$this->_parsedData	 = @$result[ 'data' ];
			$this->_dataVariable = @$result[ 'variable' ];
			$this->_dataFlag	 = @$result[ 'flag' ];

			return $this;
		}
		public static function ParseContentType( $contentType )
		{
			$typeInfo = [];
			ary_filter( explode(';', "{$contentType}"), function( $item, &$idx ) use( &$typeInfo ) {
			
				$token = trim( $item );
				

				// content-type
				if ( preg_match('/^.*\/.*$/i', $token) )
					$typeInfo[ 'type' ] = $token;
				else
				if ( preg_match('/^(.*)=(.*)$/i', $token, $matches) )
					$typeInfo[ strtolower(trim($matches[1])) ] = trim($matches[2]);
				else
					$typeInfo[ 'misc' ][] = $item;
			});
			
			return $typeInfo;
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
				$data = PBRequest::ParseRequestQuery( $targetData);
				return array('data' => $data, 'variable' => $data['attribute']['variable'], 'flag' => $data['attribute']['flag']);
			} : $dataFunction;

			$result = $func($this->_incomingRecord['request']['query']);
			$this->_parsedQuery = @$result['data'];
			$this->_queryVariable = @$result['variable'];
			$this->_queryFlag = @$result['flag'];

			return $this;
		}
		
		/*
			PBRequest::Request()->data( name, [ type, {{additional,} default} ], src );
			PBRequest::Request()->data( name, type, default, src );
		*/
		
		private static function ___dataItr( $data, $path, &$hasData = TRUE ) {
			$path = explode( '.', "{$path}" );


		
			$currLevel = $data; $hasData = TRUE;
			while( count($path) > 0 )
			{
				$isArray = is_array($currLevel);
				$isObject = ($currLevel instanceof stdClass);
				if ( !$isArray && !$isObject )
				{
					$hasData = FALSE;
					return NULL;	
				}
			
			
		
				$index = array_shift( $path );
				if ( $isArray )
				{
					$hasData = $hasData && ( $hit = array_key_exists( $index, $currLevel ) );
					$currLevel = $hit ? $currLevel[ $index ] : NULL;
				}
				else
				if ( $isObject )
				{
					$hasData = $hasData && ( $hit = property_exists( $currLevel, $index ) );
					$currLevel = $hit ? $currLevel->{$index} : NULL;
				}
			}
			
			
			return $currLevel;
		}
		
		public function data($name, $type = 'raw', $default = NULL, $varSrc = 'all')
		{
			$CUSTOM_CAST = FALSE;
		
			// INFO: Legacy Mode ( where type is an array )
			if ( is_array( $type ) )
			{
				DEPRECATION_WARNING( "Legacy mode is invoked! Second argument will no longer accept arrays!" );
				
				$varSrc = $default;
				$default = NULL;
				$CUSTOM_CAST = TRUE;
			}
		
			$hasData = FALSE; $value = NULL;
			switch( strtolower($varSrc) )
			{
				case "query":
					$value = self::___dataItr( $this->_queryVariable, $name, $hasData );
					break;
				case "data":
					$value = self::___dataItr( $this->_dataVariable, $name, $hasData );
					break;
				case "post":
					$value = self::___dataItr( @$this->_incomingRecord['request']['post'], $name, $hasData );
					break;
				case "get":
					$value = self::___dataItr( @$this->_incomingRecord['request']['get'], $name, $hasData );
					break;
				case "all":
				default:
					$value = self::___dataItr( $this->_dataVariable, $name, $hasData );
					if ( !$hasData ) $value = self::___dataItr( $this->_queryVariable, $name, $hasData );
					break;
			}






			if ( !$CUSTOM_CAST ) return ($hasData) ? CAST( $value, $type, $default ) : $default;
				
				
			
			// INFO: Legacy Mode ( where type is an array )
			if ( array_key_exists( 'options', $type ) ) 
			{
				$options = NULL;
				if ( is_array($type) )
				{
					$options = @$type['options'];
					$type	 = @$type['type'];
				}
				
				return ( $hasData ) ? TO( $value, $type, $options ) : $default;
			}
			
			
			
			$args = array_values( $type );
			array_unshift( $args, $value );
			return call_user_func_array( 'CAST', $args );
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
			$args = func_get_args(); array_shift( $args );
			return $this->data( $name, $args, 'post' );
		}
		public function get($name, $type = 'raw', $default = NULL)
		{
			$args = func_get_args(); array_shift( $args );
			return $this->data( $name, $args, 'get' );
		}
		public function pickAttribute( $fields = array(), $customFilter = NULL )
		{
			static $_lastFilter	= NULL, $_defaultFilter	= NULL;
			if ( $_defaultFilter === NULL ) $_defaultFilter = function( $key, $val ){ return $val; };



			// INFO: Specialization for invoke chaining
			if ( is_callable($fields) )
			{
				$_lastFilter = $fields;
				return $this;
			}




			// INFO: Store input customFilter if given
			// INFO: This step goes first to allow overwrting of the default filter
			if ( func_num_args() > 1 )
				$_lastFilter = ( is_callable($customFilter) ) ? $customFilter : NULL;


			// INFO: Normalize input fields and return empty if nothing given
			$fields = is_array($fields) ? $fields : array();
			if( empty($fields) ) return '';



			$filterFunc		= is_callable($_lastFilter) ? $_lastFilter : $_defaultFilter;
			$queryVariable 	= $this->_queryVariable;
			$queryFlag 		= $this->_queryFlag;

			$filtered = array();
			ary_filter( $fields, function( $item ) use( &$filtered, &$filterFunc, $queryFlag, $queryVariable )
			{
				$encodedKey = urlencode( $item );

				// INFO: Search incoming variables
				call_user_func(function() use( &$filtered, &$filterFunc, $queryVariable, $item, $encodedKey )
				{
					$varVal = $filterFunc($item, @$queryVariable[ $item ], isset($queryVariable[$item]));
					if ( $varVal === NULL ) return;

					$value = urlencode( $varVal );
					$filtered[] = "{$encodedKey}={$value}";
				});

				// INFO: Search incoming flags
				call_user_func(function() use( &$filtered, $queryFlag, $item, $encodedKey )
				{
					if ( !in_array( $item, $queryFlag ) ) return;

					$filtered[] = $encodedKey;
				});
			});

			return implode( '&', $filtered );
		}
		// endregion

		// region [ Data Processing API ]
		public static function DecomposeQuery( $rawRequest ) {
			$rawRequest = @"{$rawRequest}";
			$rawRequest = ($rawRequest === "") ? array() : explode('?', $rawRequest);
			$resource	= @array_shift( $rawRequest );
			$attributes	= implode( '?', $rawRequest );

			return array( 'resource' => $resource, 'attributes' => $attributes );
		}
		public static function ParseRequestQuery( $rawRequest )
		{
			$parts = self::DecomposeQuery( $rawRequest );

			$resource	= $parts['resource'];
			$attributes	= $parts['attributes'];



			$request = array(
				'resource'	=> ary_filter( empty($resource) ? array() : explode( '/', $resource ), function( $item ) {
					return urldecode( $item );
				}),
				'attribute'	=> PBRequest::ParseQueryAttributes( $attributes, TRUE )
			);

			return $request;
		}
		public static function ParseQueryAttributes( $rawAttribute, $urlDecode = FALSE )
		{
			$attributes = explode( '&', "{$rawAttribute}" );

			if ( empty($attributes) ) return array();


			$decodeFunc = ($urlDecode) ? 'urldecode' : function& ( &$val ){ return $val; };


			$attributeContainer = array(
				'flag'		=> array(),
				'variable'	=> array()
			);

			foreach ( $attributes as $attr )
			{
				$buffer 	= explode( '=', $attr );
				$buffer[0]  = $decodeFunc( $buffer[0] );

				if ( count($buffer) <= 1 )
				{
					if ( $buffer[0] !== '' )
						$attributeContainer['flag'][] = $buffer[0];
				}
				else
				{
					$varComps	= preg_split( '/(\[[^]]*\])/', $buffer[0], -1, PREG_SPLIT_DELIM_CAPTURE );
					$varName	= $decodeFunc( @array_shift($varComps) );
					$buffer[1]  = $decodeFunc( $buffer[1] );

					if ( count($varComps) <= 0 )
						$attributeContainer[ 'variable' ][ $varName ] = $buffer[1];
					else
					{
						$formatError = FALSE; $indices = array();
						while ( count($varComps) > 0 )
						{
							$indices[]	= trim( substr( @array_shift( $varComps ), 1, -1 ) );
							$emptyToken	= trim( @array_shift( $varComps ) );

							$formatError = $formatError || !empty($emptyToken);
						}

						if ( !$formatError )
						{
							$lastIndex = $decodeFunc( @array_pop( $indices ) );



							if ( !is_array( $attributeContainer[ 'variable' ][ $varName ] ) )
								$attributeContainer[ 'variable' ][ $varName ] = array();


							$currentLevel = &$attributeContainer[ 'variable' ][ $varName ];
							while ( count($indices) > 0 )
							{
								$index = $decodeFunc( array_shift( $indices ) );

								if ( $index === "" )
								{
									$currentLevel[] = array();
									$index = max( array_filter( array_keys($currentLevel), 'is_int'));
								}


								if ( !is_array($currentLevel[$index]) )
									$currentLevel[$index] = array();

								$currentLevel = &$currentLevel[ $index ];
							}



							if ( $lastIndex === "" )
								$currentLevel[] = $buffer[1];
							else
								$currentLevel[ $lastIndex ] = $buffer[1];
						}
					}
				}
			}

			return $attributeContainer;
		}
		private static function GetIncomingHeaders( $_SERVER_VAR = NULL ){
			static $_incomingHeaders = NULL;
			if ( $_incomingHeaders !== NULL ) return $_incomingHeaders;


			$_incomingHeaders = array();
			foreach ( $_SERVER_VAR as $header_name => $val )
			{
				if (substr( $header_name, 0, 5 ) !== 'HTTP_') continue;

				$header_name = explode( '_', strtolower(substr( $header_name, 5 )));
				$header_name = implode( '-', ary_filter( $header_name, function( $word ){ return ucfirst($word); } ) );
				$_incomingHeaders[ $header_name ] = $val;
			}
		}
		// endregion
	}
