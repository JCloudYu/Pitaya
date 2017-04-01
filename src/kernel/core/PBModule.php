<?php
	abstract class PBModule extends PBObject {
		public function execute( $chainData ) {
			return $chainData;
		}
		public function __invoke() {
			$arguments = func_get_args();
		
			$args = [
				@array_shift($arguments),
				@array_shift($arguments),
			];
			$args = array_merge($args, $arguments);
			return call_user_func_array( [ $this, 'execute' ], $args );
		}
		public function __toString() {
			return "{$this( NULL, NULL )}";
		}
		


		private $_data = NULL;
		public function &__get_data() {
			if ( $this->_data === NULL ) 
				$this->_data = (object)[];
			
			return $this->_data;
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
		
		protected $error = NULL;
		public function __get_error() {
			return $this->error;
		}
	}

	function PBModule( $moduleName, $reusable = TRUE, $noThrow = FALSE ) {
		try {
			return PBProcess::Process()->getModule( $moduleName, $reusable );
		}
		catch( Exception $e ) {
			if ( $noThrow ) return NULL;
			throw $e;
		}
	}
