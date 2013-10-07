<?php
	/*
	 * File: PBModule.php
	 * Created by Cloud.
	 * DateTime: 13/2/10 PM6:51
	 */

	class PBModule extends PBObject
	{
		private $_moduleId = NULL;
		private $_process = NULL;

		private $_bootstrap = array();

		/**
		 * @var PBProcess
		 */
		public $process = NULL;
		public $bootStrap = array();

		public $id = '';
		public $processId = '';

		public function __construct()
		{
			$this->process = new PBProcess();
		}


		public function prepare($moduleRequest)          {}
		public function prepareInstall($moduleRequest)   {}
		public function prepareUpdate($moduleRequest)    {}
		public function preparePatch($moduleRequest)     {}
		public function prepareUninstall($moduleRequest) {}

		public function exec($param) {}
		public function install($param) {}
		public function update($param) {}
		public function patch($param) {}
		public function uninstall($param) {}
	}