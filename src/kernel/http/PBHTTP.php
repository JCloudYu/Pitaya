<?php
/*
 * File: PBHTTP.php
 * Created by Cloud.
 * DateTime: 13/3/7 PM7:29
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
			$buffer = preg_split('/[=:]+/', $attr);
			if(count($buffer) <= 1)
			{
				if($buffer[0] !== '') $attributeContainer['unnamed'][] = $buffer[0];
			}
			else $attributeContainer['named'][$buffer[0]] = $buffer[1];
		}

		$request['attribute'] = $attributeContainer;

		return $request;
	}
}