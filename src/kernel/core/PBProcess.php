<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Cloud
 * Date: 13/2/4
 * Time: PM11:56
 * To change this template use File | Settings | File Templates.
 */

class PBProcess extends PBObject
{
	private $_processId = NULL;
	private $_mainModuleId = NULL;
	private $_attachedModules = array();
	private $_system = NULL;

	private $_bootSequence = NULL;

	public function __construct() {

		$this->_bootSequence = PBLList::GENERATE();
	}

	public function __destruct() {


	}

//SEC: Process API//////////////////////////////////////////////////////////////////////////////////////////////////////
	public function __get_id() {

		return $this->_processId;
	}

	public function getModule($moduleName) {

		if(array_key_exists($moduleName, $this->_attachedModules))
			return $this->_attachedModules[$moduleName];

		return NULL;
	}

	public function attachModule($moduleName, $moduleRequest, $reusable = TRUE) {

		if(array_key_exists($moduleName, $this->_attachedModules) && $reusable)
			return $this->_attachedModules[$moduleName]->id;

		$module = $this->_system->acquireModule($moduleName);
		$module->__processInst = $this;
		$module->prepare($moduleRequest);
		$moduleId = $module->id;

		$this->_attachedModules[$moduleId] = $module;
		if($reusable) $this->_attachedModules[$moduleName] = $module;

		return $moduleId;
	}

	public function assignNextModule($moduleHandle) {

		if(!array_key_exists($moduleHandle, $this->_attachedModules)) return FALSE;

		PBLList::AFTER($this->_bootSequence, $moduleHandle, $moduleHandle);

		return TRUE;
	}

	public function cancelNextModule() {

		$status = PBLList::NEXT($this->_bootSequence);
		if(!$status) return $status;

		$status = $status && PBLList::REMOVE($this->_bootSequence);
		return $status;
	}

	public function replaceNextModule($moduleHandle) {

		if(!array_key_exists($moduleHandle, $this->_attachedModules)) return FALSE;

		$status = PBLList::NEXT($this->_bootSequence);
		$status = $status && PBLList::SET($this->_bootSequence, $moduleHandle, $moduleHandle);
		$status = $status && PBLList::PREV($this->_bootSequence);

		return $status;
	}

	public function pushModule($moduleHandle) {

		if(!array_key_exists($moduleHandle, $this->_attachedModules)) return FALSE;

		$status = PBLList::PUSH($this->_bootSequence, $moduleHandle, $moduleHandle);

		return $status;
	}
//END SEC///////////////////////////////////////////////////////////////////////////////////////////////////////////////

	// MARK: Friend(SYS)
	public function run() {

		if(!$this->friend('SYS'))
			throw(new Exception("Calling an inaccessible method PBProcess::run()."));

		if($this->_processId === NULL)
			throw(new Exception("The process has no module to execute!."));

		$dataInput = NULL;
		PBLList::HEAD($this->_bootSequence);
		do
		{
			$moduleHandle = $this->_bootSequence->data;
			$dataInput = $this->_attachedModules[$moduleHandle]->exec($dataInput);
		}
		while(PBLList::NEXT($this->_bootSequence));

		return 'terminated';
	}

	// MARK: Friend(SYS)
	public function attachMainService($moduleName, $moduleRequest) {

		if(!$this->friend('SYS')) throw(new Exception("Calling an inaccessible function PBProcess::attachMainModule()."));

		if($this->_mainModuleId != NULL) throw(new Exception("Reattachment of main module is not allowed"));

		// INFO: System will first look for [ main ] module in the service folder
		// INFO: If the main module doesn't exist, look for module with the service name instead
		$module = $this->_system->acquireModule($moduleName, TRUE);

		$module->__processInst = $this;

		// INFO: Preparing the module will force the module to it's corresponding bootstrap
		$moduleId = $module->id;
		$this->_mainModuleId = $moduleId;

		PBLList::PUSH($this->_bootSequence, $moduleId, $moduleId);

		$module->prepare($moduleRequest);

		// INFO: Get default boot sequence from boot module

		$this->__bootSequence = $module->__bootSequence;

		$this->_attachedModules[$moduleName] = $module;
		$this->_attachedModules[$moduleId] = $module;
	}

	// MARK: Friend(SYS)
	public function __set___processId($value) {

		if(!$this->friend('SYS'))
			throw(new Exception("Setting value to an undefined property __processId."));

		$this->_processId = $value;
	}

	// MARK: Friend(SYS)
	public function __set___sysAPI($value) {

		if(!$this->friend('SYS'))
			throw(new Exception("Setting value to an undefined property __sysAPI."));

		$this->_system = $value;
	}

	// INFO: Parse and prepare bootSequence
	protected function __set___bootSequence($value) {

		if(is_null($value) || !is_array($value)) return;

		foreach($value as $illustrator)
		{
			if(!is_array($illustrator))
				throw(new Exception("Error bootSequence structure definition"));

			if(!array_key_exists('module', $illustrator))
				throw(new Exception("Error bootSequence structure definition"));

			$moduleName = $illustrator['module'];
			$reuse = FALSE;

			if(!array_key_exists('reuse', $illustrator))
				$reuse = $reuse || FALSE;
			else
			{
				if(!is_bool($illustrator['reuse']))
					throw(new Exception("Error bootSequence structure definition"));

				$reuse = $reuse || $illustrator['reuse'];
			}

			$request = NULL;
			if(array_key_exists('request', $illustrator))
				$request = $illustrator['request'];

			$moduleId = $this->attachModule($moduleName, $request, $reuse);

			PBLList::PUSH($this->_bootSequence, $moduleId, $moduleId);
		}
	}
}