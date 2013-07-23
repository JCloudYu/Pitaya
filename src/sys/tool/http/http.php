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
		$rawRequest = explode('?', $rawRequest);

		$request = array('resource' => $rawRequest[0], 'attribute' => NULL);
		if(count($rawRequest) > 1) $request['attribute'] = $rawRequest[1];

		$request['resource'] = explode('/', $request['resource']);
		if($request['resource'][0] === '') $request['resource'] = array();

		$request['attribute'] = PBHTTP::ParseAttribute($request['attribute']);

		return $request;
	}

	public static function ParseAttribute($rawAttribute)
	{
		$attributes = explode('&', $rawAttribute);

		if (empty($attributes)) return array();
		$attributeContainer = array('flag' => array(), 'variable' => array());
		foreach($attributes as $attr)
		{
			$buffer = preg_split('/[=:]/', $attr);

			if(count($buffer) <= 1)
			{
				if($buffer[0] !== '') $attributeContainer['flag'][] = $buffer[0];
			}
			else
			{
				if($buffer[0] !== '')
					$attributeContainer['variable'][$buffer[0]] = $buffer[1];
				else
					$attributeContainer['flag'][] = $buffer[1];
			}
		}

		return $attributeContainer;
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