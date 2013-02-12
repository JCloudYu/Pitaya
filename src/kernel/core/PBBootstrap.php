<?php
/*
 * File: PBBootstrap.php
 * Created by Cloud.
 * DateTime: 13/2/12 PM8:08
 */

class PBBootstrap extends PBObject
{
	private $_rawBootStrap;

	public static function parseBootstrap($rawFile)
	{
		$rawBootstrap = json_decode($rawFile, TRUE);
		if($rawBootstrap === NULL) return NULL;

		$rawBootstrap = new PBBootstrap($rawBootstrap);
		return $rawBootstrap;
	}

	private function __construct($rawAry) {

		switch($rawAry['version'])
		{
			case "1.0":
			case NULL:
			default:
				$this->_rawBootStrap = self::__purge($rawAry);
				break;
		}
	}

	public function __destruct() {


	}

	public function __get_sequences() {

		return $this->_rawBootStrap['sequences'] ? $this->_rawBootStrap['sequences'] : array();
	}

	public function __get_tools() {

		return $this->_rawBootStrap['tools'] ? $this->_rawBootStrap['tools'] : array();
	}

	public function __get_data() {

		return $this->_rawBootStrap['data'] ? $this->_rawBootStrap['data'] : array();
	}

	public function __get_text() {

		return $this->_rawBootStrap['text'] ? $this->_rawBootStrap['text'] : array();
	}

	private static function __purge($rawAry) {

		$arrangedAry = array();

		foreach($rawAry as $config => $content)
		{
			if(!is_array($content)) throw(new Exception("Bootstrap paring failed due to unresolvable architecture in \"$config\""));

			switch($config)
			{
				case "sequences":
					$defaultAttribute = array('reuse' => FALSE);
					foreach($content as $moduleConf)
					{
						$module = $moduleConf['module'];
						$moduleAttr = array();
						if($module == NULL || !is_string($module)) throw(new Exception("Bootstrap paring failed due to unresolvable architecture in \"$config\" near \"$moduleConf\""));
						if(!is_array($moduleConf['attr']))
							$moduleAttr = $defaultAttribute;
						else
							foreach($defaultAttribute as $key => $value) $moduleAttr[$key] = $moduleConf['attr'][$key] !== NULL ? $moduleConf['attr'][$key] : $defaultAttribute[$key];

						$arrangedAry['sequences'][] = array("module" => $module, "attr" => $moduleAttr);
					}
					break;
				case "tools":
					$defaultAttribute = array('request' => NULL);
					foreach($content as $moduleConf)
					{
						$module = $moduleConf['module'];
						$moduleAttr = array();
						if($module == NULL || !is_string($module)) throw(new Exception("Bootstrap paring failed due to unresolvable architecture in \"$config\" near \"$moduleConf\""));
						if(!is_array($moduleConf['attr']))
							$moduleAttr = $defaultAttribute;
						else
							foreach($defaultAttribute as $key => $value) $moduleAttr[$key] = $moduleConf['attr'][$key] !== NULL ? $moduleConf['attr'][$key] : $defaultAttribute[$key];

						$arrangedAry['tools'][] = array("module" => $module, "attr" => $moduleAttr);
					}
					break;
					break;
				case "data":
					foreach($content as $name => $value)
						$arrangedAry['data'][$name] = $value;
					break;
				case "text":
					foreach($content as $name => $value)
						$arrangedAry['text'][$name] = $value;
					break;
				default: break;
			}
		}

		return $arrangedAry;
	}
}