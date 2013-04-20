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

		$request = array('resource' => $rawRequest[0], 'attribute' => '');
		if(count($rawRequest) > 1) $request['attribute'] = $rawRequest[1];

		$request['resource'] = explode('/', $request['resource']);
		$attributes = explode('&', $request['attribute']);

		$attributeContainer = array('unnamed' => array(), 'named' => array());
		foreach($attributes as $attr)
		{
			$buffer = preg_split('/[=:]/', $attr);

			if(count($buffer) <= 1)
			{
				if($buffer[0] !== '') $attributeContainer['unnamed'][] = $buffer[0];
			}
			else
			{
				if($buffer[0] !== '')
					$attributeContainer['named'][$buffer[0]] = $buffer[1];
				else
					$attributeContainer['unnamed'][] = $buffer[1];
			}
		}

		$request['attribute'] = $attributeContainer;

		return $request;
	}

	//SEC: Header Reply/////////////////////////////////////////////////////////////////////////////////////////////////////
	public static function ReplyStatus($status) {

		$statusMsg = PBHTTPStatus::$StatusString[$status];
		if(is_null($statusMsg)) {throw new Exception("Unsupported HTTP Status Code");}
		header("HTTP/1.1 $status $statusMsg");
		header("Status: $status $statusMsg");
		header("Version: HTTP/1.1");
	}


	public static function ReplyJSON()
	{

	}

	public static function ReplyHTML($blueprint, $param)
	{
		acquiring("sys.blueprint.$blueprint", $param);
	}
}

class_alias('PBHTTP', 'HTTP');