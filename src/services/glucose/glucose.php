<?php
/*
 * File: glucose.php
 * Created by Cloud.
 * DateTime: 13/3/7 PM10:02
 */

using('kernel.core.PBModule');
using('kernel.http.PBHTTP');
class glucose extends PBModule
{
	private $incomingRequest = NULL;

	public function prepare($moduleRequest) {

		$this->incomingRequest = PBHTTP::ParseRequest($moduleRequest);
	}

	public function exec($param) {

		$info = SYS::Info();

		print_r($info->data);
	}
}