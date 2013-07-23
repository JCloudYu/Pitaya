<?php
/*
 * File: http.php
 * Created by Cloud.
 * DateTime: 13/4/20 PM11:23
 */

class PBHTTP
{
	public static function ParseRequest($rawRequest)
	{
		return PBRequest::ParseRequest($rawRequest);
	}

	//SEC: Header Reply/////////////////////////////////////////////////////////////////////////////////////////////////////
	public static function ReplyStatus($status) {

		$statusMsg = PBHTTPStatus::$StatusString[$status];
		if(is_null($statusMsg)) {throw new Exception("Unsupported HTTP Status Code");}
		header("HTTP/1.1 $status $statusMsg");
		header("Status: $status $statusMsg");
		header("Version: HTTP/1.1");
	}


	public static function ReplyJSON($obj)
	{
		header("Content-type: " . MIME::JSON);
		$obj = json_encode($obj);
		echo "$obj";
	}
}