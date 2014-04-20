<?php
	/**
	 * 0005.danshen - Layout.php
	 * Created by JCloudYu on 2014/04/02 23:43
	 */
	using('kernel.core.PBModule');

	class PBLayout implements ArrayAccess
	{
		private $_regions	 	= array();
		private $_modules		= array();
		private $_region_cache	= array();

		public function __construct($layoutStruct = array()) { $this->processLayout($layoutStruct); }

		public function processLayout($layoutStruct)
		{
			$this->_regions = $this->_modules = $this->_region_cache = array();
			$this->_regions[0] = $this->analyzeLayoutStructure($layoutStruct);
		}

		private function analyzeLayoutStructure($layer)
		{
			static $_anonymousCounter = 0;

			if (isset($layer['module']))
			{
				$module = PBProcess::Process()->getModule($layer['module'], FALSE);
				$module->tag = md5($module->id);
				$module->prepare($layer['request'], __CLASS__);

				$this->_modules[$module->tag] = $module;
				return $module->tag;
			}

			$layerInfo = array();
			foreach ($layer as $name => $content)
			{
				$regionName = (is_numeric($name)) ? md5($_anonymousCounter++) : $name;
				$this->_regions[$regionName] = $this->analyzeLayoutStructure($content);
				$layerInfo[] = $regionName;
			}

			return $layerInfo;
		}



		public function offsetGet($offset)
		{
			$result = $this->collectRegionContent($offset);
			return $result['content'];
		}

		public function collectRegionContent($regionName)
		{
			if (isset($this->_region_cache[$regionName]))
				return $this->_region_cache[$regionName];

			$regionCache = '';
			$isRegion = FALSE;
			if (isset($this->_regions[$regionName]))
			{
				$regionContent = $this->_regions[$regionName];

				if (!is_array($regionContent))
				{
					$module = @$this->_modules["{$regionContent}"];
					if (!empty($module))
					{
						$result = $module->exec(NULL, __CLASS__);
						$regionCache = "<div id='{$module->tag}' class='wrapper module-wrapper {$module->class}' role='module'>{$result}</div>";
						$isRegion = FALSE;
					}
				}
				else
				{
					foreach ($regionContent as $regionId)
					{
						$result = $this->collectRegionContent($regionId);
						$regionCache .= (!$result['isRegion']) ? $result['content'] : "<div id='{$regionId}' class='wrapper region-wrapper' role='region'>{$result['content']}</div>";
						$isRegion = TRUE;
					}
				}
			}

			$this->_region_cache[$regionName] = $regionCache;
			return array('isRegion' => $isRegion, 'content' => $regionCache);
		}


		public function offsetSet($offset, $value)	{}
		public function offsetUnset($offset)		{}
		public function offsetExists($offset)		{}
	}