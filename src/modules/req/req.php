<?php
/*
 * File: req.php
 * Created by Cloud.
 * DateTime: 13/4/6 PM6:09
 */

using('kernel.core.PBModule');
using('sys.tool.http.*');

class req extends PBModule
{
	private $_request = NULL;
	private static $validType = array('JS' 	=> 'text/javascript',
									  'CSS'	=> 'text/css',
									  'HTML'=> 'text/html',

									  'PDF'	=> 'application/pdf',
									  'JSON'=> 'application/json',
									  'XML'	=> 'application/xml',

									  'BMP'	=> 'image/bmp',
									  'JPG'	=> 'image/jpeg',
									  'PNG'	=> 'image/png',
									  'ICO'	=> 'image/vnd.microsoft.icon',
									  'GIF'	=> 'image/gif',
									  'TIF'	=> 'image/tiff',
									  'TIFF'=> 'image/tiff');

	public function prepare($moduleRequest) {

		$this->_request = is_string($moduleRequest) ? PBHTTP::ParseRequest($moduleRequest) : $moduleRequest;
	}

	public function exec($param) {
	
		$targetFile = implode('/', $this->_request['resource']);
		$filePath = __WORKING_ROOT__."/".$targetFile;
		if(is_file($filePath))
		{
			$ext = @strtoupper(pathinfo($targetFile, PATHINFO_EXTENSION));

			if(in_array($ext, array_keys(req::$validType)))
			{
				header("Content-Type: ".req::$validType[$ext]);
				readfile($filePath);

				exit();
			}
		}

		throw(new Exception("file {$filePath} not found!"));
	}
}