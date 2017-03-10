<?php
class PBProcess extends PBObject
{
	public static function Module( $moduleName, $reusable = TRUE, $noThrow = FALSE ) {
		call_user_func( 'PBModule', $moduleName, $reusable, $noThrow );
	}
	public static function ServiceModule() {
		return self::$_singleton->_entryModule;
	}





	/** @var PBProcess */
	public static $_singleton = NULL;
	public static function Process() { return self::$_singleton; }
	
	/** @var PBSysKernel */
	private $_system = NULL;
	/** @var null|PBLinkedList */
	private $_bootSequence	= NULL;
	public function __construct( $sysInst ) {
		self::$_singleton = $this;
		$this->_system = $sysInst;
		$this->_bootSequence = PBLList::GENERATE();
	}


	



	public function addSearchPath( $package ) { return $this->_system->addModuleSearchPath( $package ); }
	public function removeSearchPath( $package ) { return $this->_system->removeModuleSearchPath( $package ); }
	public function getNextModule() {
	
		if (!PBLinkedList::NEXT($this->_bootSequence)) return NULL;
		$moduleId = $this->_bootSequence->data[ 'id' ];
		PBLinkedList::PREV($this->_bootSequence);

		return $this->_attachedModules[$moduleId];
	}
	public function transferRequest($moduleRequest) {
	
		PBLinkedList::NEXT($this->_bootSequence);
		$this->_bootSequence->data['request'] = $moduleRequest;
		PBLinkedList::PREV($this->_bootSequence);
	}
	public function cancelNextModule() {

		$status = PBLList::NEXT($this->_bootSequence);
		if(!$status) return $status;

		$status = $status && PBLList::REMOVE($this->_bootSequence);
		return $status;
	}
	public function cancelModules( $skips = NULL ) {
	
		if ( func_num_args() == 0 )
		{
			while( PBLinkedList::NEXT($this->_bootSequence) )
				PBLinkedList::REMOVE($this->_bootSequence);
		}
		else
		if ( is_numeric( $skips ) )
		{
			if ( $skips > 0 )
			{
				$skipCounter = $skips;
				while( PBLinkedList::NEXT($this->_bootSequence) )
				{
					if ( $skipCounter <= 0 )
						PBLinkedList::REMOVE($this->_bootSequence);
					else
						$skipCounter--;
				}
				
				while( $skips-- > 0 )
					PBLinkedList::PREV( $this->_bootSequence );
			}
			else
			if ( $skips < 0 )
			{
				$skips = -$skips; $length = 0;
				while( PBLinkedList::NEXT($this->_bootSequence) ) $length++;
				
				if ( $length <= $skips )
				{
					while( $length-- > 0 ) PBLinkedList::PREV( $this->_bootSequence );
					return;
				}
				
				
				
				$length -= $skips;
				while( $skips-- > 0 ) PBLinkedList::PREV($this->_bootSequence);
				while( $length-- > 0 ) PBLinkedList::REMOVE( $this->_bootSequence );
			}
		}
		else
		{
			if ( !is_array( $skips ) ) $skips = [ $skips ];
		
		
			$skipCounter = 0;
			while( PBLinkedList::NEXT($this->_bootSequence) )
			{
				$module = $this->_attachedModules[ $this->_bootSequence->data[ 'id' ] ];
				
				$valid = FALSE;
				ary_filter( $skips, function( $name ) use( &$valid, &$module ) {
					$name = "{$name}";
					$valid = $valid || ( $module instanceof $name );
				});
				
				if ( $valid )
					$skipCounter++;
				else
					PBLinkedList::REMOVE($this->_bootSequence);
			}
			
			
			while( $skipCounter-- > 0 )
				PBLinkedList::PREV( $this->_bootSequence );
		}
	}
	
	
	
	
	
	public function run() {
		$dataInput = NULL;

		PBLList::HEAD($this->_bootSequence);
		do
		{
			$module  = @$this->_attachedModules[@$this->_bootSequence->data[ 'id' ]];
			$request = @$this->_bootSequence->data[ 'request' ];
			if ( !property_exists($module->data, "initData") )
				$module->data->initData = $request;
	
			$dataInput = $module->execute( $dataInput, $request );
			$this->_appendBootSequence( $module->bootChain );
		}
		while( PBLList::NEXT($this->_bootSequence) );
	}
	
