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
	private $_ext		= NULL;
	private $_bootstrap = array();

	public function __construct() { $this->_ext = new stdClass(); }



	// INFO: Main APIs
	public function prepare($moduleRequest, $taggingFlag = NULL) {}
	public function exec($param, $taggingFlag = NULL) { return $param; }

	public function prepareShell($moduleRequest, $taggingFlag = NULL) {}
	public function shell($param, $taggingFlag = NULL) { return $param; }

	public function prepareEvent($moduleRequest, $targetFlag = NULL) {}
	public function event($event) { return $event; }



	// INFO: Supportive APIs
	public function auth($authData = NULL){ return true; }
	public function __get_auth() { return $this->auth(); }



	// INFO: Module information
	public function getId( $length = NULL ) {
		$length = TO( $length, 'int strict' );
		return ( $length > 0 ) ? substr($this->_moduleId, 0, $length) : $this->_moduleId;
	}
	public function __get_id() { return $this->getId(); }
	public function __get_id_short()  { return $this->getId(8);  }
	public function __get_id_medium() { return $this->getId(16); }
	public function __get_id_long()	  { return $this->getId(32); }

	public function __get_class() { return get_class($this); }
	public function __get_class_lower() { return strtolower(get_class($this)); }
	public function __get_class_uppper() { return strtoupper(get_class($this)); }

	public function __get_ext() { return $this->_ext; }



	// INFO: Boot Sequence Manipulating
	protected function _assignBootSeq( $value ) {
		$this->_bootstrap = ( !is_array($value) ) ? array() : $value;
	}
	protected function &_getBootSeq() { return $this->_bootstrap; }
	protected function __set_bootstrap($value){ $this->_assignBootSeq($value); }
	protected function &__get_bootstrap() { return $this->_getBootSeq(); }
	protected function __set_boot($value){ $this->_assignBootSeq($value); }
	protected function &__get_boot() { return $this->_getBootSeq(); }



	// INFO: Friend functions
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



	// region [ Legacy ]
	public function __get_processId() {

		return $this->_process->id;
	}
	protected function __get_process() {

		return $this->_process;
	}
	// endregion
}
