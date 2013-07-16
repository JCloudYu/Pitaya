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
			$this->_incomingRecord['rawRequest'] = $GLOBALS['rawRequest'];
			$this->_incomingRecord['service'] = $GLOBALS['service'];
			$this->_incomingRecord['request'] = $GLOBALS['request'];
			$this->_incomingRecord['method'] = $_SERVER['REQUEST_METHOD'];

			$this->_incomingRecord['env'] = $_ENV;
			$this->_incomingRecord['server'] = $_SERVER;



			// NOTE: Now the only allows POST method to accept file input
			if ($_SERVER['REQUEST_METHOD'] == 'POST')
			{
				$this->_incomingRecord['data'] = $_POST;
				$this->_incomingRecord['files'] = $_FILES;
			}
			else
			{
				parse_str(file_get_contents('php://input'), $this->_incomingRecord['data']);
			}

			// NOTE: We still need to solve the session and cookie problem
			//$this->_incomingRecord['session'] = $_SESSION;
			//$this->_incomingRecord['cookie'] = $_COOKIE;

			// INFO: GET information is not kept since it may contains error parsed parameters
			// INFO: This means that the main module have to parse its own parameters from request
			unset($_GET);
			unset($HTTP_GET_VARS);

			unset($_POST);
			unset($HTTP_POST_VARS);

			unset($_FILES);
			unset($HTTP_POST_FILES);

			unset($_ENV);
			unset($HTTP_ENV_VARS);

			unset($_SERVER);
			unset($HTTP_SERVER_VARS);

			unset($_REQUEST);

			unset($GLOBALS['rawRequest']);
			unset($GLOBALS['service']);
			unset($GLOBALS['request']);

			// NOTE: We still need to solve the session and cookie problem
			//unset($_COOKIE); unset($HTTP_COOKIE_VARS);
			//unset($_SESSION); unset($HTTP_SESSION_VARS);
		}

		public function getAll()
		{
			return $this->_incomingRecord;
		}

		public function __get_service() { return $this->_incomingRecord['service']; }

		public function __get_request() { return $this->_incomingRecord['request']; }

		public function __get_raw() { return $this->_incomingRecord['rawRequest']; }
	}

	class_alias('PBRequest', 'Req');