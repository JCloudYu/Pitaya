<?php
	/**
	 ** 1024.QueueCounter - GCMKernel.php
	 ** Created by JCloudYu on 2015/11/04 03:28
	 **/
	using( 'sys.net.NotifyKernel.INotifyKernel' );
	using( 'ext.base.misc' );

	class GCMKernel extends PBObject implements INotifyKernel
	{
		const GCM_HTTP_SERVER_ADDRESS	 = "https://gcm-http.googleapis.com/gcm/send";
		const GCM_ANDROID_SERVER_ADDRESS = "https://android.googleapis.com/gcm/send";

		private $_serverAddr = "";
		private $_serverKey	 = "";
		private $_conTimeout = 100;


		public function __construct( $serverAddr, $serverKey, $timeout = 100 ) {
			$this->_serverAddr	= $serverAddr;
			$this->_serverKey	= $serverKey;
			$this->_conTimeout	= $timeout;
		}

		public function send( $msgContent )
		{
			$token      = $msgContent[ 'token' ];
			$payloads   = TO( $msgContent[ 'payload' ], 'array' );


			$fields = TO( $payloads['options'], 'array' );
			$fields[ 'to' ] = $token;
			$fields['notification'] = TO( $payloads['notification'], 'array' );
			if ( !empty($payloads['data']) ) $fields[ 'data' ] = $payloads['data'];



			$hCurl = curl_init();
			curl_setopt( $hCurl, CURLOPT_URL, $this->_serverAddr );
			curl_setopt( $hCurl, CURLOPT_POST, TRUE );
			curl_setopt( $hCurl, CURLOPT_FRESH_CONNECT, TRUE );
			curl_setopt( $hCurl, CURLOPT_HTTPHEADER, array (
				'Authorization: key=' . $this->_serverKey,
				'Content-Type: application/json'
			));
			curl_setopt( $hCurl, CURLOPT_RETURNTRANSFER, TRUE );
			curl_setopt( $hCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
			curl_setopt( $hCurl, CURLOPT_POSTFIELDS, json_encode( $fields ) );
			$response = curl_exec( $hCurl );
			$status = ( $response !== FALSE ) ? curl_getinfo( $hCurl, CURLINFO_HTTP_CODE ) : -1;
			$error  = ( $response !== FALSE ) ? NULL : curl_error( $hCurl );
			curl_close( $hCurl );



			$result = array();
			$result[ 'status' ]	= $status;
			$result[ 'error' ]	= $error;
			$result[ 'response' ] =  @json_decode( $response, TRUE );
			return $result;
		}
	}
