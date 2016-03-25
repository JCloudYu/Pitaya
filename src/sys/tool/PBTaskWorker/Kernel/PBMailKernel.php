<?php
	/**
	 ** 0002.pitaya.workbench - PBMailKernel.php
	 ** Created by JCloudYu on 2015/12/25 13:09
	 **/
	using( 'sys.tool.PBTaskWorker.PBTaskKernel' );
	using( 'ext.base.misc' );
	using( 'ext.base.array' );

	class PBMailKernel extends PBTaskKernel
	{
		const ERROR_INCORRECT_GIVEN_MSG	= -1;
		const ERROR_CONNECTION			= -2;
		const ERROR_FETCHING_RESPONSE	= -3;
		const ERROR_UNEXPECTED_RESPONSE	= -4;


		protected $_account = "";
		public function __get_account() {
			return $this->_account;
		}
		public function __set_account( $value ) {
			$this->_account = "{$value}";
		}

		protected $_password = "";
		public function __get_password() {
			return $this->_password;
		}
		public function __set_password( $value ) {
			$this->_password = "{$value}";
		}

		protected $_fromName = "";
		public function __get_fromName() {
			return $this->_fromName;
		}
		public function __set_fromName( $value ) {
			$this->_fromName = "{$value}";
		}

		protected $_fromAddr = "";
		public function __get_fromAddr() {
			return $this->_fromAddr;
		}
		public function __set_fromAddr( $value ) {
			$this->_fromAddr = "{$value}";
		}

		protected $_relayAddr = "";
		public function __get_relayAddr() {
			return $this->_relayAddr;
		}
		public function __set_relayAddr( $value ) {
			$this->_relayAddr = "{$value}";
		}

		protected $_protocol = "";
		public function __get_protocol() {
			return $this->_protocol;
		}
		public function __set_protocol( $value ) {
			$this->_protocol = "{$value}";
		}

		protected $_relayPort = 25;
		public function __get_relayPort() {
			return $this->_relayPort;
		}
		public function __set_relayPort( $value ) {
			return $this->_relayPort = CAST( $value, 'int strict', 25 );
		}

		protected $_timeout = 5;
		public function __get_conTimeout() {
			return $this->_timeout;
		}
		public function __set_conTimeout( $value ) {
			return $this->_relayPort = CAST( 'int strict', $value, 5 );
		}

		protected $_debugOutput = FALSE;
		public function __get_debugOutput() {
			return $this->_debugOutput;
		}
		public function __set_debugOutput( $value ) {
			$this->_debugOutput = !empty($value);
		}






		protected $_conSocket = NULL;
		protected function _connect()
		{
			if ( !($this->_conSocket = fsockopen("{$this->_protocol}{$this->_relayAddr}", $this->_relayPort, $errno, $errstr, $this->_timeout)) )
			{
				throw new PBException( array(
					'status' => PBMailKernel::ERROR_CONNECTION,
					'msg'	 => "Error connecting to \"{$this->_protocol}{$this->_relayAddr}\" ($errno) ($errstr)"
				));
			}
		}

		protected function _disconnect()
		{
			if ( $this->_conSocket )
				@fclose( $this->_conSocket );

			$this->_conSocket = NULL;
		}


		protected static function EatResponse( $socket, $expectedCode, $debugOutput = FALSE )
		{
			$lastResponse = '';
			while ( substr( $lastResponse, 3, 1 ) != ' ' )
			{
				if ( !( $lastResponse = fgets( $socket, 256 ) ) )
				{
					throw new PBException(array(
						'status'	=> PBMailKernel::ERROR_FETCHING_RESPONSE,
						'response'	=> $response
					));
				}
			}

			if ( $debugOutput ) echo trim($lastResponse) . EON;

			$statusCode = CAST( @substr( $lastResponse, 0, 3 ), 'int', 0 );
			if ( ($statusCode != $expectedCode) )
			{
				throw new PBException(array(
					'status'	=> PBMailKernel::ERROR_UNEXPECTED_RESPONSE,
					'code'		=> $statusCode,
					'expected'	=> $expectedCode,
					'response'	=> @substr( $lastResponse, 3 )
				));
			}
		}

		protected static function WriteContent( $socket, $content, $debugOutput = FALSE ) {
			if ( $debugOutput ) echo trim($content) . EON;

			fwrite( $socket, "{$content}\r\n" );
		}


	}

	class PBSMTPKernel extends PBMailKernel
	{
		public function process( $msg = NULL )
		{
			try
			{
				// region [ Process email information ]
				if ( !is_array( $msg ) )
				{
					throw new PBException(array(
						'status' => PBMailKernel::ERROR_INCORRECT_GIVEN_MSG,
						'msg'	 => "Given message is invalid"
					));
				}

				$subject	= "{$msg['subject']}";
				$content	= "{$msg['content']}";
				$recipients	= array();

				$parsed =
				ary_filter( array( 'to', 'cc', 'bcc' ), function( $field, &$idx ) use( &$msg, &$recipients ) {
					$idx = $field;

					$fieldData = empty($msg[$field]) ? array() : $msg[$field];
					$receivers = is_array($fieldData) ? $fieldData : array($fieldData);
					return ary_filter( $receivers, function( $email ) use( &$recipients ) {


						if ( !is_array($email) )
						{
							$addr = "<$email>";
							$name = "";
						}
						else
						{
							$addr = "<{$email['email']}>";
							$name = "\"{$email['name']}\" ";
						}

						$recipients[] = $addr;
						return "{$name}{$addr}";
					});
				});

				$to	 = $parsed[ 'to' ];
				$cc  = $parsed[ 'cc' ];
				// endregion



				// region [ SMTP Session ]
				$this->_connect();

				// region [ ESMTP Negotiation ]
				self::EatResponse( $this->_conSocket, 220, $this->_debugOutput );


				// INFO: Initiate ESMTP mode
				self::WriteContent( $this->_conSocket, "EHLO {$this->_relayAddr}", $this->_debugOutput );
				self::EatResponse( $this->_conSocket, 250, $this->_debugOutput );



				// INFO: AUTH Login Authentication protocol
				self::WriteContent( $this->_conSocket, "AUTH LOGIN", $this->_debugOutput );
				self::EatResponse( $this->_conSocket, 334, $this->_debugOutput );

				self::WriteContent( $this->_conSocket, base64_encode( $this->_account ), $this->_debugOutput );
				self::EatResponse( $this->_conSocket, 334, $this->_debugOutput );

				self::WriteContent( $this->_conSocket, base64_encode( $this->_password ), $this->_debugOutput );
				self::EatResponse( $this->_conSocket, 235, $this->_debugOutput );



				// INFO: Write envelope info
				self::WriteContent( $this->_conSocket, "MAIL FROM: <{$this->_fromAddr}>", $this->_debugOutput );
				self::EatResponse( $this->_conSocket, 250, $this->_debugOutput );

				foreach ( $recipients as $email ) {
					self::WriteContent( $this->_conSocket, "RCPT TO: {$email}", $this->_debugOutput );
					self::EatResponse( $this->_conSocket, 250, $this->_debugOutput );
				}



				// INFO: Start mail contents
				self::WriteContent( $this->_conSocket, "DATA", $this->_debugOutput );
				self::EatResponse( $this->_conSocket, 354, $this->_debugOutput );



				// region [ Headers ]
				$fromName = empty($this->_fromName) ? "" : "\"{$this->_fromName}\" ";
				self::WriteContent( $this->_conSocket, "From: {$fromName}<{$this->_fromAddr}>", $this->_debugOutput );

				// TO receivers
				if ( !empty($to) )
					self::WriteContent( $this->_conSocket, "To: " . implode( ', ', $to ), $this->_debugOutput );

				// CC receivers
				if ( !empty($cc) )
					self::WriteContent( $this->_conSocket, "Cc: " . implode( ' ', $cc ), $this->_debugOutput );

				// Date
				self::WriteContent( $this->_conSocket, "Date: " . date( "r" ), $this->_debugOutput );
				// endregion



				// INFO: Write mail body
				self::WriteContent( $this->_conSocket, "Subject: {$subject}", $this->_debugOutput );

				// Message Body
				self::WriteContent( $this->_conSocket, $content, $this->_debugOutput );



				// INFO: Finish writing
				self::WriteContent( $this->_conSocket, '.', $this->_debugOutput );
				self::EatResponse( $this->_conSocket, 250, $this->_debugOutput );



				// INFO: Quit session
				self::WriteContent( $this->_conSocket, 'QUIT', $this->_debugOutput );
				// endregion

				$this->_disconnect();
				// endregion

				return TRUE;
			}
			catch( PBException $e )
			{
				$descriptor = $e->descriptor;
				if ( $descriptor['status'] != PBMailKernel::ERROR_CONNECTION )
					$this->_disconnect();

				return $descriptor;
			}

		}
	}

	class PBGMailSMTP extends PBSMTPKernel
	{
		const GMAIL_SMTP_RELAY_PROTOCOL	= "ssl://";
		const GMAIL_SMTP_RELAY_ADDR		= "smtp.gmail.com";
		const GMAIL_SMTP_RELAY_PORT		= 465;

		public function __construct( $account, $password )
		{
			$this->account		= $account;
			$this->password		= $password;
			$this->_relayAddr	= self::GMAIL_SMTP_RELAY_ADDR;
			$this->_relayPort	= self::GMAIL_SMTP_RELAY_PORT;
			$this->_protocol	= self::GMAIL_SMTP_RELAY_PROTOCOL;
		}
	}
