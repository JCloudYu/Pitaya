<?php
	/**
	 ** 0001.pitaya - PBSimpleModule.php
	 ** Created by JCloudYu on 2015/12/04 19:21
	 **/
	using( 'kernel.core.PBModule' );

	class PBKernelVersion extends PBModule
	{
		public function prepare( $moduleRequest ) {
			$this->prepareShell( PBRequest::Request()->parseQuery()->query['resource'] );
		}

		public function prepareShell( $moduleRequest )
		{
			$reqVer = CAST( @array_shift( $moduleRequest ), 'string upper-case' );
			$verMap = array(
				"MAJOR"		=> PITAYA_VERSION_MAJOR,
				"MINOR"		=> PITAYA_VERSION_MINOR,
				"BUILD"		=> PITAYA_VERSION_BUILD,
				"PATCH"		=> PITAYA_VERSION_PATCH,
				"SHORT"		=> PITAYA_VERSION_SHORT,
				"COMPLETE"	=> PITAYA_VERSION,
				"DETAIL"	=> PITAYA_VERSION_DETAIL
			);
			$version = empty($verMap[$reqVer]) ? $verMap['DETAIL'] : $verMap[$reqVer];


			$hStream = fopen( "php://output", "wb" );
			fwrite( $hStream, $version );
			fclose( $hStream );
		}
	}
