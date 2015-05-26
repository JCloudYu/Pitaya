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

		try
		{
			if ( is_dir($servicePath = path('service')) )
			{
				// INFO: Read global service configurations
				$serviceConf = "{$servicePath}/config.php";
				if ( file_exists($serviceConf) ) require_once $serviceConf;
			}

			s_define('__DEFAULT_SERVICE_DEFINED__', defined('__DEFAULT_SERVICE__') || defined('DEFAULT_SERVICE'), TRUE, TRUE);
			s_define('__DEFAULT_SERVICE__', 'index', TRUE); // DEPRECATED: The constants will be removed in v1.4.0
			s_define('DEFAULT_SERVICE', 	'index', TRUE);



			// INFO: Keep booting
			SYS::$_SYS_INSTANCE = new SYS();
			SYS::$_SYS_INSTANCE->__initialize($argc, $argv);
			SYS::$_SYS_INSTANCE->__jobDaemonRun();

			Termination::NORMALLY();
		}
		catch(Exception $e)
		{
			if ( __LOG_EXCEPTION__ === TRUE )
				PBLog::SYSLog(print_r($e, TRUE), FALSE, "system.exception.log");

			if ( __THROW_EXCEPTION__ === TRUE )
				throw($e);
			else
			{
				if ( SYS_EXEC_ENV == EXEC_ENV_CLI )
				{
					PBStdIO::STDERR("Uncaught exception: " . $e->getMessage());

					if ( __LOG_EXCEPTION__ === TRUE )
					{
						PBStdIO::STDERR("See log file for more information");
					}
				}
				else
				if ( (SYS_EXEC_ENV == EXEC_ENV_HTTP) && (!headers_sent()) )
				{
					header("HTTP/1.1 500 Internal Server Error");
					header("Status: 500 Internal Server Error");
				}

				Termination::WITH_STATUS(Termination::STATUS_ERROR);
			}
		}
	}
// endregion

// region [ Path Control ]
	private static $_cacheServicePath = NULL;
	private static $_cacheRandomCert  = NULL;

	public static function __imprint_constants() {

		static $initialized = FALSE;

		if($initialized) return;

		SYS::$_cacheServicePath = $GLOBALS['servicePath'];
		SYS::$_cacheRandomCert  = $GLOBALS['randomCert'];
	}
// endregion

