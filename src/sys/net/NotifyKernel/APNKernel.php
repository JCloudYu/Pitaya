<?php
	/**
	 ** 1024.QueueCounter - APNKernel.php
	 ** Created by JCloudYu on 2015/11/04 03:25
	 **/
	using( 'sys.net.NotifyKernel.INotifyKernel' );
	using( 'ext.base.misc' );

	class APNKernel extends PBObject implements INotifyKernel
	{
		const DEVELOPMENT_PUSH_SERVER	= "gateway.sandbox.push.apple.com";
		const PRODUCTION_PUSH_SERVER	= "gateway.push.apple.com";

		private $_serverAddr = "";
		private $_certPath	 = "";
		private $_certPass	 = "";
		private $_conTimeout = 100;
		private $_connection = NULL;



		public function __construct() { }
		public function __destruct() {
			$this->disconnect();
		}
		public function __get_isConnected() {
			return !empty( $this->_connection );
		}



		public function connect( $serverAddr, $certPath, $certPass, $timeout = 100 )
		{
			if ( $this->isConnected )
				$this->disconnect();


			$this->_serverAddr = $serverAddr;
			$this->_certPath   = $certPath;
			$this->_certPass   = $certPass;
			$this->_conTimeout = $timeout;


			$context = stream_context_create();
			stream_context_set_option( $context, 'ssl', 'local_cert', $this->_certPath );
			if ( !empty( $certPath ) )
				stream_context_set_option( $context, 'ssl', 'passphrase', $this->_certPass );

			$fCon = stream_socket_client(
				"ssl://{$this->_serverAddr}:2195",
				$error, $errorStr,
				$this->_conTimeout,
				STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT,
				$context
			);

			if ( $fCon === FALSE ) {
				$this->_connection = NULL;

				return array(
					'error' => $error,
					'msg'   => $errorStr
				);
			}

			$this->_connection = $fCon;
			stream_set_blocking( $this->_connection, 0 );

			return $this;
		}
		public function disconnect()
		{
			if ( !empty( $this->_connection ) )
				fclose( $this->_connection );

			$this->_connection = NULL;
		}
		public function reconnect()
		{
			if ( !$this->isConnected )
				return;

			$this->disconnect();
			$this->connect( $this->_serverAddr, $this->_certPath, $this->_certPass, $this->_conTimeout );
		}



		public function send( $msgContent )
		{
			if ( !$this->isConnected )
				return NULL;

			$token      = $msgContent[ 'token' ];
			$expire     = TO( $msgContent[ 'expire' ], 'int strict' );
			$expire     = ( $expire <= 0 ) ? 0 : $expire;
			$payloads   = TO( $msgContent[ 'payload' ], 'array' );
			$identifier = time();


			$payloads[ 'data' ][ 'aps' ] = $payloads[ 'notification' ];
			$payload                     = json_encode( $payloads[ 'data' ] );
			$payloadLen                  = strlen( $payload );


			$binaryHeader = ( empty( $expire ) ) ?	pack( 'CNNnH*n', 1, $identifier, $expire, 32, $token, $payloadLen ) :
													pack( 'CnH*n', 0, 32, $token, $payloadLen );

			$data = $binaryHeader . $payload;
			fwrite( $this->_connection, $data );



			// INFO: Retrieve processing status
			$response = fread( $this->_connection, 6 );
			if ( !empty( $response ) ) {
				list( $command, $status, $identifier ) = unpack( "CCN", $response );
				$this->reconnect();

				return array(
					'status'     => $status,
					'identifier' => $identifier
				);
			}

			return TRUE;
		}
	}
