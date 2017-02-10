<?php
	class PBBasisChain extends PBModule
	{
		private $_chainInfo = array();
		public function __construct( $chainInfo ) {
			$this->_chainInfo = is_array($chainInfo) ? $chainInfo : [];
		}
		
		public function execute( $chainData = NULL, $request = NULL ) {
			foreach ( $this->_chainInfo as $chainModule )
				$this->chain[] = [ 'module' => $chainModule, 'request' => $request ];
				
			return $chainData;
		}
	}

	class PBVectorChain extends PBModule
	{
		public function execute( $chainData = NULL, $moduleRequest = NULL )
		{
			$module			= PBProcess::Module( "working." . __STANDALONE_MODULE__ );
			$this->chain[]	= [ 'module' => $module, 'request' => $moduleRequest ];
		}
	}
