<?php
	using('ext.base.string');

	class PBPatch extends PBModule
	{
		private $_patchDir = "share.patch";
		public function __set_patchDir($value) { $this->_patchDir = $value; }



		public function execute( $chainData = NULL, $initData = NULL ) {
		
			$param = ( $initData !== NULL ) ? $initData : $chainData;
			
			

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
			while ( ($filename = readdir($dh)) !== FALSE )
			{
				if ( is_dir("{$patchDir}/{$filename}") )
				{
					if ( !is_file("{$patchDir}/{$filename}/{$filename}.php") ) continue;
					$result = CompareVersion("{$filename}", "{$param}");
				}
				else
				{
					if ( substr($filename, -4) != ".php" ) continue;
					$filename = substr($filename, 0, -4);
					$result = CompareVersion("{$filename}", "{$param}");
				}


				if ($result === FALSE || $result <= 0) continue;
				$patchList[] = $filename;
			}

			if (empty($patchList))
			{
				PBStdIO::STDOUT("Nothing to patch!");
				return;
			}

			usort($patchList, "CompareVersion");


			$preprocessor = "{$patchDir}/preprocess.php";
			if ( is_file($preprocessor) && is_readable($preprocessor) )
			{
				PBStdIO::STDOUT( "Loading preprocessor..." );
				require_once $preprocessor;
			}

			$CWD = getcwd();
			foreach ($patchList as $version)
			{
				PBStdIO::STDOUT("Patching to {$version}...");

				$relPath = "{$patchDir}/{$version}";
				if ( is_dir($relPath) )
				{
					chdir($relPath);
					ScriptOut("{$patchDir}/{$version}/{$version}.php");
				}
				else
				{
					chdir($patchDir);
					ScriptOut("{$patchDir}/{$version}.php");
				}

				PBStdIO::STDOUT("Patch done!");
			}
			chdir($CWD);

			$postprocessor = "{$patchDir}/postprocess.php";
			if ( is_file($postprocessor) && is_readable($postprocessor) )
			{
				PBStdIO::STDOUT( "Loading postprocessor..." );
				require_once $postprocessor;
			}
		}
	}
