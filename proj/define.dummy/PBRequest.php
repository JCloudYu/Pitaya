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


		public $all = array();
		public $request = array();
		public $service = array();
		public $query = array();
		public $data = array();
		public $files = array();
		public $method = '';

		public $env = array();
		public $attr = array();
		public $server = array();
		public $cookie = array();
		public $session = array();

		public $baseQuery = '';
		public $rawQuery = '';
		public $rawData = '';


		// region [ Data Preprocessing Methods ]
		/**
		 * Parse the system's incoming data using the given function.
		 * If there's no function given, system will parse the data using system built-in parsing function
		 * Note that the input function must return an array with two strin indices, 'data' and 'variable', in which
		 * 'data' represets the result structure and variable indicates the vairables that are stored in the
		 * incoming data, which will be used by function PBRequest::data
		 *
		 * @param callable $dataFunction the function that will be used to parse system's incoming data
		 *
		 * @return $this the PBRequest instance itself
		 */
		public function parseData(Closure $dataFunction = NULL) { return $this; }

		/**
		 * Parse the system's incoming data according to json format
		 *
		 * @param int $jsonDepth the maximum parsing depth
		 *
		 * @return $this
		 */
		public function parseJSONData($jsonDepth = 512) { return $this; }


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
		public function parseQuery(Closure $queryFunction = NULL) { return $this; }

		public function data($name, $type = 'raw', $default = NULL) {}

		public function flag($name) {}
		// endregion

		// region [ Data Processing API ]
		public static function ParseRequest($rawRequest) {return array();}

		public static function ParseAttribute($rawAttribute) {return array();}
		// endregion
	}