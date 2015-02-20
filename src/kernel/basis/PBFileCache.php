<?php
/**
 * 1017.NeighborApp - PBFileCache.php
 * Created by JCloudYu on 2015/02/20 13:47
 */
	using('kernel.prototype.PBCachePrototype');

	final class PBFileCache extends PBCachePrototype
	{
		private static $_storages = array();
		public static function Storage( $storagePath = NULL )
		{
			static $_defaultStorage = NULL;

			$storagePath = "{$storagePath}"; // Normalization


			if ( empty($storagePath) )
				return (!empty($_defaultStorage)) ? $_defaultStorage : NULL;


			if ( !is_dir( $storagePath ) || !is_readable( $storagePath ) || !is_writable( $storagePath ) ) return NULL;

			$storageKey = md5(realpath($storagePath));
			if ( !empty(self::$_storages[$storageKey]) ) return self::$_storages[$storageKey];


			self::$_storages[$storageKey] = $cacheObj = new PBFileCache( $storagePath );
			if ( $_defaultStorage === NULL ) $_defaultStorage = $cacheObj;

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
