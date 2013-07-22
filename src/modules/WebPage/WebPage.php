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

	protected $_mainLogic = 'web';

	public function prepare($moduleRequest) {

		$this->_pageRequest = is_string($moduleRequest) ? PBHTTP::ParseRequest($moduleRequest) :
														  array('resource' => NULL, 'attribute' => NULL);

		if(strtoupper(@$this->_pageRequest['resource'][0]) == 'RC')
		{
			$this->_mainLogic = "File";

			$this->_pageRequest['resource'] = array_slice($this->_pageRequest['resource'], 1);
			$this->bootStrap = array(array('module' => 'req', 'request' => $this->_pageRequest, 'reuse' => FALSE));
		}
		else
		{
			$this->_targetPage = count($this->_pageRequest['resource']) > 0 ? array_shift($this->_pageRequest['resource']) : 'index';

			$this->_pageOrg = layout($this->_targetPage);
			$this->bootStrap = $this->__prepareModules();
		}
	}

	public function exec($param) {

		return $this->{"process{$this->_mainLogic}"}($param);
	}

	private function processWeb($param) {

		$pageStruct = array();
		foreach(array_keys($this->_pageOrg['regions']) as $region)
			$pageStruct[$region] = array('template' => 'region', 'content' => array());


		return array('pageTpl' => $this->_targetPage, 'page' => $pageStruct);
	}

	private function processFile($param) {

		return NULL;
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