<?php
/*
 * File: WebPage.php
 * Created by Cloud.
 * DateTime: 13/4/23 AM12:45
 */

using('sys.tool.http.*');
using('kernel.core.PBModule');

// INFO: root/WebPage/mod/mod_descriptor?request_extended_attributes
// INFO:          ▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔ ▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔
// INFO:                resource         retrieving attribute

class PBWebPage extends PBModule
{
	protected $_pageRequest = NULL;
	protected $_targetPage = NULL;

	protected $_pageOrg = NULL;

	public function prepare($moduleRequest) {

		$this->_pageRequest = is_string($moduleRequest) ? HTTP::ParseRequest($moduleRequest) : array();
		$this->_targetPage = count($this->_pageRequest['resource']) > 0 ? array_shift($this->_pageRequest['resource']) : 'index';

		$this->_pageOrg = layout($this->_targetPage);

		$this->bootStrap = $this->__prepareModules();
	}

	public function exec($param) {

		if(@$this->_pageRequest['resource'][0] === 'favicon.ico')
		{
			header('Content-Type: image/vnd.microsoft.icon');
			readfile(__WORKING_ROOT__.'/favicon.ico');
			exit();
		}

		$pageStruct = array();
		foreach(array_keys($this->_pageOrg['regions']) as $region)
			$pageStruct[$region] = array('template' => 'region', 'content' => array());


		return array('pageTpl' => $this->_targetPage, 'page' => $pageStruct);
	}

	protected function __prepareModules() {

		$regionData = $this->_pageOrg['regions'];
		$procedure = array();

		foreach($regionData as $regionName => $region)
		{
			foreach($region as $module)
			{
				$item = array();
				$item['module'] = $module['module'];
				$item['request'] = array("region"   => $regionName,
										 "behavior" => $module['behavior']);
				$procedure[] = $item;
			}
		}


		$procedure[] = array('module' => 'html', 'request' => NULL, 'reuse' => TRUE);

		return $procedure;
	}
}

class_alias('PBWebPage', 'WebPage');