<?php
	final class PBKernel extends PBObject {
		// region [ Boot Related ]
		/** @var $_SYS_INSTANCE PBKernel */
		private static $_SYS_INSTANCE = NULL;
		public static function Kernel() {
			return self::$_SYS_INSTANCE;
		}
		public static function boot() {

			// INFO: Avoid repeated initialization
			if ( PBKernel::$_SYS_INSTANCE ) return;
			$G_CONF = PBStaticConf( 'pitaya-env' );



			try {
				// INFO: Keep booting
				PBKernel::$_SYS_INSTANCE = new PBKernel();
				PBKernel::$_SYS_INSTANCE->__initialize();
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

				if ( $G_CONF[ 'log-exceptions' ] === TRUE )
				{
					PBLog( 'exception' )->log(print_r($e, TRUE));
					$extMsg = "See exception log for more information!";
				}

				PBLog( 'error' )->log( $errMsg );
				if (!empty($extMsg)) {
					PBLog( 'error' )->log( $extMsg );
				}



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
				if ( $G_CONF[ 'throw-exceptions' ] === TRUE )
				{
					throw( $e );
				}
				else
				if ( IS_HTTP_ENV && $G_CONF[ 'debug-mode' ] )
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
		public static function SetBasisResolver($callable ){
			if ( !is_callable($callable) ) return;
			self::$_bootResolver = $callable;
		}
		// endregion

		// region [ Boot Control ]
		private function __construct() {}
		private function __initialize() {
			$G_CONF = PBStaticConf( 'pitaya-env' );
			foreach( $G_CONF[ 'boot-scripts' ] as $script ) {
				$path = path($script) . '.php';
				if ( is_file($path) && is_readable($path) ) {
					require_once $path;
				}
			}



			// INFO: Perform service decision and data initialization
			$this->__judgeMainService();
			PBPathResolver::Purge();
			
			
			
			PBRequest()->__initialize()->parseQuery(function_exists( 'default_query_parser' ) ? 'default_query_parser' : NULL);



			// INFO: Define runtime constants
			define( 'WORKING_BASIS', $this->_entryBasis );
			



			// INFO: Bring up the main process
			$this->__forkProcess($this->_entryBasis);
		}
		
		private $_entryBasis = NULL;
		private function __judgeMainService() {
			$G_CONF = PBStaticConf( 'pitaya-env' );
		
		
		
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
				$attachPoint = @array_splice( $resource, 0, $G_CONF[ 'attach-depth' ] );
				$GLOBALS[ 'attachPoint' ] = $attachPoint;
				$GLOBALS[ 'rawRequest' ] = implode('/', $resource) . (empty($attributes) ? '' : "?{$attributes}");



				$service = @array_shift( $resource );
				$moduleRequest = $resource;
			}
			else {
				$service = CAST( @array_shift($_SERVER['argv']), 'string' );
				$moduleRequest = $_SERVER['argv'];
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
				$CWD	= PITAYA_STANDALINE_EXECUTION_DIR;
				$SCRIPT = PITAYA_STANDALINE_EXECUTION_SCRIPT;
				
				$scriptFilePath = "{$CWD}/{$SCRIPT}";
				if ( is_readable($scriptFilePath) && is_file($scriptFilePath) )
				{
					if ( !empty($service) ) array_unshift( $moduleRequest, $service );
	
					$module = basename($SCRIPT);
					$ext = substr( $module, -4 );
					if ( in_array( $ext, array( '.php' ) ) ) $module = substr( $module, 0, -4 );
					$this->_entryBasis = "PBSystem.PBExecCtrl#PBVectorChain";
	
					define( 'WORKING_ROOT', $CWD );
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
				
					$service		= @$result->basis ?: $service;
					$moduleRequest	= @$result->resource ?: $moduleRequest;
					$workingDir		= @$result->root ?: '';
					
					
					
					// INFO: Detect Main Service
					$state = file_exists( path( "{$service}" ) . ".php" );
					if ($state) {
						$this->_entryBasis = $service;
		
						define( 'WORKING_ROOT', is_dir($workingDir) ? $workingDir : sys_get_temp_dir());
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
				$broot = path( 'broot' );
				define( 'WORKING_ROOT', "{$broot}/{$this->_entryBasis}" );
				$GLOBALS['request'] = $processReq( $moduleRequest, $attributes );
				return;
			}






			$reqService = "{$service}";
			if ( !empty($service) ) array_unshift($moduleRequest, $service);

			$service = $G_CONF[ 'default-basis' ];
			$state = $state || file_exists( path( "broot.{$service}.{$service}" ) . ".php" );
			if ($state) {
				$this->_entryBasis = $service;
				$broot = path( 'broot' );
				define( 'WORKING_ROOT', "{$broot}/{$this->_entryBasis}" );
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
		public function addSearchPath( $package = "" ) {
			if ( empty( $package ) ) return FALSE;

			$hash = md5( ($path = trim($package)) );
			if ( isset( $this->_moduleSearchPaths[$hash] ) ) return TRUE;


			if ( !is_dir( path( $path ) ) ) return FALSE;
			$this->_moduleSearchPaths[$hash] = $path;
			return TRUE;
		}
		public function removeSearchPath( $package ) {
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



			// region [ Search path construction ]
			$G_CONF = PBStaticConf( 'pitaya-env' );

			$moduleSearchPaths   = [];
			$moduleSearchPaths[] = "modules.";
			$moduleSearchPaths[] = PITAYA_STANDALONE_EXECUTION_MODE ? "working." : "basis.";
			
			
			foreach ( $G_CONF[ 'module-packages' ] as $path ) {
				$moduleSearchPaths[] = "{$path}.";
			}
			foreach ( $this->_moduleSearchPaths as $path ) {
				$moduleSearchPaths[] = "{$path}.";
			}
			$moduleSearchPaths[] = ""; // Use global identifier
			$moduleSearchPaths = array_unique($moduleSearchPaths);
			// endregion



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
		private static function ParseModuleIdentifier( $moduleIdentifier ) {
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
	function PBKernel() {
		return PBKernel::Kernel();
	}