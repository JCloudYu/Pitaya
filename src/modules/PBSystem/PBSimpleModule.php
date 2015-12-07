<?php
	/**
	 ** 0001.pitaya - PBSimpleModule.php
	 ** Created by JCloudYu on 2015/12/04 19:21
	 **/
	using( 'kernel.core.PBModule' );

	class PBKernelVersion extends PBModule
	{
		private $_request = NULL;

		public function prepare( $moduleRequest ) {
			$request = is_array( $moduleRequest ) ? $moduleRequest : PBRequest::Request()->parseQuery()->query['resource'];
			$this->prepareShell( $request );
		}

		public function prepareShell( $moduleRequest ) {
			$this->_request = $moduleRequest;
		}



		public function exec( $param ) { return $this->shell( $param ); }
		public function shell( $param ) {
			$reqVer = CAST( @array_shift( $this->_request ), 'string upper-case' );
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
			return $version;
		}
	}
