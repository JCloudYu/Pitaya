<?php
	class PBFuncChain extends PBModule {
		private $_processChain = [];
		public function execute( ...$arguments ) {
			$param = @$arguments[0]; $request = @$arguments[1];
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