// region [ System Instance ]
	private $_entryService		= NULL;
	private $_systemId			= NULL;
	private $_moduleSearchPaths	= array();

	// INFO: Singleton declaration
	private function __construct() {}

	// INFO: System workflow initialization
	private function __initialize($argc = 0, $argv = NULL) {
		// INFO: Preserve path of system container
		$sysEnvPath		= path('root', 'sys.php');
		$serviceEnvPath = path("service", 'common.php'); // NOTE: This line should executed before __judgeMainService



		// INFO: Perform service decision and data initialization
		$this->__judgeMainService($argc, $argv);
		PBRequest::Request();



		// INFO: Define runtime constants
		define('__SERVICE__', $this->_entryService, TRUE);

		// INFO: Generate the unique system execution Id
		$this->_systemId = encode(PBRequest::Request()->rawQuery);

		$this->__forkProcess($this->_entryService, PBRequest::Request()->query, function() use($sysEnvPath, $serviceEnvPath) {

				if (file_exists($sysEnvPath))
				{
					chdir( dirname($sysEnvPath) );
					require_once($sysEnvPath);
				}

				if (file_exists($serviceEnvPath))
				{
					chdir( dirname($sysEnvPath) );
					require_once($serviceEnvPath);
				}
			});
	}

	public function __judgeMainService($argc = 0, $argv = NULL)
	{
		if ( SYS_WORKING_ENV == SYS_ENV_NET )
		{
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
					$service = '';
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
						$service = '';
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

			$moduleRequest = explode('/', $moduleRequest);
		}
		else
		{
			$service = TO(@array_shift($argv), 'string');
			$moduleRequest = $argv;
		}

		if ( strtoupper($service) == 'EVENT' )
		{
			$service = '';
			array_unshift($moduleRequest, 'EVENT');
		}




		// INFO: Detect Main Service
		$state = FALSE;
		$state = $state || available("service.{$service}.{$service}", FALSE);

		if ($state)
		{
			$this->_entryService = $service;

			define('__WORKING_ROOT__', SYS::$_cacheServicePath."/{$this->_entryService}", TRUE);

			if ( strtoupper(@"{$moduleRequest[0]}") == 'EVENT' )
			{
				array_shift($moduleRequest);
				define('SERVICE_EXEC_MODE', 'EVENT', TRUE);
			}
			else
			if ( SYS_EXEC_ENV == EXEC_ENV_CLI )
				define('SERVICE_EXEC_MODE', 'SHELL', TRUE);
			else
				define('SERVICE_EXEC_MODE', 'NORMAL', TRUE);


			$GLOBALS['service'] = $service;
			$GLOBALS['request'] = (SYS_WORKING_ENV == SYS_ENV_NET) ? implode('/', $moduleRequest) : $moduleRequest;
			return;
		}

		if (__DEFAULT_SERVICE_DEFINED__)
		{
			if ( !empty($service) ) array_unshift($moduleRequest, $service);

			// DEPRECATED: The constants __DEFAULT_SERVICE__ will be removed in v1.4.0
			$service = (defined('DEFAULT_SERVICE')) ? DEFAULT_SERVICE : __DEFAULT_SERVICE__;
			$state = $state || available("service.{$service}.{$service}", FALSE);

			if ($state)
			{
				$this->_entryService = $service;

				define('__WORKING_ROOT__', SYS::$_cacheServicePath."/{$this->_entryService}", TRUE);

				if ( strtoupper(@"{$moduleRequest[0]}") == 'EVENT' )
				{
					array_shift($moduleRequest);
					define('SERVICE_EXEC_MODE', 'EVENT', TRUE);
				}
				else
				if ( SYS_EXEC_ENV == EXEC_ENV_CLI )
					define('SERVICE_EXEC_MODE', 'SHELL', TRUE);
				else
					define('SERVICE_EXEC_MODE', 'NORMAL', TRUE);


				$GLOBALS['service'] = $service;
				$GLOBALS['request'] = (SYS_WORKING_ENV == SYS_ENV_NET) ? implode('/', $moduleRequest) : $moduleRequest;
				return;
			}

			$state = $state || available("modules.{$service}.{$service}", FALSE);

			if ($state)
			{
				$this->_entryService = $service;

				define('__WORKING_ROOT__', __ROOT__."modules/{$this->_entryService}", TRUE);

				if ( strtoupper(@"{$moduleRequest[0]}") == 'EVENT' )
				{
					array_shift($moduleRequest);
					define('SERVICE_EXEC_MODE', 'EVENT', TRUE);
				}
				else
				if ( SYS_EXEC_ENV == EXEC_ENV_CLI )
					define('SERVICE_EXEC_MODE', 'SHELL', TRUE);
				else
					define('SERVICE_EXEC_MODE', 'NORMAL', TRUE);


				$GLOBALS['service'] = $service;
				$GLOBALS['request'] = (SYS_WORKING_ENV == SYS_ENV_NET) ? implode('/', $moduleRequest) : $moduleRequest;
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

	private function __forkProcess($service, $moduleRequest, $custInit = NULL) {

		$systemIds = divide($this->_systemId);
		$processId = encode(array($service, uniqid("", TRUE)), $systemIds['extended']);
		$process = new PBProcess();

		$process->__processId = $processId;
		$process->__sysAPI = $this;

		$this->_processQueue[$processId] = $process;


		if ( is_callable($custInit) ) $custInit();

		chdir( __WORKING_ROOT__ );
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
	public function addModuleSearchPath( $package = "" )
	{
		if ( empty( $package ) ) return FALSE;

		$hash = md5( ($path = trim($package)) );
		if ( isset( $this->_moduleSearchPaths[$hash] ) ) return TRUE;


		if ( !is_dir( path( $path ) ) ) return FALSE;
		$this->_moduleSearchPaths[$hash] = $path;
	}

	public function removeModuleSearchPath( $package )
	{
		if ( empty( $package ) ) return FALSE;

		$hash = md5( ($path = trim($package)) );
		if ( !isset( $this->_moduleSearchPaths[$hash] ) ) return TRUE;

		unset( $this->_moduleSearchPaths[$hash] );
	}

	public function acquireModule($chiefModule, $moduleName = '', $exception = TRUE) {

		static $allocCounter = 0;

		$caller = $this->caller;
		if($caller['class'] != 'PBProcess')
			throw(new Exception("Calling an inaccessible function SYS::acquireServiceModule()."));

		$moduleName = ( is_string($moduleName) && !empty($moduleName) ) ? $moduleName : $chiefModule;

		$processId = $caller['object']->id;
		$processIds = divide($processId);
		$moduleId = encode(array($processId, $chiefModule, $moduleName, ++$allocCounter), $processIds['extended']);

		$moduleSearchPaths = array();

		$moduleSearchPaths[] = "modules.{$chiefModule}";
		$moduleSearchPaths[] = "modules.{$chiefModule}.{$moduleName}";

		$moduleSearchPaths[] = "share.modules.{$chiefModule}";
		$moduleSearchPaths[] = "share.modules.{$chiefModule}.{$moduleName}";

		$moduleSearchPaths[] = "data.modules.{$chiefModule}";
		$moduleSearchPaths[] = "data.modules.{$chiefModule}.{$moduleName}";

		$moduleSearchPaths[] = "service.{$chiefModule}";
		$moduleSearchPaths[] = "service.{$chiefModule}.{$moduleName}";


		if ( defined("MODULE_PATH") )
			$custModulePath = MODULE_PATH;
		else
		if ( defined("__MODULE_PATH__") )	// DEPRECATED: The constants will be removed in v1.4.0
			$custModulePath = "service." . __MODULE_PATH__;
		else
			$custModulePath = NULL;

		if ( !empty($custModulePath) )
		{
			$moduleSearchPaths[] = "{$custModulePath}.{$chiefModule}";
			$moduleSearchPaths[] = "{$custModulePath}.{$chiefModule}.{$moduleName}";
		}



		foreach ( $this->_moduleSearchPaths as $path )
		{
			$moduleSearchPaths[] = "{$path}.{$chiefModule}";
			$moduleSearchPaths[] = "{$path}.{$chiefModule}.{$moduleName}";
		}



		$hitPath = NULL;
		foreach ( $moduleSearchPaths as $path )
		{
			if ( empty($path) || !available($path) ) continue;

			using($path);
			$hitPath = $path;
		}

		if ( empty($hitPath) )
		{
			if ( $exception )
				throw(new Exception("Module doesn't exist!"));
			else
				return NULL;
		}



		$invokeModule = "{$moduleName}";
		$module		  = new $invokeModule();
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
