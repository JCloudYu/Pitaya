<?php
	/**
	 ** 0002.pitaya.workbench - mail.php
	 ** Created by JCloudYu on 2015/09/07 03:02
	 **/

	function smtp_mail( $account, $password, $server, $port, $to, $subject, $message, $headers = '' )
	{
		PBLog::ShareLog( $account);
		PBLog::ShareLog( $password );
		PBLog::ShareLog( $server );
		PBLog::ShareLog( $port );
		PBLog::ShareLog( $to );
		PBLog::ShareLog( $subject );

		$recipients = explode( ',', $to );
		$user       = "{$account}";
		$pass       = "{$password}";


		$smtp_host = "{$server}";
		$smtp_port = $port;


		if ( !( $socket = fsockopen( $smtp_host, $smtp_port, $errno, $errstr, 15 ) ) )
		{
			$msg = "Error connecting to '$smtp_host' ($errno) ($errstr)";
			PBLog::ShareLog( $msg );
		}

		server_parse( $socket, '220' );

		fwrite( $socket, 'EHLO ' . $smtp_host . "\r\n" );
		server_parse( $socket, '250' );

		fwrite( $socket, 'AUTH LOGIN' . "\r\n" );
		server_parse( $socket, '334' );

		fwrite( $socket, base64_encode( $user ) . "\r\n" );
		server_parse( $socket, '334' );

		fwrite( $socket, base64_encode( $pass ) . "\r\n" );
		server_parse( $socket, '235' );

		fwrite( $socket, 'MAIL FROM: <' . $user . '>' . "\r\n" );
		server_parse( $socket, '250' );

		foreach ( $recipients as $email ) {
			fwrite( $socket, 'RCPT TO: <' . $email . '>' . "\r\n" );
			server_parse( $socket, '250' );
		}

		fwrite( $socket, 'DATA' . "\r\n" );
		server_parse( $socket, '354' );

		fwrite( $socket, 'Subject: '
					   . $subject . "\r\n" . 'To: <' . implode( '>, <', $recipients ) . '>'
					   . "\r\n" . $headers . "\r\n\r\n" . $message . "\r\n" );

		fwrite( $socket, '.' . "\r\n" );
		server_parse( $socket, '250' );

		fwrite( $socket, 'QUIT' . "\r\n" );
		fclose( $socket );

		return TRUE;
	}

	//Functin to Processes Server Response Codes
	function server_parse( $socket, $expected_response )
	{
		$server_response = '';
		while ( substr( $server_response, 3, 1 ) != ' ' ) {
			if ( !( $server_response = fgets( $socket, 256 ) ) )
			{
				$msg = 'Error while fetching server response codes.' . __FILE__ . __LINE__;
				PBLog::ShareLog( $msg );
			}
		}

		if ( !( substr( $server_response, 0, 3 ) == $expected_response ) )
		{
			$msg = 'Unable to send e-mail."' . $server_response . '"' . __FILE__ . __LINE__;
			PBLog::ShareLog( $msg );
		}
	}
