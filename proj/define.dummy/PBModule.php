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

		public $process = new PBProcess();
		public $bootStrap = array();

		public $id = '';
		public $processId = '';

		public function __construct()
		{
			$this->process = new PBProcess();
		}


		public function prepare($moduleRequest) {


		}

		public function exec($param) {

		}

	}