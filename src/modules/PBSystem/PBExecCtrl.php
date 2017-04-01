<?php
	class PBBasisChain extends PBModule {
		private $_chainInfo = array();
		public function __construct( $chainInfo ) {
			$this->_chainInfo = is_array($chainInfo) ? $chainInfo : [];
		}
		
		public function execute( $chainData ) {
			foreach ( $this->_chainInfo as $chainModule ) {
				$this->chain[] = $module = PBModule($chainModule);
				data_fuse($module->data, $this->data);
			}
				
			return $chainData;
		}
	}

	class PBVectorChain extends PBModule {
		public function execute( $chainData ) {
			$module			= PBModule( "working." . __STANDALONE_MODULE__ );
			$this->chain[]	= PBModule($module);
			data_fuse($module->data, $this->data);
			
			return $chainData;
		}
	}
