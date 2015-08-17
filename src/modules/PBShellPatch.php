<?php
	/**
	 ** 1027.BadmintonLa - PBShellPatch.php
	 ** Created by JCloudYu on 2015/08/18 01:58
	 **/
	final class PBShellPatch extends PBModule
	{
		public function prepareShell( $moduleRequest )
		{
			s_define("PATCH_DIR", path('service'),	TRUE, FALSE);



			// INFO: Check Installation
			$installFlag = path('data.flags', '.install');
			if ( !file_exists( $installFlag ) )
			{
				PBStdIO::STDOUT( "System has not been installed!" );
				Termination::NORMALLY();
			}



			// INFO: Prepare patch module
			$patchObj = PBProcess::Process()->getModule('PBPatch');
			$patchObj->patchDir = PATCH_DIR;



			// INFO: Prepare FuncChain module
			$funcChainObj = PBProcess::Module( 'PBFuncChain', FALSE );
			$funcChainObj->post = function() {
				PBStdIO::STDOUT("Finalizing...     " . date("Y/m/d H:i:s"));

				// INFO: Remove previously attached streaming destinations
				PBStream::STDOUT()->pop( TRUE );
				PBStream::STDERR()->pop( TRUE );

				PBStdIO::STDOUT("Updating timestamp...");

				$patchFlag = path("data.flags", '.patch');
				file_put_contents("{$patchFlag}", date("Y/m/d H:i:s O\n"), FILE_APPEND);

				$dataDir = path("data");
				exec("chown -R apache:apache {$dataDir}");
			};



			PBProcess::Process()->assignNextModules(array(
				array( 'module' => $patchObj, 'request' => PBDBCtrl::DB()->version ),
				array( 'module' => $funcChainObj )
			));
		}

		public function shell($param)
		{
			// INFO: Attach another streaming destination to STDOUT and STDERR
			$stream = PBStream::Open( path('root', '.patch.log'), "a+b" );
			PBStream::STDOUT()->tee( $stream );
			PBStream::STDERR()->tee( $stream );

			PBStdIO::STDOUT("Start patching... " . date("Y/m/d H:i:s"));
			return $param;
		}
	}
