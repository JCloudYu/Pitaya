<?php
/*
 * File: req.php
 * Created by Cloud.
 * DateTime: 13/4/6 PM6:09
 */

using('kernel.http.PBHTTP');
using('kernel.core.PBModule');

class req extends PBModule
{
	private $_request = NULL;
	private $_validReqType = array('JS', 'CSS', 'HTML',
								   'BMP', 'JPG', 'PNG', 'ICO');

	public function prepare($moduleRequest) {

		$this->_request = PBHTTP::ParseRequest($moduleRequest);
	}

	public function exec($param) {

		if($this->_request['req'][0] === 'favicon.ico')
		{
			header('Content-Type: image/vnd.microsoft.icon');
			readfile(__ROOT__.'/favicon.ico');
		}
	}
}