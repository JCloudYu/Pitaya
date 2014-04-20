<?php
	/**
	 * 0005.danshen - Layout.php
	 * Created by JCloudYu on 2014/04/02 23:43
	 */
	using('kernel.core.PBModule');

	class PBLayout implements ArrayAccess
	{
		private $_regions	 	= array();
		private $_region_cache	= array();

		public function __construct($layoutStruct = array()) { $this->processLayout($layoutStruct); }

		public function processLayout($layoutStruct)
		{
			$process = PBProcess::Process();
			$this->_regions = $this->_region_cache = array();

			if (empty($layoutStruct)) return;

			foreach ($layoutStruct as $regionName => $regionContent)
			{
				$this->_regions[$regionName] = array();
				foreach ($regionContent as $moduleConf)
				{
					$module = $process->getModule($moduleConf['module'], FALSE);
					$module->tag = md5($module->id);
					$this->_regions[$regionName][] = $module;
					$module->prepare($moduleConf['request'], __CLASS__);
				}
			}
		}

		public function offsetGet($offset)
		{
			if (isset($this->_region_cache[$offset]))
				return $this->_region_cache[$offset];

			$resultCache = '';

			if (!empty($this->_regions[$offset]))
			{
				foreach ($this->_regions[$offset] as $module)
				{
					$result = $module->exec(NULL, __CLASS__);
					$resultCache .= "<div modId='{$module->tag}' module='{$module->class}'>{$result}</div>";
				}
			}

			$this->_region_cache[$offset] = $resultCache;
			return $resultCache;
		}



		public function offsetSet($offset, $value)	{}
		public function offsetUnset($offset)		{}
		public function offsetExists($offset)		{}
	}