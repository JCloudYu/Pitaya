<?php
/*
 * File: sys.php
 * Created by JCloudYu.
 * DateTime: 13/2/9 PM4:05
 */

class SYS extends PBObject
{
// region [ System Boot Loader ]
	private static $_SYS_INSTANCE = NULL;
	public static function boot($argc = 0, $argv = NULL) {

		// INFO: Avoid repeated initialization
		if(SYS::$_SYS_INSTANCE) return;

		SYS::$_SYS_INSTANCE = new SYS();
		SYS::$_SYS_INSTANCE->__initialize();
		SYS::$_SYS_INSTANCE->__jobDaemonRun();

		die();
	}
// endregion

// region [ Path Control ]
	private static $_cacheServicePath = NULL;
	private static $_cacheKernelPath = NULL;
	private static $_cacheRandomCert = NULL;

	public static function __imprint_constants() {

		static $initialized = FALSE;

		if($initialized) return;

		SYS::$_cacheServicePath = $GLOBALS['servicePath'];
		SYS::$_cacheKernelPath = $GLOBALS['kernelPath'];
		SYS::$_cacheRandomCert = $GLOBALS['randomCert'];
	}
// endregion

// region [ System Instance ]
	private $_entryService = NULL;
	private $_systemId = NULL;

	// INFO: Singleton declaration
	private function __construct() { }

