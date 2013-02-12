<?php
/*
 * File: PBException.php
 * Created by Cloud.
 * DateTime: 13/2/10 PM12:16
 */

class PBException extends Exception
{
	public $_statusCode;

	public function __construct($code, $message = '', Exception $previous = null)
	{
		// In php version above 5.3, $previous is added for tracing back usage
		// See the official document http://www.php.net/manual/en/exception.construct.php
		if(strnatcmp(phpversion(),'5.3') >= 0)
			parent::__construct($message, $code, $previous);
		else
			parent::__construct($message, $code);
	}
}