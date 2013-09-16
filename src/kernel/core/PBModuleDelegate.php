<?php
	/**
	 * ModuleDelegate - ModuleDelegate.php
	 * Created by JCloudYu on 2013/08/25 12:05
	 */

	interface PBModuleDelegate
	{
		public function prepare($moduleRequest = NULL);
		public function exec($param = NULL);
	}