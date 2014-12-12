<?php
/*
 * File: PBModule.php
 * Created by Cloud.
 * DateTime: 13/2/10 PM6:51
 */

class PBModule extends PBObject
{
	private $_moduleId 	= NULL;
	private $_process	= NULL;

	private $_tag 		= NULL;
	private $_extTag	= NULL;

	private $_bootstrap = array();

	public function prepare($moduleRequest, $taggingFlag = NULL) {}
	public function exec($param, $taggingFlag = NULL) { return $param; }

	public function prepareEvent($moduleRequest, $targetFlag = NULL) {}
	public function event($event) { return $event; }

	public function __get_id() {

		return $this->_moduleId;
	}

	public function __get_processId() {

		return $this->_process->id;
	}

	public function __get_class() { return get_class($this); }
	public function __get_class_lower() { return strtolower(get_class($this)); }
	public function __get_class_uppper() { return strtoupper(get_class($this)); }

	public function __get_tag()			{ return $this->_tag; }
	public function __set_tag($value)	{ $this->_tag = $value; }

	public function __get_ext() 		{ return $this->_extTag; }
	public function __set_ext($value)	{ $this->_extTag = $value; }

//SEC: Getters/Setters that are provided for internal usage
	protected function __get_process() {

		return $this->_process;
	}

	protected function __set_bootstrap($value){

		if(!is_array($value))
			$this->_bootstrap = array();
		else
			$this->_bootstrap = $value;
	}

	protected function __get_bootstrap(){

		return $this->_bootstrap;
	}


//SEC: Friend functions

	public function __get___bootSequence() {
		if(!$this->friend('SYS', 'PBProcess', get_class($this)))
			throw(new Exception("Setting value to an undefined property __bootstrap."));

		return $this->_bootstrap;
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
