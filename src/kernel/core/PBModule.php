<?php
/*
 * File: PBModule.php
 * Created by Cloud.
 * DateTime: 13/2/10 PM6:51
 */

using('kernel.core.PBBootstrap');

class PBModule extends PBObject
{
	private $_moduleId = NULL;
	private $_process = NULL;

	private $_bootstrap = NULL;

	public function prepare($moduleRequest) {


	}

	public function __get_id() {

		return $this->_moduleId;
	}

	public function __get_processId() {

		return $this->_process->id;
	}

	protected function loadBootStrap($bootstrapName) {

		$self = get_class($this);
		$requiredFile = __ROOT__."/modules/{$self}/bootstraps/$bootstrapName.bootstrap.json";
		if(!file_exists($requiredFile))
			throw(new Exception("Unable to locate the target bootstrap descriptor $bootstrapName"));

		$this->_bootstrap = PBBootstrap::parseBootstrap(json_decode(file_get_contents($requiredFile), TRUE));
		if($this->_bootstrap === NULL)
			throw(new Exception("Unable to decode the target bootstrap descriptor $bootstrapName"));

		// NOTE: load tools here...
		$requiredModules = $this->_bootstrap->tools;
		foreach($requiredModules as $module)
		{

		}
	}

//SEC: Getters/Setters that are provided for internal usage
	protected function __get_process() {

		return $this->_process;
	}

//SEC: Friend functions

	public function __get_tools() {

		if(!$this->friend('SYS', 'PBProcess', get_class($this)))
			throw(new Exception("Setting value to an undefined property __bootstrap."));

		return $this->_bootstrap ? $this->_bootstrap->tools : NULL;
	}

	public function __get_sequences() {

		if(!$this->friend('SYS', 'PBProcess', get_class($this)))
			throw(new Exception("Setting value to an undefined property __bootstrap."));

		return $this->_bootstrap ? $this->_bootstrap->sequences : NULL;
	}

	public function __set___moduleId($value) {

		if(!$this->friend('SYS'))
			throw(new Exception("Setting value to an undefined property __moduleId."));

		$this->_moduleId = $value;
	}

	public function __set___processInst($value) {

		if(!$this->friend('SYS', 'PBProcess'))
			throw(new Exception("Setting value to an undefined property __processInst."));

		$this->_process = $value;
	}
}