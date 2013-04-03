<?php
/*
 * File: PBModule.php
 * Created by Cloud.
 * DateTime: 13/2/10 PM6:51
 */

class PBModule extends PBObject
{
	private $_moduleId = NULL;
	private $_process = NULL;

	protected $_bootstrap = NULL;


	public function prepare($moduleRequest) {


	}

	public function exec($param) {

	}

	public function __get_id() {

		return $this->_moduleId;
	}

	public function __get_processId() {

		return $this->_process->id;
	}

//SEC: Getters/Setters that are provided for internal usage
	protected function __get_process() {

		return $this->_process;
	}

//SEC: Friend functions

	public function __get___bootSequence() {
		if(!$this->friend('SYS', 'PBProcess', get_class($this)))
			throw(new Exception("Setting value to an undefined property __bootstrap."));

		return is_array($this->_bootstrap) ? $this->_bootstrap : array();
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