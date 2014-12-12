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
		public function prepareEvent($moduleRequest) { $this->prepareUpdate($moduleRequest); }
		public function prepareUpdate($moduleRequest) { $this->_targetVersion = $moduleRequest; }

		public function event($event)
		{
			$this->update($event['data']);
			$event['propagation'] = FALSE;
			return $event;
		}
		public function update($param = NULL)
		{
			if ($this->_targetVersion !== NULL && $param === NULL)
				$param = $this->_targetVersion;

			if (ParseVersion("{$param}") === NULL)
			{
				PBLog::ERRLog("Given parameter is not a valid version format!");
				return;
			}

			$patchDir = path($this->_patchDir);
			if (!is_dir($patchDir))
			{
				PBLog::ERRLog("Patch directory doesn't exist!");
				return;
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
				PBPatch::Log("Nothing to patch!", FALSE);
				return;
			}

			usort($patchList, "CompareVersion");


			$CWD = getcwd();
			chdir($patchDir);

			PBPatch::Log("Start patching...", FALSE);
			PBPatch::Log("", FALSE);
			PBPatch::INDENT();



			foreach ($patchList as $version)
			{
				PBPatch::Log("Patching {$version}...", FALSE);
				PBPatch::INDENT();

					ScriptOut("{$patchDir}/{$version}.php");

				PBPatch::UNINDENT();
				PBPatch::Log("Patch done!", FALSE);
				PBPatch::Log("", FALSE);
			}



			PBPatch::UNINDENT();
			PBPatch::Log("All patches are done!!", FALSE);

			chdir($CWD);
		}



		private static $_indentedTabs = 0;
		public static function INDENT() 	{ self::$_indentedTabs++; }
		public static function UNINDENT()	{ self::$_indentedTabs--; if (self::$_indentedTabs < 0) self::$_indentedTabs = 0; }

		public static function Log($msg, $logPos = FALSE)
		{
			PBLog::ShareLog(str_repeat("\t", self::$_indentedTabs) . $msg, $logPos, 'update.log');
			echo str_repeat(str_repeat("&nbsp;", 8), self::$_indentedTabs) . $msg . EOL;
		}
	}