	// INFO: System workflow initialization
	private function __initialize() {

		try
		{
			$this->__judgeMainService();
			PBRequest::Request();

			// INFO: Define runtime constants
			define('__SERVICE__', $this->_entryService, TRUE);

			// INFO: Generate the unique system execution Id
			$this->_systemId = encode(PBRequest::Request()->rawQuery);

			$this->__forkProcess($this->_entryService, PBRequest::Request()->query);
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

	public function __judgeMainService() {

		// INFO: Parse URL

		//SEC: REQUEST_URI Purge////////////////////////////////////////////////////////////////////////////////////////
		// INFO: Purge redundant separators from the REQUEST_URI
		// INFO: Example: http://SERVER_HOST////////RC//REQUEST/REQUEST///REQUEST?PARAMETERS=FDSAFDSAFDSADSA//
		// INFO: 		  will be purged into
		// INFO:		  http://SERVER_HOST/RC/REQUEST/REQUEST/REQUEST?PARAMETERS=FDSAFDSAFDSADSA
		$rawRequest = preg_replace('/\/+/', '/', preg_replace('/^\/*|\/*$/', '', preg_replace('/\\\\/', '/', $_SERVER['REQUEST_URI'])));
		$GLOBALS['rawRequest'] = $rawRequest;
		//END SEC///////////////////////////////////////////////////////////////////////////////////////////////////////

		// INFO: Extract the requested module from request string
		$requestItems = explode('/', $rawRequest);

		if(count($requestItems) == 1)
		{
			// http://SERVER_HOST/
			if($requestItems[0] == '')
			{
				$service = 'index';
				$moduleRequest = '';
			}
			else
			{
				$tmpBuf = explode('?', $requestItems[0]);
				// http://SERVER_HOST/RC
				if(count($tmpBuf) == 1)
				{
					$service = $requestItems[0];
					$moduleRequest = '';
				}
				else
				// http://SERVER_HOST/?REQUEST_ATTR
				if($tmpBuf[0] == '')
				{
					$service = 'index';
					$moduleRequest = $requestItems[0];
				}
				else
				// http://SERVER_HOST/RC?REQUEST_ATTR
				{
					$service = array_shift($tmpBuf);
					$moduleRequest = "?".implode('?', $tmpBuf);
				}

			}
		}
		else
		// http://SERVER_HOST/RC/REQUEST
		{
			$service = array_shift($requestItems);
			$moduleRequest = implode('/', $requestItems);
		}


		// INFO: Detect Main Service
		$state = FALSE;
		$state = $state || available("service.{$service}.{$service}");

		if ($state)
		{
			$this->_entryService = $service;

			define('__WORKING_ROOT__', SYS::$_cacheServicePath."/{$this->_entryService}", TRUE);
			chdir(__WORKING_ROOT__);

			$GLOBALS['service'] = $service;
			$GLOBALS['request'] = $moduleRequest;
			return;
		}

		$state = $state || available("modules.{$service}.{$service}");

		if($state)
		{
			$this->_entryService = $service;

			define('__WORKING_ROOT__', __ROOT__."modules/{$this->_entryService}", TRUE);
			chdir(__WORKING_ROOT__);

			$GLOBALS['service'] = $service;
			$GLOBALS['request'] = $moduleRequest;
			return;
		}

		$service = 'index';
		$state = $state || available("service.{$service}.{$service}");

		if ($state)
		{
			$this->_entryService = $service;
			$moduleRequest = "{$service}/{$moduleRequest}";

			define('__WORKING_ROOT__', SYS::$_cacheServicePath."/{$this->_entryService}", TRUE);
			chdir(__WORKING_ROOT__);

			$GLOBALS['service'] = $service;
			$GLOBALS['request'] = $moduleRequest;
			return;
		}

		$state = $state || available("modules.{$service}.{$service}");

		if ($state)
		{
			$this->_entryService = $service;
			$moduleRequest = "{$service}/{$moduleRequest}";

			define('__WORKING_ROOT__', __ROOT__."modules/{$this->_entryService}", TRUE);
			chdir(__WORKING_ROOT__);

			$GLOBALS['service'] = $service;
			$GLOBALS['request'] = $moduleRequest;
			return;
		}

		throw(new Exception("Cannot locate the target entry module!"));
	}

	public function __get_id() {

		return $this->_systemId;
	}
// endregion

// region [ System Workflow Control ]
	// INFO: In this version of system, there will be only one process instance in the system (main process)
	private $_processQueue = array();

	private function __forkProcess($service, $moduleRequest) {

		$systemIds = divide($this->_systemId);
		$processId = encode(array($service, uniqid("", TRUE)), $systemIds['extended']);
		$process = new PBProcess();

		$process->__processId = $processId;
		$process->__sysAPI = $this;
		$process->attachMainService($service, $moduleRequest);

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
// endregion

// region [ Module Control ]
	public function acquireModule($moduleName, $exception = TRUE) {

		$caller = $this->caller;
		if($caller['class'] != 'PBProcess')
			throw(new Exception("Calling an inaccessible function SYS::acquireServiceModule()."));

		$processId = $caller['object']->id;
		$processIds = divide($processId);
		$moduleId = encode(array($processId, $moduleName), $processIds['extended']);

		$servicePath = "service.{$moduleName}";

		$modulePath = "modules.{$moduleName}.{$moduleName}";
		$moduleStoragePath = "modules.{$moduleName}";

		$custServicePath = defined('__MODULE_PATH__') ? "service.".__MODULE_PATH__.".{$moduleName}" : NULL;
		$custServiceNestedPath = defined('__MODULE_PATH__') ? "service.".__MODULE_PATH__.".{$moduleName}.{$moduleName}" : NULL;

		$invokeModule = $moduleName;

		// INFO: If the requested module is existed in system core and services,
		// INFO: system core will be chosen first
		if(available($servicePath))
			using($servicePath);
		else
		if($custServicePath !== NULL && available($custServicePath))
			using($custServicePath);
		else
		if($custServiceNestedPath !== NULL && available($custServiceNestedPath))
			using($custServiceNestedPath);
		else
		if(available($modulePath))
			using($modulePath);
		else
		if(available($moduleStoragePath))
			using($moduleStoragePath);
		else
		{
			if($exception)
				throw(new Exception("Module doesn't exist!"));
			else
				return NULL;
		}

		$module = new $invokeModule();
		if(!is_subclass_of($module, 'PBModule'))
			throw(new Exception("Requested service is not a valid module"));

		$module->__moduleId = $moduleId;

		return $module;
	}

	public function validateChild($childrenId) {

		$childrenId = divide($childrenId);
		$selfId = $this->id;

		return $selfId['base'] === $childrenId['extended'];
	}
// endregion

// region [ Global APIs ]
	// INFO: The info function will return an object that contains information about current runtime environment
	// NOTE: The information provided by the system still have to be considered
	public static function Info()
	{
		if(is_null(SYS::$_SYS_INSTANCE)) return NULL;

		$runTimeInstance = SYS::$_SYS_INSTANCE;

		$infoStorage = new PBStorage();

		$infoStorage->request->method = $runTimeInstance->_incomingRecord['method'];
		$infoStorage->request->raw = $runTimeInstance->_incomingRecord['request'];
		$infoStorage->request->module = $runTimeInstance->_incomingRecord['service'];
		$infoStorage->request->time = $runTimeInstance->_incomingRecord['server']['REQUEST_TIME'];
		$infoStorage->request->contentType = array_key_exists('CONTENT_TYPE', $runTimeInstance->_incomingRecord['server']) ? $runTimeInstance->_incomingRecord['server']['CONTENT_TYPE'] : '';

		$infoStorage->client->accept->encoding = $runTimeInstance->_incomingRecord['server']['HTTP_ACCEPT_ENCODING'];
		$infoStorage->client->accept->format = $runTimeInstance->_incomingRecord['server']['HTTP_ACCEPT'];
		$infoStorage->client->accept->language = $runTimeInstance->_incomingRecord['server']['HTTP_ACCEPT_LANGUAGE'];
		$infoStorage->client->accept->charset = $runTimeInstance->_incomingRecord['server']['HTTP_ACCEPT_CHARSET'];
		$infoStorage->client->agent = $runTimeInstance->_incomingRecord['server']['HTTP_USER_AGENT'];
		$infoStorage->client->addr = $runTimeInstance->_incomingRecord['server']['REMOTE_ADDR'];

		$infoStorage->incoming->files = $runTimeInstance->_incomingRecord['files'];
		$infoStorage->incoming->args = $runTimeInstance->_incomingRecord['post'];


		return $infoStorage;
	}
// endregion
}