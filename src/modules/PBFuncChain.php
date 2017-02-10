<?php
	class PBFuncChain extends PBModule {
		private $_processChain = [];
		public function execute( $param = NULL, $request = NULL ) {
		
			foreach ( $this->_processChain as $call )
			{
				if ( !is_callable( $call ) ) continue;
				$param = $call( $param, $request );
			}

			return $param;
		}

		public function __set_item( $call ) { 
			$this->add( $call );
		}

		public function add( $callable ) {
			$this->_processChain[] = $callable;
		}
	}
