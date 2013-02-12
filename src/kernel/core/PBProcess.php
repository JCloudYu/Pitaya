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

	private $_bootSequences = NULL;

	public function __construct() {


	}

	public function __destruct() {


	}

	public function run() {

		if(!$this->friend('SYS'))
			throw(new Exception("Calling an inaccessible function PBProcess::run()."));

		if($this->_processId === NULL)
			throw(new Exception("The process has no module to execute!."));

	}

	public function __get_id() {

		return $this->_processId;
	}

	public function attachModule($module, $moduleRequest) {

		$module = $this->_system->acquireModule($module);
		$module->__processInst = $this;

		$module->prepare($moduleRequest);

		$moduleId = $module->id;
		if($this->_mainModuleId == NULL)
		{
			$this->_mainModuleId = $moduleId;
			$this->_bootSequences = $module->sequences;
		}

		$this->_attachedModules[$moduleId] = $module;
		return $moduleId;
	}

	public function Invoke()
	{

	}

//SEC: Private functions or friend functions
	public function __set___processId($value) {

		if(!$this->friend('SYS'))
			throw(new Exception("Setting value to an undefined property __processId."));

		$this->_processId = $value;
	}

	public function __set___sysAPI($value) {

		if(!$this->friend('SYS'))
			throw(new Exception("Setting value to an undefined property __sysAPI."));

		$this->_system = $value;
	}
}