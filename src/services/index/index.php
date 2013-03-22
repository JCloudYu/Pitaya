<?php

using('kernel.core.PBModule');
using('kernel.http.PBHTTP');
class index extends PBModule
{
	protected $incomingRequest = NULL;

	public function prepare($moduleRequest) {

		$this->incomingRequest = PBHTTP::ParseRequest($moduleRequest);
		var_dump($this->incomingRequest);
	}

	public function exec($param) {


	}
}