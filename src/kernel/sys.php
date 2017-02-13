<?php
	using( 'ext.base.array' );
	using( 'ext.base.misc'	);
	using( 'ext.base.objects' );

	class PBSysKernel extends PBObject {
	
		// region [ Boot Related ]
		private static $_cacheServicePath	= NULL;
		private static $_cachedRuntimeAttr	= NULL;

		public static function __imprint_constants() {
			static $initialized = FALSE;

			if($initialized) return;

			PBSysKernel::$_cacheServicePath  = $GLOBALS['servicePath'];
			PBSysKernel::$_cachedRuntimeAttr = array(
				'standalone'	=> @$GLOBALS['STANDALONE_EXEC']
			);
		}
		
		/** @var PBSysKernel */
		private static $_SYS_INSTANCE = NULL;
		public static function boot( $argv = NULL ) {

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

				s_define( '__DEFAULT_SERVICE_DEFINED__',		defined('DEFAULT_SERVICE'), TRUE, TRUE );
				s_define( 'DEFAULT_SERVICE',					'index',					TRUE );
				s_define( 'PITAYA_ENVIRONMENTAL_ATTACH_LEVEL',	0,							TRUE );



				// INFO: Keep booting
				PBSysKernel::$_SYS_INSTANCE = new PBSysKernel();
				PBSysKernel::$_SYS_INSTANCE->__initialize( $argv );
				PBSysKernel::$_SYS_INSTANCE->_process->run();

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
				/** @var PBModule */
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
					$errProcObj->execute( $e );
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
		
		private static $_bootResolver = NULL;
		public static function SetResolver( $callable ){
			if ( !is_callable($callable) ) return;
			self::$_bootResolver = $callable;
		}
		// endregion


		private function __construct() {}
		private function __initialize( $argv = NULL ) {

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
			$this->__judgeMainService( $argv );
			PBRequest::Request()->__initialize();



			// INFO: Define runtime constants
			define('__SERVICE__', $this->_entryService);



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
		
		
		private $_entryService		= NULL;
		private $_entryServiceParam	= NULL;
		public function __judgeMainService( $argv = NULL )
		{
			$service = $attributes = '';
			$moduleRequest = [];

			if ( SYS_EXEC_ENV == EXEC_ENV_HTTP ) {
			
				$reqURI		= @"{$_SERVER['REQUEST_URI']}";
				$request	= empty($reqURI) ? array() : explode('?', $reqURI);
				$resource	= preg_replace('/\/+/', '/', preg_replace('/^\/*|\/*$/', '', preg_replace('/\\\\/', '/', CAST( @array_shift( $request ), 'string no-trim' ) )));
				$attributes	= implode( '?', $request );



				$resource	 = ary_filter( empty($resource) ? array() : explode( '/', $resource ), function( $item ) {
					return urldecode( $item );
				});
				$attachPoint = @array_splice( $resource, 0, PITAYA_ENVIRONMENTAL_ATTACH_LEVEL );
				$GLOBALS[ 'attachPoint' ] = $attachPoint;
				$GLOBALS[ 'rawRequest' ] = implode('/', $resource) . (empty($attributes) ? '' : "?{$attributes}");



				$service = @array_shift( $resource );
				$moduleRequest = $resource;
			}
			else {
			
				$service = CAST( @array_shift($argv), 'string' );
				$moduleRequest = $argv;
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
			if ( CLI_ENV && !empty(self::$_cachedRuntimeAttr['standalone']) )
			{
				$scriptFilePath = self::$_cachedRuntimeAttr['standalone']['cwd'] . "/" . self::$_cachedRuntimeAttr['standalone']['script'];
				if ( is_readable($scriptFilePath) && is_file($scriptFilePath) )
				{
					if ( !empty($service) ) array_unshift( $moduleRequest, $service );
	
					$module = basename( self::$_cachedRuntimeAttr['standalone']['script'] );
					$ext = substr( $module, -4 );
					if ( in_array( $ext, array( '.php' ) ) ) $module = substr( $module, 0, -4 );
					$this->_entryService = "PBSystem.PBExecCtrl#PBVectorChain";
	
					define('__WORKING_ROOT__', self::$_cachedRuntimeAttr['standalone']['cwd']);
					define('__STANDALONE_MODULE__', $module );
					
					
	
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
		
						$GLOBALS['service'] = $service;
						$GLOBALS['request'] = $processReq( $moduleRequest, $attributes );
						return;
					}
				}
			}

			




			// INFO: Detect Main Service
			$serviceParts = @explode( '.', "{$service}" );
			$serviceName = @array_pop( $serviceParts );
			$state = available("service.{$serviceName}.{$serviceName}", FALSE);
			if ($state) {
				$this->_entryService = $serviceName;

				define('__WORKING_ROOT__', PBSysKernel::$_cacheServicePath."/{$this->_entryService}");

				$GLOBALS['service'] = $serviceName;
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

				$service = (defined('DEFAULT_SERVICE')) ? DEFAULT_SERVICE : '';
				$state = $state || available("service.{$service}.{$service}", FALSE);

				if ($state)
				{
					$this->_entryService = $service;

					define('__WORKING_ROOT__', PBSysKernel::$_cacheServicePath."/{$this->_entryService}");


					$GLOBALS['service'] = $service;
					$GLOBALS['request'] = $processReq( $moduleRequest, $attributes );
					return;
				}

				$state = $state || available("modules.{$service}.{$service}", FALSE);

				if ($state)
				{
					$this->_entryService = $service;

					define('__WORKING_ROOT__', __ROOT__."modules/{$this->_entryService}");


					$GLOBALS['service'] = $service;
					$GLOBALS['request'] = $processReq( $moduleRequest, $attributes );
					return;
				}

				$defaultBasis = $service;
			}
			// endregion

			throw(new Exception("Cannot locate default basis ( DEFAULT_SERVICE: {$defaultBasis} | RESOURCE: {$reqResource}) !"));
		}

		/** @var PBProcess */
		private $_process = NULL;
		private function __forkProcess($service, $moduleRequest, $custInit = NULL) {
			if ( $this->_process ) return;
			
			
			
			$this->_process = new PBProcess( $this );
			if ( is_callable($custInit) ) $custInit();

			chdir( __WORKING_ROOT__ );
			$this->_process->attachMainService($service, $this->_entryServiceParam, $moduleRequest);
		}
		
		
		private $_moduleSearchPaths	= [];
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
		public function acquireModule( $identifier, $instParam = NULL ) {
			static $allocCounter = 0;

			$moduleDesc = self::ParseModuleIdentifier( $identifier );
			if ( $moduleDesc === FALSE ) throw( new Exception( "Given target module identifier has syntax error!" ) );



			$package  = implode( '.', $moduleDesc[ 'package' ] );
			$module	  = $moduleDesc[ 'module' ];
			$class	  = empty($moduleDesc[ 'class' ]) ? $module : $moduleDesc[ 'class' ];
			$moduleId = sha1( "{$package}.{$module}.{$class}#{$allocCounter}" . microtime() );






			// INFO: Search path construction
			$moduleSearchPaths = [];

			if ( defined( '__SERVICE__' ) )
				$moduleSearchPaths[] = __STANDALONE_EXEC_MODE__ ? "working." : "service.";
			
			$moduleSearchPaths[] = "modules.";
			$moduleSearchPaths[] = "data.modules.";
			$moduleSearchPaths[] = "share.modules.";
			$moduleSearchPaths[] = ""; // Use global identifier

			if ( defined("MODULE_PATH") )
				$moduleSearchPaths[] = MODULE_PATH . ".";

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
			$moduleObj = new $invokeModule( $instParam );
			if ( !is_a($moduleObj, PBModule::class) ) throw(new Exception("Requested class is not a valid module"));

			$moduleObj->id = $moduleId;
			return $moduleObj;
		}
		
		
		
		
		
		
		private static function ParseModuleIdentifier( $moduleIdentifier )
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
	}
