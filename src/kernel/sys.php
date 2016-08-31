<?php
/*
 * File: sys.php
 * Created by JCloudYu.
 * DateTime: 13/2/9 PM4:05
 */
	using( 'ext.base.array' );
	using( 'ext.base.misc'	);
	using( 'ext.base.objects' );

	class PBSysKernel extends PBObject
	{
		// region [ System Boot Loader ]
		/**
		 * @var PBSysKernel
		 */
		private static $_SYS_INSTANCE = NULL;
		public static function boot($argc = 0, $argv = NULL) {

			// INFO: Avoid repeated initialization
			if ( PBSysKernel::$_SYS_INSTANCE ) return;



			try
			{
				if ( is_dir($servicePath = path('service')) )
				{
					// INFO: Read global service configurations
					$serviceConf = "{$servicePath}/config.php";
					if ( file_exists($serviceConf) ) require_once $serviceConf;
				}

				s_define('__DEFAULT_SERVICE_DEFINED__', defined('__DEFAULT_SERVICE__') || defined('DEFAULT_SERVICE'), TRUE, TRUE);
				s_define('__DEFAULT_SERVICE__', 'index', TRUE); // DEPRECATED: The constants will be removed in v2.0.0
				s_define('DEFAULT_SERVICE', 	'index', TRUE);

				s_define( 'PITAYA_ENVIRONMENTAL_ATTACH_LEVEL', 0, TRUE );



				// INFO: Keep booting
				PBSysKernel::$_SYS_INSTANCE = new PBSysKernel();
				PBSysKernel::$_SYS_INSTANCE->__initialize( $argc, $argv);
				PBSysKernel::$_SYS_INSTANCE->__jobDaemonRun();

				Termination::NORMALLY();
			}
			catch( Exception $e )
			{
				$errMsg = "Uncaught exception: " . $e->getMessage();
				$extMsg = "";

				if ( is_a( $e, 'PBException' ) )
				{
					$descriptor = $e->descriptor;

					if ( !empty($descriptor) )
						$errMsg .= "\nData:\n" . print_r( $descriptor, TRUE );
				}

				if ( __LOG_EXCEPTION__ === TRUE )
				{
					PBLog::SYSLog( print_r($e, TRUE), FALSE, "system.exception.pblog" );
					$extMsg = "See exception log for more information!";
				}

				PBLog::ERRLog( $errMsg );
				if (!empty($extMsg)) PBLog::ERRLog( $extMsg );



				if ( CLI_ENV )
				{
					PBStdIO::STDERR( $errMsg );
					if (!empty($extMsg)) PBStdIO::STDERR( $extMsg );
				}
				else
				{
					error_log( preg_replace( '/(\n|\s)+/', ' ', $errMsg) );
					if (!empty($extMsg)) error_log( preg_replace( '/(\n|\s)+/', ' ', $errMsg) );
				}



				// INFO: Check vailidaty of default error processing module
				$errProcObj = NULL;
				if ( defined( "ERROR_MODULE" ) )
				{
					try
					{
						$errProcObj = PBSysKernel::$_SYS_INSTANCE->acquireModule( ERROR_MODULE );
					}
					catch( Exception $e )
					{
						$errProcObj = NULL;
					}
				}


				if ( $errProcObj )
				{
					$errProcObj->prepareEvent( $e );
					$errProcObj->event( $e );
				}
				else
				if ( __THROW_EXCEPTION__ === TRUE )
				{
					throw( $e );
				}
				else
				if ( NET_ENV && __DEBUG_MODE__ )
				{
					if ( !headers_sent() )
					{
						header( "HTTP/1.1 500 Internal Server Error" );
						header( "Status: 500 Internal Server Error" );
						header( "Content-Type: text/plain; charset=utf8" );
						header( "Content-Length: " . strlen($errMsg) );
					}

					echo $errMsg;
				}



				Termination::WITH_STATUS(Termination::STATUS_ERROR);
			}
		}
		// endregion

		// region [ Path Control ]
		private static $_cacheServicePath	= NULL;
		private static $_cacheRandomCert	= NULL;
		private static $_cachedRuntimeAttr	= NULL;

		public static function __imprint_constants() {

			static $initialized = FALSE;

			if($initialized) return;

			PBSysKernel::$_cacheServicePath  = $GLOBALS['servicePath'];
			PBSysKernel::$_cacheRandomCert   = $GLOBALS['randomCert'];
			PBSysKernel::$_cachedRuntimeAttr = array(
				'standalone'	=> @$GLOBALS['STANDALONE_EXEC']
			);
		}
		// endregion

		// region [ System Instance ]
		private $_entryService		= NULL;
		private $_entryServiceParam	= NULL;
		private $_systemId			= NULL;
		private $_moduleSearchPaths	= array();

		// INFO: Singleton declaration
		private function __construct() {}

		// INFO: System workflow initialization
		private function __initialize($argc = 0, $argv = NULL) {

			// INFO: Preserve path of system container
			// DANGER: Make sure that this line will be excuted before __judgeMainService ( "service" will be different )
			$preprocessEnvPaths = [
				path( 'root',	 'boot.php' ),
				path( 'service', 'boot.php' ),
				path( 'share',	 'boot.php' )
			];
			
			$postprocessEnvPaths = [
				path( 'root',	 'sys.php'),
				path( 'root',	 'service.php'),
				path( "service", 'common.php'),
				path( 'share',	 'share.php' ),
				__STANDALONE_EXEC_MODE__ ? path( "working", "runtime.php" ): ""
			];



			foreach ( $preprocessEnvPaths as $path )
			{
				if (is_File($path) && is_readable($path))
				{
					chdir( dirname($path) );
					require_once $path;
				}
			}

			// INFO: Perform service decision and data initialization
			$this->__judgeMainService($argc, $argv);
			PBRequest::Request()->__initialize();



			// INFO: Define runtime constants
			define('__SERVICE__', $this->_entryService);



			// INFO: Generate the unique system execution Id
			$this->_systemId = encode(PBRequest::Request()->rawQuery);



			// INFO: Bring up the main process
			$this->__forkProcess($this->_entryService, PBRequest::Request()->query, function() use(&$postprocessEnvPaths) {
				foreach ( $postprocessEnvPaths as $path )
				{
					if (is_File($path) && is_readable($path))
					{
						chdir( dirname($path) );
						require_once $path;
					}
				}
			});
		}

		public function __judgeMainService($argc = 0, $argv = NULL)
		{
			$service = $attributes = '';
			$moduleRequest = array();

			if ( SYS_WORKING_ENV == SYS_ENV_NET )
			{
				$reqURI		= @"{$_SERVER['REQUEST_URI']}";
				$request	= empty($reqURI) ? array() : explode('?', $reqURI);
				$resource	= preg_replace('/\/+/', '/', preg_replace('/^\/*|\/*$/', '', preg_replace('/\\\\/', '/', CAST( @array_shift( $request ), 'string no-trim' ) )));
				$attributes	= implode( '?', $request );



				$resource	= ary_filter( empty($resource) ? array() : explode( '/', $resource ), function( $item ) {
					return urldecode( $item );
				});
				@array_splice( $resource, 0, PITAYA_ENVIRONMENTAL_ATTACH_LEVEL );
				$GLOBALS[ 'rawRequest' ] = implode('/', $resource) . (empty($attributes) ? '' : "?{$attributes}");



				$service = @array_shift( $resource );
				$moduleRequest = $resource;
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


			$processReq = function( $moduleRequest, $attributes ) {
				if ( CLI_ENV ) return $moduleRequest;

				$moduleRequest	= implode('/', $moduleRequest);
				$attributes		= empty($attributes) ? '' : "?{$attributes}";
				return "{$moduleRequest}{$attributes}";
			};




			// region [ Find the default basis ]
			// INFO: If cli and standalone script has been assigned
			// MARK: Developer customizable only
			if ( CLI_ENV )
			{
				$scriptFilePath = self::$_cachedRuntimeAttr['standalone']['cwd'] . "/" . self::$_cachedRuntimeAttr['standalone']['script'];
				if ( is_readable($scriptFilePath) )
				{
					if ( !empty($service) ) array_unshift( $moduleRequest, $service );
	
					$module = basename( self::$_cachedRuntimeAttr['standalone']['script'] );
					$ext = substr( $module, -4 );
					if ( in_array( $ext, array( '.php' ) ) ) $module = substr( $module, 0, -4 );
					$this->_entryService		= "PBSystem.PBExecCtrl#PBVectorChain";
	
					define('__WORKING_ROOT__', self::$_cachedRuntimeAttr['standalone']['cwd']);
					define('__STANDALONE_MODULE__', $module );
					self::DecideExecMode( $moduleRequest );
	
					$GLOBALS['service'] = $module;
					$GLOBALS['request'] = $processReq( $moduleRequest, $attributes );
					return;
				}
			}



			// INFO: Customized service decision logics
			if ( defined( 'DEFAULT_BOOT_RESOLVER' ) )
			{
				try{
					$module = $this->acquireModule( DEFAULT_BOOT_RESOLVER );
				}
				catch( Exception $e ){
					throw(new Exception( "Target default boot resolver is invalid!" ));
				}

				$result = $module->coreResolve( $service, $moduleRequest, $attributes );
				if ( !empty($result) )
				{
					$service		= $result[ 'service' ];
					$moduleRequest	= $result[ 'request' ];
					$workingDir		= $result[ 'workingRoot' ];
					
					
					
					// INFO: Detect Main Service
					$state = available("{$service}", FALSE);
					if ($state) {
						$this->_entryService = $service;
		
						define( '__WORKING_ROOT__', is_dir($workingDir) ? $workingDir : sys_get_temp_dir());
						self::DecideExecMode( $moduleRequest );
		
						$GLOBALS['service'] = $service;
						$GLOBALS['request'] = $processReq( $moduleRequest, $attributes );
						return;
					}
				}
			}

			




			// INFO: Detect Main Service
			$serviceName = array_pop( explode( '.', "{$service}" ) );
			$state = available("service.{$serviceName}.{$serviceName}", FALSE);
			if ($state) {
				$this->_entryService = $service;

				define('__WORKING_ROOT__', PBSysKernel::$_cacheServicePath."/{$this->_entryService}");
				self::DecideExecMode( $moduleRequest );

				$GLOBALS['service'] = $service;
				$GLOBALS['request'] = $processReq( $moduleRequest, $attributes );
				return;
			}






			// INFO: Default basis chaining mode
			// MARK: Developer customizable only
			s_define( 'DEFAULT_BASIS_CHAIN_DESCRIPTOR',		'', TRUE );
			s_define( 'DEFAULT_BASIS_CHAIN_WORKING_DIR',	'', TRUE );
			
			$basisChain = @json_decode( @file_get_contents( path( 'defaults', 'basis-chain.json' ) ), TRUE );
			$basisChainPath	= DEFAULT_BASIS_CHAIN_DESCRIPTOR;
			if ( !empty( $basisChainPath ) && is_file( $basisChainPath ) )
			{
				$custChain = @json_decode( @file_get_contents($basisChainPath), TRUE );
				$basisChain = array_merge( $basisChain, $custChain );
			}
			if ( !empty($basisChain[ $service ]) )
			{
				$workingDir = DEFAULT_BASIS_CHAIN_WORKING_DIR;
				$this->_entryService		= "PBSystem.PBExecCtrl#PBBasisChain";
				$this->_entryServiceParam	= $basisChain[$service];

				define( '__WORKING_ROOT__', is_dir($workingDir) ? $workingDir : sys_get_temp_dir());
				self::DecideExecMode( $moduleRequest );

				$GLOBALS['service'] = $service;
				$GLOBALS['request'] = $processReq( $moduleRequest, $attributes );
				return;
			}






			// INFO: If default basis is defined
			// MARK: Developer customizable only
			$defaultBasis = "NULL"; $reqResource = "{$service}";
			if (__DEFAULT_SERVICE_DEFINED__)
			{
				if ( !empty($service) ) array_unshift($moduleRequest, $service);

				// DEPRECATED: The constants __DEFAULT_SERVICE__ will be removed in v2.0.0
				$service = (defined('DEFAULT_SERVICE')) ? DEFAULT_SERVICE : __DEFAULT_SERVICE__;
				$state = $state || available("service.{$service}.{$service}", FALSE);

				if ($state)
				{
					$this->_entryService = $service;

					define('__WORKING_ROOT__', PBSysKernel::$_cacheServicePath."/{$this->_entryService}");
					self::DecideExecMode( $moduleRequest );


					$GLOBALS['service'] = $service;
					$GLOBALS['request'] = $processReq( $moduleRequest, $attributes );
					return;
				}

				$state = $state || available("modules.{$service}.{$service}", FALSE);

				if ($state)
				{
					$this->_entryService = $service;

					define('__WORKING_ROOT__', __ROOT__."modules/{$this->_entryService}");
					self::DecideExecMode( $moduleRequest );


					$GLOBALS['service'] = $service;
					$GLOBALS['request'] = $processReq( $moduleRequest, $attributes );
					return;
				}

				$defaultBasis = $service;
			}
			// endregion

			throw(new Exception("Cannot locate default basis ( DEFAULT_SERVICE: {$defaultBasis} | RESOURCE: {$reqResource}) !"));
		}

		private static function DecideExecMode( &$moduleRequest )
		{
			if ( strtoupper(@"{$moduleRequest[0]}") == 'EVENT' )
			{
				array_shift($moduleRequest);
				define( 'SERVICE_EXEC_MODE', 'EVENT');

				define( 'EVENT_CHAIN',	TRUE);
				define( 'SHELL_CHAIN',	FALSE);
				define( 'CORS_CHAIN',	FALSE);
				define( 'NORMAL_CHAIN',	FALSE);
			}
			else
			if ( SYS_EXEC_ENV == EXEC_ENV_CLI )
			{
				define('SERVICE_EXEC_MODE', 'SHELL');

				define( 'EVENT_CHAIN',	FALSE);
				define( 'SHELL_CHAIN',	TRUE);
				define( 'CORS_CHAIN',	FALSE);
				define( 'NORMAL_CHAIN',	FALSE);
			}
			else
			if ( REQUESTING_METHOD == "OPTIONS" && __ENABLE_CORS_MODE__ )
			{
				define('SERVICE_EXEC_MODE', 'CORS');

				define( 'EVENT_CHAIN',	FALSE);
				define( 'SHELL_CHAIN',	FALSE);
				define( 'CORS_CHAIN',	TRUE);
				define( 'NORMAL_CHAIN',	FALSE);
			}
			else
			{
				define('SERVICE_EXEC_MODE', 'NORMAL');

				define( 'EVENT_CHAIN',	FALSE);
				define( 'SHELL_CHAIN',	FALSE);
				define( 'CORS_CHAIN',	FALSE);
				define( 'NORMAL_CHAIN',	TRUE);
			}
		}

		public function __get_id() {

			return $this->_systemId;
		}
		// endregion

		// region [ System Workflow Control ]
		// INFO: In this version of system, there will be only one process instance in the system (main process)
		private $_processQueue = NULL;

		private function __forkProcess($service, $moduleRequest, $custInit = NULL) {
			if ( $this->_processQueue ) return;


			$systemIds = divide($this->_systemId);
			$processId = encode(array($service, uniqid("", TRUE)), $systemIds['extended']);
			$process = new PBProcess();

			$process->__processId = $processId;
			$process->__sysAPI = $this;

			$this->_processQueue = $process;


			if ( is_callable($custInit) ) $custInit();

			chdir( __WORKING_ROOT__ );
			$process->attachMainService($service, $this->_entryServiceParam, $moduleRequest);
		}

		private function __jobDaemonRun() {
			$result = $this->_processQueue->run();
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


		/**
		 * Parse module identifier according to following syntax
		 * 		 Syntax => leadingModule.subModule#class
		 * 				=> module#class
		 *
		 * @param $moduleIdentifier
		 *
		 * @return array|bool
		 */
		public static function ParseModuleIdentifier( $moduleIdentifier )
		{
			$moduleIdentifier = trim( "{$moduleIdentifier}" );
			if ( empty($moduleIdentifier) ) return FALSE;



			$packages	= explode( '.',  "{$moduleIdentifier}" );
			$packages	= ary_filter( $packages, NULL, FALSE );
			$module		= array_pop( $packages );



			$module = explode( '#', $module);
			if ( count( $module ) > 2 ) return FALSE;

			$class	= trim(@"{$module[1]}");
			$module	= trim("{$module[0]}");
			if ( empty( $module ) ) return FALSE;


			return array(
				'package'	=> $packages,
				'module'	=> $module,
				'class'		=> $class
			);
		}

		public function acquireModule( $identifier, $instParam = NULL ) {

			static $allocCounter = 0;
			$caller = $this->caller;

			if ( !in_array($caller['class'], array('PBProcess', 'PBSysKernel')) )
				throw(new Exception("Calling an inaccessible function PBSysKernel::acquireModule()."));



			$moduleDesc = self::ParseModuleIdentifier( $identifier );

			if ( $moduleDesc === FALSE )
				throw( new Exception( "Given target module identifier has syntax error!" ) );

			$package	= implode( '.', $moduleDesc[ 'package' ] );
			$module		= $moduleDesc[ 'module' ];
			$class		= empty($moduleDesc[ 'class' ]) ? $module : $moduleDesc[ 'class' ];

			$processId	= $caller['object']->id;
			$processIds = divide( $processId );
			$moduleId	= encode( array($processId, $package, $module, ++$allocCounter), $processIds['extended'] );






			// INFO: Search path construction
			$moduleSearchPaths = array();

			if ( defined( '__SERVICE__' ) )
				$moduleSearchPaths[] = __STANDALONE_EXEC_MODE__ ? "working." : "service.";
			
			$moduleSearchPaths[] = "modules.";
			$moduleSearchPaths[] = "data.modules.";
			$moduleSearchPaths[] = "share.modules.";
			$moduleSearchPaths[] = ""; // Use global identifier

			if ( defined("MODULE_PATH") )
				$moduleSearchPaths[] = MODULE_PATH . ".";
			else
			if ( defined("__MODULE_PATH__") )	// DEPRECATED: The constants will be removed in v2.0.0
			{
				if ( defined( '__SERVICE__' ) )
					$moduleSearchPaths[] = "service." . __MODULE_PATH__ . ".";
			}

			foreach ( $this->_moduleSearchPaths as $path ) $moduleSearchPaths[] = "{$path}.";






			// INFO: Candidate paths
			$candidateComps = array();
			$candidateComps[] = $module;
			$candidateComps[] = "{$module}.{$module}";



			$hitPath = '';
			$subPkg	 = (!empty($package)) ? "{$package}." : "";
			foreach ( $moduleSearchPaths as $searchPath )
			{
				$searchPath = "{$searchPath}{$subPkg}";
				foreach ( $candidateComps as $component )
				{
					$path = "{$searchPath}{$component}";

					if ( available($path) )
					{
						using($path);
						$hitPath = $path;
					}
				}
			}



			if ( empty( $hitPath ) || !class_exists( $class ) )
				throw(new Exception("Module {$class} doesn't exist!"));



			$invokeModule = "{$class}";
			$moduleObj	  = new $invokeModule( $instParam );
			if(!is_subclass_of($moduleObj, 'PBModule'))
				throw(new Exception("Requested class is not a valid module"));

			$moduleObj->__moduleId = $moduleId;

			return $moduleObj;
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
		public static function Process()
		{
			return self::$_SYS_INSTANCE->_processQueue;
		}
		// endregion
	}
