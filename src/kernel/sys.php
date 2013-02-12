<?php
/*
 * File: sys.php
 * Created by JCloudYu.
 * DateTime: 13/2/9 PM4:05
 */

class SYS extends PBObject implements ISYS
{
//SEC: System Boot Loader///////////////////////////////////////////////////////////////////////////////////////////////
	private static $_SYS_INSTANCE = NULL;
	public static function boot($argc = 0, $argv = NULL) {

		// INFO: Avoid repeated initialization
		if(self::$_SYS_INSTANCE) return;

		$requestItem = explode('/', $_GET['__src_request']);

		// INFO: Process Request Resource Module
		// VAR: $targetModule | $moduleRequest
		$targetModule = strtoupper(array_shift($requestItem));

		// INFO: http://SERVER_HOST/
		if($targetModule == '')
		{
			$targetModule = 'web';
			$moduleRequest = $requestItem;
		}
		else
		// INFO: http://SERVER_HOST/RC/
		// INFO: http://SERVER_HOST/RC/REQUESTS
		if(count($requestItem) > 0)
		{
			$moduleRequest = $requestItem[0] == '' ? array_shift($requestItem) : $requestItem;
		}
		else
		// INFO: http://SERVER_HOST/RC
		{
			$moduleRequest = array();
		}

		self::$_SYS_INSTANCE = new SYS($targetModule, $moduleRequest);
	}

//SEC: System Instance//////////////////////////////////////////////////////////////////////////////////////////////////
	private $_inputRequest = NULL;
	private $_entryModule = NULL;

	private $_systemId = NULL;

	// INFO: Constructor declared as a private function is to maintain the singleness of the SYS object
	private function __construct($targetModule, $moduleRequest) {

		// INFO: Generate the unique system execution Id
		$temp = array($targetModule);
		foreach($moduleRequest as $req) $temp[] = $req;
		$this->_systemId = encode($temp);

		try
		{
			$this->__arrangeVariables();

			$this->_inputRequest = $moduleRequest;
			$this->_entryModule = $targetModule;

			$this->__forkProcess($targetModule, $moduleRequest);
			$this->__jobDaemonRun();
		}
		catch(PBException $e)
		{
			print_r($e);
		}
		catch(Exception $e)
		{
			print_r($e);
		}
	}

	public function __get_id() {

		return $this->_systemId;
	}

//SEC: System Variable Manager//////////////////////////////////////////////////////////////////////////////////////////
	private $_varRawRequest = NULL;
	private $_varPost = NULL;
	private $_varGet = NULL;
	private $_varFiles = NULL;
	private $_varEnv = NULL;
	private $_varServer = NULL;

	private function __arrangeVariables() {

		// INFO: This function will unify the means accessing arguments passed into the system
		// INFO: After unifying the arguments, all other means such as $_GET, $_POST will be disabled...
		// INFO: In this version, only $_COOKIE and $_SESSION will be kept

		$this->_varRawRequest = $_GET['__src_request'];
		unset($_GET['__src_request']);

		$this->_varGet = $_GET;
		unset($_GET); unset($HTTP_GET_VARS);

		$this->_varPost = $_POST;
		unset($_POST); unset($HTTP_POST_VARS);

		$this->_varFiles = $_FILES;
		unset($_FILES); unset($HTTP_POST_FILES);

		$this->_varEnv = $_ENV;
		unset($_ENV); unset($HTTP_ENV_VARS);

		$this->_varServer = $_SERVER;
		unset($_SERVER); unset($HTTP_SERVER_VARS);

		// NOTE: We still need to solve the session and cookie problem

		unset($_REQUEST);
	}

//SEC: System Execution API/////////////////////////////////////////////////////////////////////////////////////////////

	// INFO: In this version of system, there will be only one process instance in the system (main process)
	private $_processQueue = array();

	private function __forkProcess($module, $moduleRequest) {

		$systemIds = divide($this->_systemId);
		$processId = encode(array($module, uniqid("", TRUE)), $systemIds['extended']);
		$process = new PBProcess();

		$process->__processId = $processId;
		$process->__sysAPI = $this;
		$process->attachModule($module, $moduleRequest);

		$this->_processQueue[$processId] = $process;
	}

	private function __killProcess($processId) {

		if(array_key_exists($processId, $this->_processQueue))
			unset($this->_processQueue[$processId]);
	}

	private function __jobDaemonRun() {

		while(count($this->_processQueue) > 0)
		{
			foreach($this->_processQueue as $process)
			{
				$result = $process->run();
				if(is_null($result))
					$result = 'terminated';

				switch($result)
				{
					case 'terminated':
						$this->__killProcess($process->id);
						break;
					case 'exit':
						return;
					default:
						break;
				}
			}
		}
	}

//SEC: System Response//////////////////////////////////////////////////////////////////////////////////////////////////
	// NOTE: We need to figure out a way to response error according to request module media type
	private function __responseError() {

		using('sys.http.PBHTTPReply');

		PBHTTPReply::ReplyStatus(PBHTTPStatus::STATUS_404_NOT_FOUND);
		PBHTTPReply::ReplyHTML('error', array('responseId' 	=> PBHTTPStatus::STATUS_404_NOT_FOUND,
											  'errMsg'		=> 'Service not found!',
											  'errId'		=> 10));
		die();
	}

	private function dump() {

		preg_replace('/\n/', '<br \>', preg_replace('/\ /', '&nbsp;', print_r(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT), TRUE)));
	}

//SEC: ISYS
	public function acquireModule($moduleName)
	{
		$caller = $this->caller;
		if($caller['class'] != 'PBProcess')
			throw(new Exception("Calling an inaccessible function SYS::acquireModule()."));

		if(!file_exists(__ROOT__."/modules/$moduleName/$moduleName.php"))
			throw(new Exception("Module doesn't exist!"));

		$processId = $caller['object']->id;
		$processIds = divide($processId);
		$moduleId = encode(array($processId, $moduleName), $processIds['extended']);

		using("modules.$moduleName.$moduleName");
		$module = new $moduleName();
		$module->__moduleId = $moduleId;

		return $module;
	}

	public function validateChild($childrenId)
	{
		$childrenId = divide($childrenId);
		$selfId = $this->id;

		return $selfId['base'] === $childrenId['extended'];
	}
}