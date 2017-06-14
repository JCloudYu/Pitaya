<?php
	final class PBKernel extends PBObject {
		/** @var PBKernelAccessor */
		private static $_SYS_ACCESS_INTERFACE = NULL;
		public static function SYS() {
			return self::$_SYS_ACCESS_INTERFACE;
		}
	
		// region [ Boot Related ]
		private static $_cacheServicePath	= NULL;
		private static $_cachedRuntimeAttr	= NULL;

		public static function __imprint_constants() {
			static $initialized = FALSE;

			if($initialized) return;

			PBKernel::$_cacheServicePath  = BASIS_ROOT;
			PBKernel::$_cachedRuntimeAttr = array(
				'standalone' => @$GLOBALS['STANDALONE_EXEC']
			);
		}
		
		/** @var PBKernel */
		private static $_SYS_INSTANCE = NULL;
		public static function boot( $argv = NULL ) {

			// INFO: Avoid repeated initialization
			if ( PBKernel::$_SYS_INSTANCE ) return;



			try
			{
				if ( is_dir($servicePath = path('service')) )
				{
					// INFO: Read global service configurations
					$serviceConf = "{$servicePath}/config.php";
					if ( file_exists($serviceConf) ) require_once $serviceConf;
				}

				s_define( '__DEFAULT_SERVICE_DEFINED__',		defined('DEFAULT_SERVICE'), TRUE, TRUE );
				s_define( 'DEFAULT_SERVICE',					IS_CLI_ENV ? 'cli' : 'index',	TRUE );
				s_define( 'PITAYA_ENVIRONMENTAL_ATTACH_LEVEL',	0,								TRUE );



				// INFO: Keep booting
				PBKernel::$_SYS_INSTANCE = new PBKernel();
				PBKernel::$_SYS_ACCESS_INTERFACE = new PBKernelAccessor( PBKernel::$_SYS_INSTANCE );
				
				PBKernel::$_SYS_INSTANCE->__initialize( $argv );
				PBKernel::$_SYS_INSTANCE->_process->run();

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
					PBLog::SYSLog( print_r($e, TRUE), "system.exception.pblog" );
					$extMsg = "See exception log for more information!";
				}

				PBLog::ERRLog( $errMsg );
				if (!empty($extMsg)) PBLog::ERRLog( $extMsg );



				if ( IS_CLI_ENV )
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
						$errProcObj = PBKernel::$_SYS_INSTANCE->acquireModule( ERROR_MODULE );
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
				if ( IS_HTTP_ENV && __DEBUG_MODE__ )
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

		// region [ Boot Control ]
		private function __construct() {}
		private function __initialize( $argv = NULL ) {

			// INFO: Preserve path of system container
			// DANGER: Make sure that this line will be executed before __judgeMainService ( "service" will be different )
			$preprocessEnvPaths = [
				path( 'root',	 'boot.php' ),
				path( 'service', 'boot.php' ),
				path( 'share',	 'boot.php' ),
				PITAYA_STANDALONE_EXECUTION_MODE ? path( 'working', 'boot.php' ) : ''
			];
			foreach ( $preprocessEnvPaths as $path ) {
				if ( is_file($path) && is_readable($path) ) {
					require_once $path;
				}
			}



			// INFO: Perform service decision and data initialization
			$this->__judgeMainService( $argv );
			
			
			// region [ PBPathResolver Customize Initialization ]
			$extendPath = @$GLOBALS[ 'extPath' ];
			if ( defined( 'EXTENDED_PACKAGES' ) ) $extendPath = EXTENDED_PACKAGES;
			PBPathResolver::Register( is_array($extendPath) ? $extendPath : [] );
			PBPathResolver::Purge();
			// endregion
			
			
			PBRequest()->__initialize()->parseQuery(function_exists( 'default_query_parser' ) ? 'default_query_parser' : NULL);



			// INFO: Define runtime constants
			define( '__BASIS__', $this->_entryBasis );
			define( '__SERVICE__', __BASIS__ );
			



			// INFO: Bring up the main process
			$this->__forkProcess($this->_entryBasis);
		}
		
		private $_entryBasis = NULL;
		private function __judgeMainService( $argv = NULL ) {
			$service = $attributes = $fragment = '';
			$moduleRequest = [];
			
			if ( IS_HTTP_ENV ) {
			
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


			if ( is_callable(self::$_bootResolver) ) {
				// ISSUE: Merge boot resolvers after rewriting boot path decisition logics
				//call_user_func( self::$_bootResolver, $service, $moduleRequest );
			}



			$processReq = function( $moduleRequest, $attributes ) {
				if ( IS_CLI_ENV ) return $moduleRequest;

				$moduleRequest	= implode('/', $moduleRequest);
				$attributes		= empty($attributes) ? '' : "?{$attributes}";
				return "{$moduleRequest}{$attributes}";
			};




			// region [ Find the default basis ]
			// INFO: If cli and standalone script has been assigned
			// MARK: Developer customizable only
			if ( IS_CLI_ENV && PITAYA_STANDALONE_EXECUTION_MODE ) {
				$scriptFilePath = self::$_cachedRuntimeAttr['standalone']['cwd'] . "/" . self::$_cachedRuntimeAttr['standalone']['script'];
				if ( is_readable($scriptFilePath) && is_file($scriptFilePath) )
				{
					if ( !empty($service) ) array_unshift( $moduleRequest, $service );
	
					$module = basename( self::$_cachedRuntimeAttr['standalone']['script'] );
					$ext = substr( $module, -4 );
					if ( in_array( $ext, array( '.php' ) ) ) $module = substr( $module, 0, -4 );
					$this->_entryBasis = "PBSystem.PBExecCtrl#PBVectorChain";
	
					define( 'WORKING_ROOT', self::$_cachedRuntimeAttr['standalone']['cwd'] );
					define( '__WORKING_ROOT__', WORKING_ROOT ); // DEPRECATED: __WORKING_ROOT__ will be deprecated in 2.5.0
					define( '__STANDALONE_MODULE__', $module );
					
					
	
					$GLOBALS['service'] = $module;
					$GLOBALS['request'] = $processReq( $moduleRequest, $attributes );
					return;
				}
			}



			// INFO: Customized service decision logic
			if ( is_callable(self::$_bootResolver) ) {
				$resolver = self::$_bootResolver;
				$result = call_user_func($resolver, $service, $moduleRequest, $attributes, $fragment);
				if ( !empty($result) ) {
					$result = object($result);
				
					$service		= @$result->basis ?: @$result->service ?: $service;
					$moduleRequest	= @$result->resource ?: @$result->request ?: $moduleRequest;
					$workingDir		= @$result->root ?: @$result->workingRoot ?: '';
					
					
					
					// INFO: Detect Main Service
					$state = file_exists( path( "{$service}" ) . ".php" );
					if ($state) {
						$this->_entryBasis = $service;
		
						define( 'WORKING_ROOT', is_dir($workingDir) ? $workingDir : sys_get_temp_dir());
						define( '__WORKING_ROOT__', WORKING_ROOT );  // DEPRECATED: __WORKING_ROOT__ will be deprecated in 2.5.0
		
						$GLOBALS['service'] = $service;
						$GLOBALS['request'] = $processReq( $moduleRequest, $attributes );
						return;
					}
				}
			}
			else
			if ( defined( 'DEFAULT_BOOT_RESOLVER' ) ) {
				try{
					$module = $this->acquireModule( DEFAULT_BOOT_RESOLVER );
				}
				catch( Exception $e ){
					throw(new Exception( "Target default boot resolver is invalid!" ));
				}

				if ( !class_implements($module, FALSE) ) {
					throw(new Exception( "Target boot resolver doesn't implements PBIBootResolver!" ));
				}
				
				$result = $module->resolve( $service, $moduleRequest, $attributes, $fragment );
				if ( !empty($result) ) {
					$result = object($result);
				
					$service		= @$result->basis ?: @$result->service ?: $service;
					$moduleRequest	= @$result->resource ?: @$result->request ?: $moduleRequest;
					$workingDir		= @$result->root ?: @$result->workingRoot ?: '';
					
					
					
					// INFO: Detect Main Service
					$state = file_exists( path( "{$service}" ) . ".php" );
					if ($state) {
						$this->_entryBasis = $service;
		
						define( 'WORKING_ROOT', is_dir($workingDir) ? $workingDir : sys_get_temp_dir());
						define( '__WORKING_ROOT__', WORKING_ROOT );  // DEPRECATED: __WORKING_ROOT__ will be deprecated in 2.5.0
		
						$GLOBALS['service'] = $service;
						$GLOBALS['request'] = $processReq( $moduleRequest, $attributes );
						return;
					}
				}
			}

			




			// INFO: Detect Main Service
			$serviceParts = @explode( '.', "{$service}" );
			$serviceName = @array_pop( $serviceParts );
			$state = file_exists( path( "broot.{$serviceName}.{$serviceName}" ) . ".php" );
			if ($state) {
				$this->_entryBasis = $serviceName;

				define( 'WORKING_ROOT', PBKernel::$_cacheServicePath."/{$this->_entryBasis}" );
				define( '__WORKING_ROOT__', WORKING_ROOT );  // DEPRECATED: __WORKING_ROOT__ will be deprecated in 2.5.0

				$GLOBALS['service'] = $serviceName;
				$GLOBALS['request'] = $processReq( $moduleRequest, $attributes );
				return;
			}






			$reqService = "{$service}";
			if ( !empty($service) ) array_unshift($moduleRequest, $service);

			$service = DEFAULT_SERVICE;
			$state = $state || file_exists( path( "broot.{$service}.{$service}" ) . ".php" );
			if ($state) {
				$this->_entryBasis = $service;

				define( 'WORKING_ROOT', PBKernel::$_cacheServicePath."/{$this->_entryBasis}" );
				define( '__WORKING_ROOT__', WORKING_ROOT );  // DEPRECATED: __WORKING_ROOT__ will be deprecated in 2.5.0


				$GLOBALS['service'] = $service;
				$GLOBALS['request'] = $processReq( $moduleRequest, $attributes );
				return;
			}
			// endregion

			throw(new Exception("Cannot locate default basis ({$reqService})!"));
		}
		// endregion

		// region [ Process Control ]
		/** @var PBProc */
		private $_process = NULL;
		private function __forkProcess($service) {
			if ( $this->_process ) return;
			
			
			
			$this->_process = new PBProc( $this );

			chdir( WORKING_ROOT );
			$this->_process->prepareQueue($service);
		}
		// endregion
		
		// region [ Module Control ]
		private $_moduleSearchPaths	= [];
		public function addModuleSearchPath( $package = "" ) {
			if ( empty( $package ) ) return FALSE;

			$hash = md5( ($path = trim($package)) );
			if ( isset( $this->_moduleSearchPaths[$hash] ) ) return TRUE;


			if ( !is_dir( path( $path ) ) ) return FALSE;
			$this->_moduleSearchPaths[$hash] = $path;
			return TRUE;
		}
		public function removeModuleSearchPath( $package ) {
			if ( empty( $package ) ) return FALSE;

			$hash = md5( ($path = trim($package)) );
			if ( !isset( $this->_moduleSearchPaths[$hash] ) ) return TRUE;

			unset( $this->_moduleSearchPaths[$hash] );
			return TRUE;
		}
		public function acquireModule( $identifier ) {
			static $allocCounter = 0;

			$moduleDesc = self::ParseModuleIdentifier( $identifier );
			if ( $moduleDesc === FALSE ) throw( new Exception( "Given target module identifier has syntax error!" ) );



			$package  = implode( '.', $moduleDesc[ 'package' ] );
			$module	  = $moduleDesc[ 'module' ];
			$class	  = empty($moduleDesc[ 'class' ]) ? $module : $moduleDesc[ 'class' ];
			$moduleId = sha1( "{$package}.{$module}.{$class}#{$allocCounter}" . microtime() );






			// INFO: Search path construction
			$moduleSearchPaths   = [];
			$moduleSearchPaths[] = PITAYA_STANDALONE_EXECUTION_MODE ? "working." : "service.";
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

					if (file_exists( path($path) . ".php" ))
					{
						using($path);
						$hitPath = $path;
					}
				}
			}



			if ( empty( $hitPath ) || !class_exists( $class ) )
				throw(new Exception("Module {$class} doesn't exist!"));



			$invokeModule = "{$class}";
			$moduleObj = new $invokeModule();
			if ( !is_a($moduleObj, PBModule::class) ) throw(new Exception("Requested class is not a valid module"));

			$moduleObj->id = $moduleId;
			return $moduleObj;
		}
		// endregion
		
		
		
		
		
		
		// region [ Supportive Functions ]
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
		// endregion
	}
	class_alias( 'PBKernel', 'PBSysKernel' );

	final class PBKernelAccessor {
		/**@var PBKernel*/
		private $_relatedSys = NULL;
		public function __construct( PBKernel $sysInst ) {
			$this->_relatedSys = $sysInst;
		}
		public function acquireModule($moduleName, $reuse = FALSE) {
			return call_user_func_array([ $this->_relatedSys, "acquireModule" ], func_get_args());
		}
		public function addSearchPath( $package ) {
			return $this->_relatedSys->addModuleSearchPath( $package );
		}
		public function removeSearchPath( $package ) {
			return $this->_relatedSys->removeModuleSearchPath( $package );
		}
	}