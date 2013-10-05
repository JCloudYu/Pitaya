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
	const DEFAULT_ACCEPTABLE_TYPES = '	{	"JS":		"text/javascript",
											"CSS":		"text/css",
											"HTML":		"text/html",
											"TXT":		"text/plain",
											"CSV":		"text/csv",
											"HTML":		"text/html",
											"HTM":		"text/html",
											"VCARD":	"text/vcard",
											"VCF":		"text/vcard"

											"PDF":		"application/pdf",
											"PS":		"application/postscript",

											"JSON":		"application/json",
											"XML":		"application/xml",

											"BMP":		"image/bmp",
											"JPG":		"image/jpeg",
											"PNG":		"image/png",
											"ICO":		"image/x-icon",
											"GIF":		"image/gif",
											"TIF":		"image/tiff",
											"TIFF":		"image/tiff",

											"WAV":		"audio/wav",
											"MP3":		"audio/mpeg",
											"OGG":		"audio/ogg",
											"AAC":		"audio/x-aac",

											"AVI":		"video/avi",
											"MP4":		"video/mp4",

											"ZIP":		"application/zip",
											"GZ":		"application/gzip",
											"TGZ":		"application/gzip"
										}';

	private $_request = NULL;
	private $_acceptTypes = NULL;

	public function __set_acceptableExt($value) { if (is_array($value)) $this->_acceptTypes = $value; }

	public function prepare($moduleRequest)
	{
		if (empty($this->_acceptTypes)) $this->_acceptTypes = json_decode(self::DEFAULT_ACCEPTABLE_TYPES, TRUE);

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