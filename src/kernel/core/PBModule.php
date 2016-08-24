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
	public function prepare($moduleRequest) {}
	public function exec($param) { return $param; }

	public function prepareCORS($moduleRequest) {}
	public function cors($param) { return $param; }

	public function prepareShell($moduleRequest) {}
	public function shell($param) { return $param; }

	public function prepareEvent($moduleRequest) {}
	public function event($event) { return $event; }
	
	public function prepareCommon($moduleRequest) {}
	public function common( $param ) { return $param; }




	// INFO: Interfaces that will be used in system core
	public function coreResolve() { return NULL; }



	// INFO: Supportive APIs
	public function auth($authData = NULL){ return true; }
	public function __get_auth() { return $this->auth(); }

	protected $_error = NULL;
	public function __get_error() { return $this->_error; }



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
	protected function _setBootSeq( $value ) {
		$this->_bootstrap = ( !is_array($value) ) ? array() : $value;
	}
	protected function &_getBootSeq() { return $this->_bootstrap; }
	protected function __set_bootstrap($value){ $this->_setBootSeq($value); }
	protected function &__get_bootstrap() { return $this->_getBootSeq(); }
	protected function __set_boot($value){ $this->_setBootSeq($value); }
	protected function &__get_boot() { return $this->_getBootSeq(); }



	// INFO: Friend functions
	public function __get___bootSequence() {
		if(!$this->friend('PBSysKernel', 'PBProcess', get_class($this)))
			throw(new Exception("Setting value to an undefined property __bootstrap."));

		return $this->_bootstrap;
	}
	public function __set___moduleId($value) {

		if(!$this->friend('PBSysKernel'))
			throw(new Exception("Setting value to an undefined property __moduleId."));

		$this->_moduleId = $value;
	}
	public function __set___processInst($value) {

		if(!$this->friend('PBSysKernel', 'PBProcess'))
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
