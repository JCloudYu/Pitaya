<?php
	/**
	 ** 1024.QueueCounter - Notifier.php
	 ** Created by JCloudYu on 2015/11/02 19:40
	 **/
	using( 'kernel.basis.PBObject' );
	using( 'sys.net.NotifyKernel.INotifyKernel' );

	class NotifyWorker extends PBObject
	{
		private $_kernel = NULL;

		public function __set_kernel( INotifyKernel $value )
		{
			$this->_kernel = $value;
		}

		public function send( $packages )
		{
			if ( $this->_kernel === NULL )
				return NULL;


			$status = array();
			foreach ( $packages as $key => $msgContent ) {
				if ( empty( $msgContent ) ) {
					$status[ $key ] = FALSE;
					continue;
				}

				$status[ $key ] = $this->_kernel->send( $msgContent );
			}

			return $status;
		}
	}
