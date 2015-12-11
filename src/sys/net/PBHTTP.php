<?php
/**
 * server - http.php
 * Created by JCloudYu on 2014/12/13 04:57
 */

	final class PBHTTP
	{
		public static function ResponseStatus( $status )
		{
			$statusMsg = self::GetStatusString( $status );
			if ( empty($statusMsg) ) throw new Exception("Unsupported HTTP Status Code");

			header("HTTP/1.1 {$status} {$statusMsg}");
			header("Status: {$status} {$statusMsg}");
		}
		public static function ResponseJSON( $obj, $status = NULL ) { self::ResponseContent( json_encode($obj), "application/json", $status ); }
		public static function ResponseContent( $content, $contentType = "text/plain", $status = NULL )
		{
			if ( $status !== NULL ) self::ResponseStatus( $status );
			header("Content-Type: {$contentType}");



			if ( !is_resource($content) )
			{
				echo "{$content}";
				return;
			}

			$output = fopen( "php://output", "a+b" );
			stream_copy_to_stream( $content, $output );
			fclose($output);
		}


		// region [ HTTP Status Code ]
		//SEC: Information
		const STATUS_100_CONTINUE								= 100;
		const STATUS_101_SWITCHING_PROTOCOLS					= 101;
		const STATUS_102_PROCESSING								= 102;

		//SEC: Success
		const STATUS_200_OK										= 200;
		const STATUS_201_CREATED								= 201;
		const STATUS_202_ACCEPTED								= 202;
		const STATUS_203_NON_AUTHORITATIVE_INFORMATION			= 203;
		const STATUS_204_NO_CONTENT								= 204;
		const STATUS_205_RESET_CONTENT							= 205;
		const STATUS_206_PARTIAL_CONTENT						= 206;
		const STATUS_207_MULTI_STATUS							= 207;
		const STATUS_208_ALREADY_REPORTED						= 208;
		const STATUS_226_IM_USED								= 226;

		//SEC: Redirection
		const STATUS_300_MULTIPLE_CHOICES						= 300;
		const STATUS_301_MOVED_PERMANENTLY						= 301;
		const STATUS_302_FOUND									= 302;
		const STATUS_303_SEE_OTHER								= 303;
		const STATUS_304_NOT_MODIFIED							= 304;
		const STATUS_305_USE_PROXY								= 305;
		const STATUS_306_SWITCH_PROXY							= 306;
		const STATUS_307_TEMPORARY_REDIRECT						= 307;
		const STATUS_308_PERMANENT_REDIRECT						= 308;

		//SEC: Client Error
		const STATUS_400_BAD_REQUEST							= 400;
		const STATUS_401_UNAUTHORIZED							= 401;
		const STATUS_402_PAYMENT_REQUIRED						= 402;
		const STATUS_403_FORBIDDEN								= 403;
		const STATUS_404_NOT_FOUND								= 404;
		const STATUS_405_METHOD_NOT_ALLOWED						= 405;
		const STATUS_406_NOT_ACCEPTABLE							= 406;
		const STATUS_407_PROXY_AUTHENTICATION_REQUIRED			= 407;
		const STATUS_408_REQUEST_TIMEOUT						= 408;
		const STATUS_409_CONFLICT								= 409;
		const STATUS_410_GONE									= 410;
		const STATUS_411_LENGTH_REQUIRED						= 411;
		const STATUS_412_PRECONDITION_FAILED					= 412;
		const STATUS_413_REQUEST_ENTITY_TOO_LARGE				= 413;
		const STATUS_414_REQUEST_URI_TOO_LONG					= 414;
		const STATUS_415_UNSUPPORTED_MEDIA_TYPE					= 415;
		const STATUS_416_REQUEST_RANGE_NOT_SATISFIABLE			= 416;
		const STATUS_417_EXPECTATION_FAILED						= 417;
		const STATUS_418_IM_A_TEAPOT							= 418;
		const STATUS_420_ENHANCE_YOUR_CALM						= 420;
		const STATUS_422_UNPROCESSABLE_ENTITY					= 422;
		const STATUS_423_LOCKED									= 423;
		const STATUS_424_FAILED_DEPENDENCY						= 424;
		const STATUS_424_METHOD_FAILURE							= 424;
		const STATUS_425_UNORDERED_COLLECTION					= 425;
		const STATUS_426_UPGRADE_REQUIRED						= 426;
		const STATUS_428_PRECONDITION_REQUIRED 					= 428;
		const STATUS_429_TOO_MANY_REQUESTS						= 429;
		const STATUS_431_REQUEST_HEADER_FIELDS_TOO_MANY			= 431;
		const STATUS_444_NO_RESPONSE							= 444;
		const STATUS_449_RETRY_WITH								= 449;
		const STATUS_450_BLOCKED_BY_WINDOWS_PARENTAL_CONTROLS	= 450;
		const STATUS_451_UNAVAILABLE_FOR_LEGAL_REASONS			= 451;
		const STATUS_451_REDIRECT								= 451;
		const STATUS_494_REQUEST_HEADER_TOO_LARGE				= 494;
		const STATUS_495_CERT_ERROR								= 495;
		const STATUS_496_NO_CERT								= 496;
		const STATUS_497_HTTP_TO_HTTPS							= 497;
		const STATUS_499_CLIENT_CLOSED_REQUEST					= 499;

		//SEC: Server Error
		const STATUS_500_INTERNAL_SERVER_ERROR					= 500;
		const STATUS_501_NOT_IMPLEMENTED						= 501;
		const STATUS_502_BAD_GATEWAY							= 502;
		const STATUS_503_SERVICE_UNAVAILABLE					= 503;
		const STATUS_504_GATEWAY_TIMEOUT						= 504;
		const STATUS_505_HTTP_VERSION_NOT_SUPPORTED				= 505;
		const STATUS_506_VARIANT_ALSO_NEGOTIATES				= 506;
		const STATUS_507_INSUFFICIENT_STORAGE					= 507;
		const STATUS_508_LOOP_DETECTED							= 508;
		const STATUS_509_BANDWIDTH_LIMIT_EXCEEDED				= 509;
		const STATUS_510_NOT_EXTENDED							= 510;
		const STATUS_511_NETWORK_AUTHENTICATION_REQUIRED		= 511;
		const STATUS_598_NETWORK_READ_TIMEOUT					= 598;
		const STATUS_599_NETWORK_CONNECT_TIMEOUT_ERROR			= 599;
		// endregion

		public static function GetStatusString($status)
		{
			static $STATUS_STRING = array(
				100 => 'Continue',
				101 => 'Switching Protocols',
				102 => 'Processing',

				200 =>'OK',
				201 =>'Created',
				202 =>'Accepted',
				203 =>'Non-Authoritative Information',
				204 =>'No Content',
				205 =>'Reset Content',
				206 =>'Partial Content',
				207 =>'Multi-Status',
				208 =>'Already Reported',
				226 =>'IM Used',

				300 =>'Multiple Choices',
				301 =>'Moved Permanently',
				302 =>'Found',
				303 =>'See Other',
				304 =>'Not Modified',
				305 =>'Use Proxy',
				306 =>'Switch Proxy',
				307 =>'Temporary Redirect',
				308 =>'Permanent Redirect',

				400 =>'Bad Request',
				401 =>'Unauthorized',
				402 =>'Payment Required',
				403 =>'Forbidden',
				404 =>'Not Found',
				405 =>'Method Not Allowed',
				406 =>'Not Acceptable',
				407 =>'Proxy Authentication Required',
				408 =>'Request Timeout',
				409 =>'Conflict',
				410 =>'Gone',
				411 =>'Length Required',
				412 =>'Precondition Failed',
				413 =>'Request Entity Too Large',
				414 =>'Request-URI Too Long',
				415 =>'Unsupported Media Type',
				416 =>'Request Range Not Satisfiable',
				417 =>'Expectation Failed',
				418 =>'I\'m a teapot',
				420 =>'Enhance Your Calm',
				422 =>'Unprocessable Entity',
				423 =>'Locked',
				424 =>'Failed Dependency',
//				424 =>'Method Failure',
				425 =>'Unordered Collection',
				426 =>'Upgrade Required',
				428 =>'Precondition Required',
				429 =>'Too Many Requests',
				431 =>'Request Header Fields Too Large',
				444 =>'No Response',
				449 =>'Retry With',
				450 =>'Blocked by Windows Parental Controls',
				451 =>'Unavailable For Legal Reasons',
//				451 =>'Redirect',
				494 =>'Request Header Too Large',
				495 =>'Cert Error',
				496 =>'No Cert',
				497 =>'HTTP to HTTPS',
				499 =>'Client Closed Request',

				500 =>'Internal Server Error',
				501 =>'Not Implemented',
				502 =>'Bad Gateway',
				503 =>'Service Unavailable',
				504 =>'Gateway Timeout',
				505 =>'HTTP Version Not Supported',
				506 =>'Variant Also Negotiates',
				507 =>'Insufficient Storage',
				508 =>'Loop Detected',
				509 =>'Bandwidth Limit Exceeded',
				510 =>'Not Extended',
				511 =>'Network Authentication Required',
				598 =>'Network read timeout error',
				599 =>'Network connect timeout error',
			);
			return $STATUS_STRING[$status];
		}
	}
