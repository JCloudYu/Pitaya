<?php
	/**
	 ** 0001.pitaya - PBTaskKernel.php
	 ** Created by JCloudYu on 2015/12/05 01:43
	 **/
	using( 'ext.base.misc' );

	class PBTaskKernel extends PBObject
	{
		private $_seqId = 0;
		public function __set_seqId( $value ) {
			$this->_seqId = CAST( $value, 'int strict' );
		}
		public function __get_seqId() {
			return $this->_seqId;
		}

		private $_dataProc = NULL;
		private function __procData( $data ){
			$dataProc = (is_callable($this->_dataProc)) ? $this->_dataProc : function($data){ return $data; };
			return $dataProc( $data );
		}

		public function doProcess( $data = NULL ){
			return $this->process( $this->__procData($data) );
		}
		public function process( $msg = NULL ){ return TRUE; }
	}
