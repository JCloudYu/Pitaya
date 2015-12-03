<?php
	/**
	 ** 0001.pitaya - PBSYSStandaloneExecutor.php
	 ** Created by JCloudYu on 2015/10/24 15:04
	 **/
	using( 'kernel.core.PBModule' );

	class PBSYSStandaloneExecutor extends PBModule
	{
		public function prepareEvent( $moduleRequest )
		{
			if ( !CLI_ENV ) return;

			$this->prepareShell( $moduleRequest );
		}

		public function prepareShell( $moduleRequest )
		{
			$moduleName = "working." . __STANDALONE_MODULE__;
			$module = PBProcess::Module( $moduleName );

			if ( !$module->auth() )
			{
				PBStdIO::STDERR( "Access Denied! This module cannot be executed!" );
				return;
			}

			$this->boot[] = array( 'module' => $module, 'request' => $moduleRequest );
		}
	}
