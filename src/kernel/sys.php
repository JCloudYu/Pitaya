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
	private static $_cacheRandomCert = NULL;

	public static function __imprint_constants() {

		static $initialized = FALSE;

		if($initialized) return;

		SYS::$_cacheServicePath = $GLOBALS['servicePath'];
		SYS::$_cacheRandomCert = $GLOBALS['randomCert'];
	}
// endregion

// region [ System Instance ]
	private $_entryService = NULL;
	private $_systemId = NULL;

	// INFO: Singleton declaration
	private function __construct() {}

	// INFO: System workflow initialization
	private function __initialize() {

		try
		{
			// INFO: Preserve path of system container
			$sysEnvPath = path('root', 'sys.php');
			$serviceEnvPath = path("service", 'common.php');



			// INFO: Perform service decision and data initialization
			$this->__judgeMainService();
			PBRequest::Request();



			// INFO: Define runtime constants
			define('__SERVICE__', $this->_entryService, TRUE);

			// INFO: Generate the unique system execution Id
			$this->_systemId = encode(PBRequest::Request()->rawQuery);



			// INFO: Invoke pre-included files
			if (file_exists($sysEnvPath)) require_once($sysEnvPath);
			if (file_exists($serviceEnvPath)) require_once($serviceEnvPath);



			$this->__forkProcess($this->_entryService, PBRequest::Request()->query);
		}
		catch(PBException $e)
		{
			PBLog::SYSLog(print_r($e, TRUE));
		}
		catch(Exception $e)
		{
			PBLog::SYSLog(print_r($e, TRUE));
		}

	}

	public function __judgeMainService() {

		// INFO: Parse URL

		//SEC: REQUEST_URI Purge////////////////////////////////////////////////////////////////////////////////////////
		// INFO: Purge redundant separators from the REQUEST_URI
		// INFO: Example: http://SERVER_HOST////////RC//REQUEST/REQUEST///REQUEST?PARAMETERS=FDSAFDSAFDSADSA//
		// INFO: 		  will be purged into
		// INFO:		  http://SERVER_HOST/RC/REQUEST/REQUEST/REQUEST?PARAMETERS=FDSAFDSAFDSADSA
		$rawRequest = preg_replace('/\/+/', '/', preg_replace('/^\/*|\/*$/', '', preg_replace('/\\\\/', '/', @$_SERVER['REQUEST_URI'])));
		$GLOBALS['rawRequest'] = $rawRequest;
		//END SEC///////////////////////////////////////////////////////////////////////////////////////////////////////

		// INFO: Extract the requested module from request string
		$requestItems = explode('/', $rawRequest);;
		if(count($requestItems) == 1)
		{
			// http://SERVER_HOST/
			if($requestItems[0] == '')
			{
				$service = __DEFAULT_SERVICE__;
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
					$service = __DEFAULT_SERVICE__;
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


		// http://SERVER_HOST/RC/Update?attributes
		$requestMode = explode('?', @"{$requestItems[0]}");


		// INFO: Decide module maintenance mode
		switch (strtoupper($requestMode[0]))
		{
			case 'INSTALL':
				array_shift($requestItems);
				define('SERVICE_EXEC_MODE', 'INSTALL', TRUE);
				break;
			case 'UPDATE':
				array_shift($requestItems);
				define('SERVICE_EXEC_MODE', 'UPDATE', TRUE);
				break;
			case 'PATCH':
				array_shift($requestItems);
				define('SERVICE_EXEC_MODE', 'PATCH', TRUE);
				break;
			case 'UNINSTALL':
				array_shift($requestItems);
				define('SERVICE_EXEC_MODE', 'UNINSTALL', TRUE);
				break;
			default:
				define('SERVICE_EXEC_MODE', 'NORMAL', TRUE);
				break;
		}


		if (!__CASE_SENSITIVE_MODULE_NAME__) $service = strtolower($service);

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


		if (__DEFAULT_SERVICE_DEFINED__)
		{
			$moduleRequest = "{$service}/{$moduleRequest}";
			$service = __DEFAULT_SERVICE__;
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

			if ($state)
			{
				$this->_entryService = $service;

				define('__WORKING_ROOT__', __ROOT__."modules/{$this->_entryService}", TRUE);
				chdir(__WORKING_ROOT__);

				$GLOBALS['service'] = $service;
				$GLOBALS['request'] = $moduleRequest;
				return;
			}
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

		$this->_processQueue[$processId] = $process;

		$process->attachMainService($service, $moduleRequest);
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
	public function acquireModule($chiefModule, $moduleName = '', $exception = TRUE) {

		static $allocCounter = 0;

		if (!__CASE_SENSITIVE_MODULE_NAME__)
		{
			$chiefModule = strtolower($chiefModule);
			$moduleName = strtolower($moduleName);
		}

		$caller = $this->caller;
		if($caller['class'] != 'PBProcess')
			throw(new Exception("Calling an inaccessible function SYS::acquireServiceModule()."));

		$moduleName = (is_string($moduleName) && !empty($moduleName)) ? $moduleName : $chiefModule;

		$processId = $caller['object']->id;
		$processIds = divide($processId);
		$moduleId = encode(array($processId, $chiefModule, $moduleName, ++$allocCounter), $processIds['extended']);



		$modulePath = "modules.{$chiefModule}.{$moduleName}";
		$chiefModulePath = "modules.{$chiefModule}.{$chiefModule}";
		$moduleStoragePath = "modules.{$chiefModule}";



		$sharePath = "share.modules.{$chiefModule}.{$moduleName}";
		$shareSubPath = "share.modules.{$chiefModule}.{$chiefModule}";
		$shareStoragePath = "share.modules.{$chiefModule}";



		$servicePath = "service.{$chiefModule}";
		$serviceSubModulePath = "service.{$chiefModule}.{$moduleName}";
		$serviceDefaultPath = "service.{$chiefModule}.{$chiefModule}";



		$serviceInternalModulePath = defined('__MODULE_PATH__') ? "service.".__MODULE_PATH__.".{$chiefModule}" : NULL;
		$serviceInternalModuleSubModulePath = defined('__MODULE_PATH__') ? "service.".__MODULE_PATH__.".{$chiefModule}.{$moduleName}" : NULL;
		$serviceInternalModuleNestedPath = defined('__MODULE_PATH__') ? "service.".__MODULE_PATH__.".{$chiefModule}.{$chiefModule}" : NULL;

		$invokeModule = $moduleName;

		// INFO: If the requested module is existed in system core and services,
		// INFO: system core will be chosen first
		if(available($servicePath))
			using($servicePath);
		else
		if (available($serviceSubModulePath))
			using($serviceSubModulePath);
		else
		if(available($serviceDefaultPath))
			using($serviceDefaultPath);
		else
		if($serviceInternalModulePath !== NULL && available($serviceInternalModulePath))
			using($serviceInternalModulePath);
		else
		if($serviceInternalModuleSubModulePath !== NULL && available($serviceInternalModuleSubModulePath))
			using($serviceInternalModuleSubModulePath);
		else
		if($serviceInternalModuleNestedPath !== NULL && available($serviceInternalModuleNestedPath))
			using($serviceInternalModuleNestedPath);
		else
		if(available($sharePath))
			using($sharePath);
		else
		if(available($shareSubPath))
			using($shareSubPath);
		else
		if(available($shareStoragePath))
			using($shareStoragePath);
		else
		if(available($modulePath))
			using($modulePath);
		else
		if(available($chiefModulePath))
			using($chiefModulePath);
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

// region [ Process Management API ]
	/**
	 * Get the process with specified process id
	 *
	 * @param string|null $id the specified process id
	 *
	 * @return PBProcess | null the specified PBProcess object
	 */
	public static function Process($id = NULL)
	{
		return ($id === NULL) ? reset(self::$_SYS_INSTANCE->_processQueue) : @self::$_SYS_INSTANCE->_processQueue[$id];
	}
// endregion
}