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
	const DEFAULT_ACCEPTABLE_TYPES = '	{	"JS":	"text/javascript",
											"CSS":	"text/css",
											"HTML":	"text/html",

											"PDF":	"application/pdf",
											"JSON":	"application/json",
											"XML":	"application/xml",

											"BMP":	"image/bmp",
											"JPG":	"image/bmp",
											"PNG":	"image/bmp",
											"ICO":	"image/bmp",
											"GIF":	"image/bmp",
											"TIF":	"image/bmp",
											"TIFF":	"image/bmp",

											"WAV":	"audio/wav",
											"AVI":	"video/avi",

											"TXT":	"text/plain"
										}';

	private $_request = NULL;
	private $_acceptTypes = NULL;

	public function __set_acceptableExt($value) { if (is_array($value)) $this->_acceptTypes = $value; }

	public function prepare($moduleRequest)
	{
		if (empty($this->_acceptTypes)) $this->_acceptTypes = json_decode(self::DEFAULT_ACCEPTABLE_TYPES);

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