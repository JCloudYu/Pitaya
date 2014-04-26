<?php
/**
 * 0005.danshen - PBPage.php
 * Created by JCloudYu on 2014/04/19 22:00
 */
	using('kernel.core.PBModule');
	using('ext.base.misc');
	using('kernel.tool.html.PBLayout');

	abstract class PBPage extends PBModule
	{
		private $_layoutPath	= '';
		private $_layoutStuct	= array();
		private $_layoutObj		= NULL;
		private $_logic			= '';



		private $_tplPath		= '';

		public function __construct() { $this->_layoutObj = new PBLayout(); }

		public function prepare($moduleRequest, $taggingFlag = NULL)
		{
			if ($taggingFlag == 'PBLayout')
				$this->prepareModule($moduleRequest);
			else
				$this->preparePage($moduleRequest);
		}

		public function preparePage($moduleRequest = NULL) {}
		public function prepareModule($moduleRequest = NULL)
		{
			$this->_logic = $moduleRequest['logic'];
			$func = "prepare_{$this->_logic}";

			unset($moduleRequest['logic']);

			if (method_exists($this, $func))
				$this->{$func}($moduleRequest);
		}



		public function exec($param = NULL, $taggingFlag = NULL)
		{
			return ($taggingFlag == 'PBLayout') ? $this->execModule($param) : $this->execPage($param);
		}

		public function execPage($param = NULL) { return $this->render($this->_layoutObj); }
		public function execModule($param = NULL)
		{
			$func	= "exec_{$this->_logic}";
			$result = (method_exists($this, $func)) ? $this->{$func}($param) : '';
			return "{$result}";
		}



		protected function __get_layoutPath() { return $this->_layoutPath; }
		protected function __set_layoutPath($layoutPath)
		{
			if (!file_exists($layoutPath))
			{
				$this->_layoutPath  = '';
				$this->_layoutStuct = array();
				return;
			}



			$layout = json_decode(file_get_contents($layoutPath), TRUE);
			if (!is_array($layout))
			{
				$this->_layoutPath  = '';
				$this->_layoutStuct = array();
				return;
			}



			$this->_layoutStuct = $layout;
			$this->_layoutPath  = $layoutPath;
			$this->_layoutObj->processLayout($this->_layoutStuct);
		}

		protected function __get_layout(){ return $this->_layoutStuct; }
		protected function __set_layout($layoutStruct)
		{
			$this->_layoutPath = '';
			$this->_layoutStuct = (!is_array($layoutStruct)) ? array() : $layoutStruct;
			$this->_layoutObj->processLayout($this->_layoutStuct);
		}

		protected function __get_tplPath() { return $this->_tplPath; }
		protected function __set_tplPath($path) { $this->_tplPath = (file_exists($path)) ? $path : ''; }


		protected function render($layout)
		{
			if (empty($this->_tplPath)) return '';
			return Script($this->_tplPath, array('layout' => $layout));
		}
	}