<?php
	/**
	 ** Pitaya - PBTaskWorker.php
	 ** Created by JCloudYu on 2015/11/20 09:55
	 **/
	using( 'kernel.core.PBModule' );
	using( 'sys.tool.PBTaskWorker.PBTaskKernel' );

	class PBTaskWorker extends PBObject
	{
		private $_seqNum	= 0;
		private $_kernels	= array();
		public function addTaskKernel( PBTaskKernel $task ) {
			if ( in_array($task, $this->_kernels, TRUE) ) return;
			$task->seqId = ++$this->_seqNum;
			$this->_kernels[ $this->_seqNum ] = $task;
		}

		public function process( $msg = NULL ) {
			$result = array();
			foreach ( $this->_kernels as $idx => $task )
				$result[$idx] = $task->doProcess( $msg );
			return count($result) <= 1 ? array_shift($result) : $result;
		}

		public function batch( $msgPackages ) {
			$results = array();
			foreach ( $msgPackages as $key => $msgContent )
				$results[ $key ] = $this->process( $msgContent );
			return $results;
		}
	}
