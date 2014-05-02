<?php
/**
 * 1003.IMSIS - PBPageModule.php
 * Created by JCloudYu on 2014/05/02 16:49
 */ 
	using('kernel.core.PBModule');

	class PBPageModule extends PBModule
	{
		protected $_logic			= '';

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

		public function execPage($param = NULL) { return parent::exec($param); }
		public function execModule($param = NULL)
		{
			$func	= "exec_{$this->_logic}";
			$result = (method_exists($this, $func)) ? $this->{$func}($param) : '';
			return "{$result}";
		}
	}
