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
		const UPLOAD_PROC_NOOP				=  0;
		const UPLOAD_PROC_MD5_CHECKSUM		=  1;
		const UPLOAD_PROC_SHA1_CHECKSUM		=  2;
		const UPLOAD_PROC_SHA256_CHECKSUM	=  4;
		const UPLOAD_PROC_SHA512_CHECKSUM	=  8;
		const UPLOAD_PROC_CRC_CHECKSUM		= 16;



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

		private $_fileProc = NULL;
		public function __get_fileProc(){
			return $this->_fileProc;
		}
		public function __set_fileProc( $value ){
			$this->_fileProc = is_callable($value) ? $value : NULL;
		}
		
		private $_autoDirectroy = FALSE;
		public function __get_autoDir(){
			return $this->_autoDirectroy;
		}
		public function __set_autoDir( $value ){
			$this->_autoDirectroy = !!$value;
		}
		


		private $_status = PBEXECState::NORMAL;
		private $_fields = array();



		public function prepare( $moduleRequest )
		{
			if ( PBRequest::Request()->method_upper !== "POST" )
				$this->_status = PBEXECState::INVALID_METHOD;

			$this->_fields = TO( $moduleRequest, 'array' );
		}

		public function exec( $param )
		{
			if ( $this->_status !== PBEXECState::NORMAL )
				return FALSE;

			$param = TO( $param, 'array' );

			$uploadedFiles	= PBRequest::Request()->files;
			$purgeError		= $this->_purgeError;
			$storagePath	= $this->_storagePath;
			$procFlag		= $this->_procFlag;
			$autoDir		= $this->_autoDirectroy;
			$procFunc		= !empty($this->_fileProc) ? $this->_fileProc : function( $fileInfo ){ return $fileInfo; };

			$targetFields	= $this->_fields;
			if ( empty($targetFields) )
				$targetFields	= ( empty( $param ) ) ? array_keys( $uploadedFiles ) : $param;



			$processed = ary_filter( $targetFields, function( $item, &$fieldName ) use ( &$autoDir, &$uploadedFiles, &$purgeError, &$storagePath, &$procFlag, &$procFunc )
			{
				if ( empty($item) || !@is_array($uploadedFiles[$item]) ) return NULL;

				$fieldName = $item;

				return ary_filter( $uploadedFiles[$item], function( $info, $idx ) use ( &$autoDir, &$purgeError, &$storagePath, &$procFlag, &$procFunc )
				{
					$fileInfo = array(
						'name'		=> $info['name'],
						'tmpPath'	=> "{$info[ 'tmp_name' ]}"
					);

					// region [ Skipping condition of PHP file error ]
					if ( !empty( $info[ 'error' ] ) )
					{
						if ( $purgeError ) return NULL;

						$fileInfo[ 'error' ] = $info[ 'error' ];
						return $fileInfo;
					}
					// endregion

					// region [ Exract information from original input file ]
					if ( $procFlag & PBFileUploadHandler::UPLOAD_PROC_MD5_CHECKSUM )
						$fileInfo[ 'md5' ] = hash_file( 'md5', $info['tmp_name'] );

					if ( $procFlag & PBFileUploadHandler::UPLOAD_PROC_SHA1_CHECKSUM )
						$fileInfo[ 'sha1' ] = hash_file( 'sha1', $info['tmp_name'] );

					if ( $procFlag & PBFileUploadHandler::UPLOAD_PROC_SHA256_CHECKSUM )
						$fileInfo[ 'sha256' ] = hash_file( 'sha256', $info['tmp_name'] );

					if ( $procFlag & PBFileUploadHandler::UPLOAD_PROC_SHA512_CHECKSUM )
						$fileInfo[ 'sha256' ] = hash_file( 'sha512', $info['tmp_name'] );

					if ( $procFlag & PBFileUploadHandler::UPLOAD_PROC_CRC_CHECKSUM )
						$fileInfo[ 'crc32' ] = hash_file( 'crc32', $info['tmp_name'] );
					// endregion

					$token = sha1( uniqid() . "{$info['name']}" );
					// region [ Move file to file stroage ]
					if ( !empty($storagePath) )
					{
						if ( $autoDir ) 
							@mkdir( $storagePath, 0777, TRUE );
							
					
					
						if ( !is_dir( $storagePath ) )
							$fileInfo[ 'error' ] = PBUpadedFile::ERROR_INVALID_TARGET_PATH;

						if ( !is_uploaded_file( $info['tmp_name'] ) )
							$fileInfo[ 'error' ] = PBUpadedFile::ERROR_GENERATED;


						$dstPath = "{$storagePath}/{$token}";
						$result	 = @move_uploaded_file( $info['tmp_name'], $dstPath );
						if ( empty($result) )
							$fileInfo[ 'error' ] = PBUpadedFile::ERROR_CANT_PROC_MOVE;

						unset( $fileInfo['tmpPath'] );
					}
					// endregion

					// NOTE: If purge error is on...
					if ( !empty($fileInfo[ 'error' ]) && $purgeError ) return NULL;

					// region [ Collect remaining input file infomation ]
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
					// endregion



					return $procFunc( $fileInfo );
				}, NULL);
			}, NULL);

			return $processed;
		}
	}
