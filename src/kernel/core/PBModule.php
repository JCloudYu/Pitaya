<?php

	abstract class PBModule extends PBObject {
		public function execute( $chainData = NULL, $initData = NULL ) { 
			return $chainData; 
		}
		public function __invoke( ...$arguments ) {
			return call_user_func_array( [ $this, 'execute' ], $arguments );
		}
		


		private $_instId = NULL;
		public function __set_id( $value ) {
			if ( $this->_instId !== NULL ) return;
			$this->_instId = "{$value}";
		}
		public function __get_id() {
			return $this->_instId;
		}
		public function __get_id_short() {
			return substr( $this->_instId, 0, 8  );
		}
		public function __get_id_medium() {
			return substr( $this->_instId, 0, 16 );
		}
		public function __get_id_long() {
			return substr( $this->_instId, 0, 32 );
		}
	
		public function __get_class() {
			return get_class($this);
		}
		public function __get_class_lower() {
			return strtolower(get_class($this));
		}
		public function __get_class_uppper() {
			return strtoupper(get_class($this));
		}
		
		protected $chain = [];
		public function __get_bootChain() {
			return $this->chain;
		}
		
		protected $_error = NULL;
		public function __get_error() {
			return $this->_error;
		}
	}
