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
			return $this->_relayPort = CAST( 'int strict', $value, 25 );
		}

		protected $_timeout = 15;
		public function __get_conTimeout() {
			return $this->_timeout;
		}
		public function __set_conTimeout( $value ) {
			return $this->_relayPort = CAST( 'int strict', $value, 5 );
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


		protected static function EatResponse( $socket, $expectedCode )
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

		protected static function WriteContent( $socket, $content ) {
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

					return (!is_array(@$msg[$field])) ? array() : ary_filter( $msg[$field], function( $email ) use( &$recipients ) {


						if ( !is_array($email) )
						{
							$addr = "<$email>";
							$name = "";
						}
						else
						{
							$addr = "<{$email['email']}>";
							$name = "{$email['name']} ";
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
				self::EatResponse( $this->_conSocket, 220, __LINE__ );


				// INFO: Initiate ESMTP mode
				self::WriteContent( $this->_conSocket, "EHLO {$this->_relayAddr}" );
				self::EatResponse( $this->_conSocket, 250, __LINE__ );



				// INFO: AUTH Login Authentication protocol
				self::WriteContent( $this->_conSocket, "AUTH LOGIN" );
				self::EatResponse( $this->_conSocket, 334, __LINE__ );

				self::WriteContent( $this->_conSocket, base64_encode( $this->_account ) );
				self::EatResponse( $this->_conSocket, 334, __LINE__ );

				self::WriteContent( $this->_conSocket, base64_encode( $this->_password ) );
				self::EatResponse( $this->_conSocket, 235, __LINE__ );



				// INFO: Write envelope info
				self::WriteContent( $this->_conSocket, "MAIL FROM: <{$this->_account}>" );
				self::EatResponse( $this->_conSocket, 250, __LINE__ );

				foreach ( $recipients as $email ) {
					self::WriteContent( $this->_conSocket, "RCPT TO: {$email}" );
					self::EatResponse( $this->_conSocket, 250, __LINE__ );
				}



				// INFO: Start mail contents
				self::WriteContent( $this->_conSocket, "DATA" );
				self::EatResponse( $this->_conSocket, 354, __LINE__ );



				// INFO: Write mail body
				self::WriteContent( $this->_conSocket, "Subject: {$subject}" );

				// region [ Headers ]
				// TO receivers
				if ( !empty($to) )
					self::WriteContent( $this->_conSocket, "TO: " . implode( ', ', $to ) );

				// CC receivers
				if ( !empty($cc) )
					self::WriteContent( $this->_conSocket, "CC: " . implode( ' ', $cc ) );
				// endregion

				// Message Body
				self::WriteContent( $this->_conSocket, $content );



				// INFO: Finish writing
				self::WriteContent( $this->_conSocket, '.' );
				self::EatResponse( $this->_conSocket, 250, __LINE__ );



				// INFO: Quit session
				self::WriteContent( $this->_conSocket, 'QUIT' );
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
