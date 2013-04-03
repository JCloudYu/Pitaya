<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Cloud
 * Date: 13/2/4
 * Time: PM11:56
 * To change this template use File | Settings | File Templates.
 */

// ISSUE: We need to define the APIs exposed to the module
class PBProcess extends PBObject
{
	private $_processId = NULL;
	private $_mainModuleId = NULL;
	private $_attachedModules = array();
	private $_system = NULL;

	private $_bootSequence = NULL;

	public function __construct() {


	}

	public function __destruct() {


	}

//SEC: Process API//////////////////////////////////////////////////////////////////////////////////////////////////////
	public function __get_id() {

		return $this->_processId;
	}

	public function attachModule($moduleName, $moduleRequest, $allowDuplicate = FALSE) {

		$duplicated = FALSE;
		if(array_key_exists($moduleName, $this->_attachedModules))
		{
			if(!$allowDuplicate) return FALSE;
			$duplicated = TRUE;
		}

		$module = $this->_system->acquireModule($moduleName);
		$module->__processInst = $this;
		$module->prepare($moduleRequest);
		$moduleId = $module->id;

		$this->_attachedModules[$moduleId] = $module;
		if(!$duplicated) $this->_attachedModules[$moduleName] = $module;

		return $moduleId;
	}
//END SEC///////////////////////////////////////////////////////////////////////////////////////////////////////////////

	// MARK: Friend(SYS)
	public function run() {

		if(!$this->friend('SYS'))
			throw(new Exception("Calling an inaccessible method PBProcess::run()."));

		if($this->_processId === NULL)
			throw(new Exception("The process has no module to execute!."));

		$dataInput = NULL;
		foreach($this->_bootSequence as $moduleHandle)
			$dataInput = $this->_attachedModules[$moduleHandle]->exec($dataInput);

		return 'terminated';
	}

	// MARK: Friend(SYS)
	public function attachMainService($moduleName, $moduleRequest) {

		if(!$this->friend('SYS')) throw(new Exception("Calling an inaccessible function PBProcess::attachMainModule()."));

		if($this->_mainModuleId != NULL) throw(new Exception("Reattachment of main module is not allowed"));

		$module = $this->_system->acquireModule($moduleName);
		$module->__processInst = $this;

		// INFO: Preparing the module will force the module to it's corresponding bootstrap
		$module->prepare($moduleRequest);

		$moduleId = $module->id;
		$this->_mainModuleId = $moduleId;

		// INFO: Get the boot sequence from bootstrap

		$this->_bootSequence = array();
		$this->_bootSequence[] = $moduleId;

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

			$this->_bootSequence[] = $moduleId;
		}
	}
}