	private $_entryModule	= NULL;
	private $_mainModuleId = NULL;
	public function attachMainService($moduleName, $instParam, $moduleRequest) {

		// NOTE: Leading Module
		if ( defined('LEADING_MODULE') )
		{
			$module = $this->_acquireModule( LEADING_MODULE, TRUE );
			$moduleId = $module->id;
			PBLList::PUSH( $this->_bootSequence, [
				'id' => $moduleId, 'request' => $moduleRequest 
			], $moduleId );
		}



		// NOTE: Service Entry Module
		$this->_entryModule = $this->_acquireModule( $moduleName, $instParam, TRUE );
		$this->_mainModuleId = $this->_entryModule->id;
		PBLList::PUSH( $this->_bootSequence, [
			'id' => $this->_mainModuleId, 'request' => $moduleRequest 
		], $this->_mainModuleId);



		// NOTE: Tailing Module
		if ( defined('TAILING_MODULE') )
		{
			$module = $this->_acquireModule(TAILING_MODULE, TRUE);
			$moduleId = $module->id;
			PBLList::PUSH( $this->_bootSequence,  [ 
				'id' => $moduleId, 'request' => $moduleRequest
			], $moduleId );
		}
		
		
		
		// NOTE: Rewind back to the first instance
		PBLinkedList::HEAD($this->_bootSequence);
	}
	
	public function getModule($moduleName, $instParam = NULL, $reusable = TRUE) {
		if ( func_num_args() == 2 )
		{
			$reusable = $instParam;
			$instParam = NULL;
		}

		return $this->_acquireModule($moduleName, $instParam, $reusable);
	}
	private function _appendBootSequence( $bootSequence ) {

		if( empty( $bootSequence ) || !is_array( $bootSequence )) return;

		$bootSequence = array_reverse( $bootSequence );

		foreach( $bootSequence as $illustrator )
		{
			if ( is_a( $illustrator, stdClass::class ) ) $illustrator = (array)$illustrator;
			if ( !is_array($illustrator) ) continue; // Skipping none array
			if( !array_key_exists('module', $illustrator) )
				throw(new Exception("Error bootSequence structure definition"));
				
			$moduleHandle = $illustrator['module'];
			if ( empty($moduleHandle) ) continue; // Skipping empty values



			if ( is_a($moduleHandle, PBModule::class ) && array_key_exists($moduleHandle->id, $this->_attachedModules))
				$moduleId = $moduleHandle->id;
			else
			{
				$reuse = array_key_exists( 'reuse', $illustrator ) ? !empty($illustrator['reuse'] ) : TRUE;
				$moduleId = $this->_acquireModule( $moduleHandle, $reuse )->id;
			}



			PBLList::AFTER( $this->_bootSequence,  [ 
				'id'		=> $moduleId, 
				'request'	=> array_key_exists('request', $illustrator) ? $illustrator[ 'request' ] : NULL
			], $moduleId );
		}
	}
	
	private $_attachedModules = [];
	private function _acquireModule( $moduleIdentifier, $instParam = NULL, $reusable = TRUE )
	{
		if ( func_num_args() == 2 )
		{
			$reusable = $instParam;
			$instParam = NULL;
		}



		if ( array_key_exists( $moduleIdentifier, $this->_attachedModules ) )
		{
			$module = $this->_attachedModules[ $moduleIdentifier ];

			// INFO: Given module identifier is in package format
			if ( ($moduleIdentifier != $module->id) && !$reusable ) $module = NULL;
		}


		if ( empty($module) )
		{
			$module	  = $this->_system->acquireModule( $moduleIdentifier, $instParam );
			$moduleId = $module->id;
			$this->_attachedModules[ $moduleId ] = $module;

			if ( $reusable ) $this->_attachedModules[ $moduleIdentifier ] = $module;
		}

		return $module;
	}
}

function PBProcess(){
	static $_singleton = NULL;
	if ( $_singleton === NULL ) {
		$_singleton = PBProcess::Process();
	}
	
	return $_singleton;
}
