<?php
	using('kernel.prototype.PBCachePrototype');

	final class PBFileCache extends PBCachePrototype
	{
		private static $_storages		= array();
		private static $_defaultStorage	= NULL;
		public static function Storage( $storageKey = '', $storagePath = NULL )
		{
			$nArgs = func_num_args();

			// INFO: Default storage retrieval
			if ( $nArgs == 0 )
				return self::$_defaultStorage;



			// INFO: Getter mode
			$hashedKey = md5( @"{$storageKey}" );	// Normalization
			if ( $nArgs == 1 )
			{
				if ( !empty(self::$_storages[ $hashedKey ]) )
					return self::$_storages[ $hashedKey ];


				// INFO: Setter mode with storage path
				if ( !is_dir( $storageKey ) )
					return NULL;

				$storagePath = $storageKey;
			}



			// INFO: Setter mode
			$storagePath = @"{$storagePath}";	// Normalization
			if ( empty($storagePath) || !is_dir($storagePath) || !is_readable($storagePath) || !is_writable($storagePath) )
				return NULL;



			// INFO: Attach and return new stoage object
			$cacheObj = self::$_storages[ $hashedKey ] = new PBFileCache( $storagePath );
			if ( self::$_defaultStorage === NULL ) self::$_defaultStorage = $cacheObj;

			return $cacheObj;
		}



		private $_fileStorageDir = NULL;
		private function __construct( $storageDir ) { $this->_fileStorageDir = $storageDir; }
		public function uniqueName( $prefix = "" ) {
			@unlink( $fileName = tempnam($this->_fileStorageDir, $prefix) );
			return $fileName;
		}



		public function store( $token, $data )
		{
			$cacheFile	= "{$this->_fileStorageDir}/{$token}";
			if ( is_file( $cacheFile ) && !is_writable( $cacheFile ) ) return FALSE;

			// Open file
			$handle	= fopen( $cacheFile, "a+b" );
			if ( !$handle ) throw new Exception("Cache file: {$cacheFile} is not writable!");

			// Lock file
			if ( !flock( $handle, LOCK_EX ) )
			{
				fclose( $handle );
				throw new Exception("Cache file: {$cacheFile} cannot be locked!");
			}

			// Truncate and write
			fseek( $handle, 0 );
			ftruncate( $handle, 0 );

			if ( fwrite( $handle, serialize($data) ) === FALSE )
			{
				flock( $handle, LOCK_UN ); fclose( $handle );
				throw new Exception("Cache file: {$cacheFile}, write failed!");
			}

			// Done
			flock( $handle, LOCK_UN );
			fclose( $handle );

			return TRUE;
		}
		public function fetch( $token, &$fileTime = NULL )
		{
			$cacheFile	= "{$this->_fileStorageDir}/{$token}";
			if ( !is_file( $cacheFile ) || !is_readable( $cacheFile ) ) return NULL;

			// Open file
			$handle = fopen( $cacheFile, "rb" );
			if ( !$handle ) return NULL;

			if ( !flock( $handle, LOCK_SH ) )
			{
				fclose( $handle );
				return NULL;
			}

			$fileTime = filemtime( $cacheFile );
			$data = @unserialize( file_get_contents($cacheFile) );
			flock( $handle, LOCK_UN );
			fclose( $handle );

			if ( empty($data) ) return NULL;

			return $data;
		}
		public function destroy( $token )
		{
			$cacheFile = "{$this->_fileStorageDir}/{$token}";
			@unlink( $cacheFile );
		}
	}
