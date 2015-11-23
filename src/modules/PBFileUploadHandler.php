<?php
	/**
	 ** 1027.BadmintonLa - PBFIleReceiver.php
	 ** Created by JCloudYu on 2015/11/23 09:48
	 **/
	using( 'kernel.core.PBModule' );
	using( 'sys.process.*' );
	using( 'sys.net.PBUploadedFile' );
	using( 'ext.base.misc' );

	final class PBFileUploadHandler extends PBModule
	{
		const UPLOAD_PROC_NOOP			= 0;
		const UPLOAD_PROC_MD5_CHECKSUM	= 1;
		const UPLOAD_PROC_SHA1_CHECKSUM	= 2;



		private $_storagePath = '';
		public function __get_storagePath(){
			return $this->_storagePath;
		}
		public function __set_storagePath( $value ){
			$this->_storagePath = "{$value}";
		}

		private $_purgeError = FALSE;
		public function __get_purgeError(){
			return $this->_purgeError;
		}
		public function __set_purgeError( $value ){
			$this->_purgeError = ( $value === TRUE );
		}

		private $_procFlag = PBFileUploadHandler::UPLOAD_PROC_NOOP;
		public function __get_procFlag(){
			return $this->_procFlag;
		}
		public function __set_procFlag( $value ){
			$this->_procFlag = TO( $value, 'int strict' );
		}


		private $_status = PBEXECState::NORMAL;



		public function prepare( $moduleRequest )
		{
			if ( PBRequest::Request()->method_upper !== "POST" )
				$this->_status = PBEXECState::INVALID_METHOD;
		}

		public function exec( $param )
		{
			if ( $this->_status !== PBEXECState::NORMAL )
				return FALSE;



			$uploadedFiles	= PBRequest::Request()->files;
			$purgeError		= $this->_purgeError;
			$storagePath	= $this->_storagePath;
			$procFlag		= $this->_procFlag;
			$targetFields	= ( empty( $param ) || !is_array($param) ) ? array_keys( $uploadedFiles ) : $param;



			$processed = ary_filter( $targetFields, function( $item, &$fieldName ) use ( &$uploadedFiles, &$purgeError, &$storagePath, &$procFlag )
			{
				if ( empty($item) || !@is_array($uploadedFiles[$item]) ) return NULL;

				$fieldName = $item;

				return ary_filter( $uploadedFiles[$item], function( $info, $idx ) use ( &$purgeError, &$storagePath, &$procFlag )
				{
					$fileInfo = array(
						'name'		=> $info['name'],
						'tmpPath'	=> $info[ 'tmp_name' ]
					);


					if ( !empty( $info[ 'error' ] ) )
					{
						if ( $purgeError ) return NULL;

						$fileInfo[ 'error' ] = $info[ 'error' ];
						return $fileInfo;
					}

					$token = sha1( uniqid() . "{$info['name']}" );
					if ( !empty($storagePath) )
					{
						if ( !is_dir( $storagePath ) )
						{
							$fileInfo[ 'error' ] = PBUpadedFile::ERROR_CANT_PROC_MOVE;
							return $fileInfo;
						}

						if ( !is_uploaded_file( $info['tmp_name'] ) )
						{
							$fileInfo[ 'error' ] = PBUpadedFile::ERROR_GENERATED;
							return $fileInfo;
						}


						$dstPath = "{$storagePath}/{$token}";
						$result	 = @move_uploaded_file( $info['tmp_name'], $dstPath );
						if ( empty($result) )
						{
							$fileInfo[ 'error' ] = PBUpadedFile::ERROR_CANT_PROC_MOVE;
							return $fileInfo;
						}


						unset( $fileInfo['tmpPath'] );




						// INFO: Post Processing
						if ( $procFlag & PBFileUploadHandler::UPLOAD_PROC_MD5_CHECKSUM )
							$fileInfo[ 'md5' ] = md5_file( $dstPath );

						if ( $procFlag & PBFileUploadHandler::UPLOAD_PROC_SHA1_CHECKSUM )
							$fileInfo[ 'sha1' ] = sha1_file( $dstPath );
					}



					$mime = $info['type'];
					list( $mimeMajor, $mimeMinor ) = explode( '/', $mime );

					$fileInfo['token']	= $token;
					$fileInfo['name']	= $info['name'];
					$fileInfo['mime']	= array(
						'general'	=> $mime,
						'major'		=> $mimeMajor,
						'minor'		=> $mimeMinor,
					);
					$fileInfo['size']	= $info['size'];

					return $fileInfo;
				}, NULL);
			}, NULL);

			return $processed;
		}
	}
