<?php
	/**
	 ** Pitaya - PBSYSBasisChainner.php
	 ** Created by JCloudYu on 2015/12/03 19:11
	 **/
	using( 'kernel.core.PBModule' );

	class PBSYSBasisChainner extends PBModule
	{
		private $_chainInfo = array();
		public function __construct( $chainInfo ) {
			$this->_chainInfo = is_array($chainInfo) ? $chainInfo : array();
		}

		public function prepare( $moduleRequest ) { $this->__buildChain($moduleRequest); }
		public function prepareShell( $moduleRequest ) { $this->__buildChain($moduleRequest);  }
		public function prepareEvent( $moduleRequest ) { $this->__buildChain($moduleRequest);  }

		private function __buildChain( $request )
		{
			foreach ( $this->_chainInfo as $chainModule )
				$this->boot[] = array( 'module' => $chainModule, 'request' => $request );
		}
	}
