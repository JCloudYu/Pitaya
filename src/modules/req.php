<?php
/*
 * File: req.php
 * Created by Cloud.
 * DateTime: 13/4/6 PM6:09
 */

using('kernel.core.PBModule');
using('sys.tool.http.*');

define('DEFAULT_ACCEPTABLE_TYPES', array('JS' 	=> 'text/javascript',
										 'CSS'	=> 'text/css',
										 'HTML'	=> 'text/html',
										 'PDF'	=> 'application/pdf',
										 'JSON'	=> 'application/json',
										 'XML'	=> 'application/xml',

										 'BMP'	=> 'image/bmp',
										 'JPG'	=> 'image/jpeg',
										 'PNG'	=> 'image/png',
										 'ICO'	=> 'image/vnd.microsoft.icon',
										 'GIF'	=> 'image/gif',
										 'TIF'	=> 'image/tiff',
										 'TIFF'	=> 'image/tiff',

										 'WAV'	=> 'audio/wav',
										 'AVI'	=> 'video/avi',

										 'TXT'	=> 'text/plain'), TRUE);

class req extends PBModule
{ 
	private $_request = NULL;
	private $_acceptTypes = NULL;

	public function __set_acceptTypes($value) { if (is_array($value)) $this->_acceptTypes = $value; }

	public function prepare($moduleRequest)
	{
		if (empty($this->_acceptTypes)) $this->_acceptTypes = DEFAULT_ACCEPTABLE_TYPES;

		if (is_string($moduleRequest))
		{
			$this->_request = PBHTTP::ParseRequest($moduleRequest);
			$this->_request['resource'];
		}
		else
			$this->_request = $moduleRequest;
	}

	public function exec($param) {
	
		$targetFile = implode('/', $this->_request);
		$filePath = __WORKING_ROOT__."/".$targetFile;
		if(is_file($filePath))
		{
			$ext = @strtoupper(pathinfo($targetFile, PATHINFO_EXTENSION));

			if(in_array($ext, array_keys($this->_acceptTypes)))
			{
				header("Content-Type: ".$this->_acceptTypes[$ext]);
				readfile($filePath);

				exit();
			}
		}

		throw(new Exception("file {$filePath} not found!"));
	}
}