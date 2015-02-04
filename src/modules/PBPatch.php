<?php
/**
 * 0009.sampee - PBPatch.php
 * Created by JCloudYu on 2014/06/15 22:27
 */
	using('kernel.core.PBModule');
	using('ext.base.string');

	class PBPatch extends PBModule
	{
		private $_patchDir = "share.patch";
		public function __set_patchDir($value) { $this->_patchDir = $value; }


		private $_targetVersion = NULL;
		public function prepare($moduleRequest) { $this->prepareEvent($moduleRequest); }
		public function prepareEvent($moduleRequest) { $this->_targetVersion = $moduleRequest; }

		public function exec($param) { return $this->event($param); }
		public function event($param)
		{
			if ($this->_targetVersion !== NULL)
				$param = $this->_targetVersion;

			if (ParseVersion("{$param}") === NULL)
			{
				if ( SYS_WORKING_ENV == SYS_ENV_CLI )
					PBStdIO::STDERR("Given parameter is not a valid version format! ({$param})");

				PBLog::ERRLog("Given parameter is not a valid version format!");

				return;
			}

			$patchDir = path($this->_patchDir);
			if ( !is_dir($patchDir) )
			{
				if ( !is_dir($this->_patchDir) )
				{
					if ( SYS_WORKING_ENV == SYS_ENV_CLI )
						PBStdIO::STDERR("Patch directory is not a valid directory! ({$this->_patchDir})");

					PBLog::ERRLog("Patch directory is not a valid directory!");
					return;
				}

				$patchDir = $this->_patchDir;
			}

			$patchList = array();
			$dh  = opendir($patchDir);
			while (false !== ($filename = readdir($dh)))
			{
				if (is_dir("{$patchDir}/{$filename}") || (substr($filename, -4) != ".php")) continue;

				$filename = substr($filename, 0, -4);
				$result = CompareVersion("{$filename}", "{$param}");

				if ($result === FALSE || $result <= 0) continue;
				$patchList[] = $filename;
			}

			if (empty($patchList))
			{
				PBStdIO::STDOUT("Nothing to patch!");
				return;
			}

			usort($patchList, "CompareVersion");


			$CWD = getcwd();
			chdir($patchDir);

			PBStdIO::STDOUT("Start patching...");
			foreach ($patchList as $version)
			{
				PBStdIO::STDOUT("Patching {$version}...");
				ScriptOut("{$patchDir}/{$version}.php");
				PBStdIO::STDOUT("");
			}

			PBStdIO::STDOUT("Patch done!");

			chdir($CWD);
		}
	}
