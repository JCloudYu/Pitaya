<?php
	/**
	 ** 0001.pitaya - PBEvtCtrl.php
	 ** Created by JCloudYu on 2015/12/04 18:28
	 **/
	using( 'kernel.core.PBModule' );

	class PBBasisChain extends PBModule
	{
		private $_chainInfo = array();
		public function __construct( $chainInfo ) {
			parent::__construct();
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

	class PBVectorChain extends PBModule
	{
		public function prepareEvent( $moduleRequest )
		{
			if ( !CLI_ENV ) return;

			$this->prepareShell( $moduleRequest );
		}
		public function prepareShell( $moduleRequest )
		{
			$moduleName = "working." . __STANDALONE_MODULE__;
			$module = PBProcess::Module( $moduleName );

			if ( !$module->auth() )
			{
				PBStdIO::STDERR( "Access Denied! This module cannot be executed!" );
				return;
			}

			$this->boot[] = array( 'module' => $module, 'request' => $moduleRequest );
		}
	}
