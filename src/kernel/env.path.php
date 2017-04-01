<?php
	final class __PATH_RESOLVER {
		private static $_path_cache = [];
		public static function Initialize() {
			if ( !is_array(@$GLOBALS['extPath']) ) $GLOBALS['extPath'] = [];
			
			// INFO: Attach custom packages
			foreach( $GLOBALS['extPath'] as $identifier => $path )
				self::$_path_cache[$identifier] = "{$path}";

			// INFO: Attach pitaya root packages 
			$list = scandir(PITAYA_ROOT);
			foreach ($list as $dir) {
				$absPath = PITAYA_ROOT . "/{$dir}";
				if ( !is_dir($absPath) ) continue;
				
				self::$_path_cache[strtolower($dir)] = $absPath;
			}

			// INFO: Attach other keywords
			self::$_path_cache[ 'root' ]	= ROOT;
			self::$_path_cache[ 'lib' ]		= defined( "__LIB_PATH" ) ? __LIB_PATH : '/Lib';
			self::$_path_cache[ 'share' ]	= defined( "__SHARE_PATH" ) ? __SHARE_PATH : ROOT . '/Share';
			self::$_path_cache[ 'data' ]	= defined( "__DATA_PATH" ) ? __DATA_PATH : ROOT . '/Data';
			self::$_path_cache[ 'broot' ]	= self::$_path_cache[ 'srvroot' ] = defined( "__BASIS_PATH" ) ? __BASIS_PATH : ROOT . '/Basis';
			self::$_path_cache[ 'basis' ]	= self::$_path_cache[ 'service' ] = self::$_path_cache[ 'broot' ];
			self::$_path_cache[ 'working' ]	= ( empty($GLOBALS['STANDALONE_EXEC']) ) ? self::$_path_cache[ 'basis' ] : $GLOBALS['STANDALONE_EXEC']['cwd'];

			// Resolve to real path if targeted directory is a lnk file
			if ( IS_WIN_ENV ) {
				foreach( self::$_path_cache as $key => $path )
				{
					$linkPath = "{$path}.lnk";
					if ( is_dir( $path ) || !is_file( $linkPath ) ) continue;
					
					self::$_path_cache[ $key ] = resolveLnk( $linkPath );
				}
			}



			define( 'BASIS_ROOT',	self::$_path_cache[ 'broot' ] );
			define( 'SHARE_ROOT',	self::$_path_cache[ 'share' ] );
			define( 'DATA_ROOT',	self::$_path_cache[ 'data' ]  );
		}
		public static function Purge() {
			if ( defined( 'WORKING_ROOT' ) ) {
				self::$_path_cache[ 'basis' ] = self::$_path_cache[ 'service' ] = WORKING_ROOT;
			}
		}
		public static function Resolve( $package ) {
			return empty(self::$_path_cache[$package]) ? '' : self::$_path_cache[$package];
		}
	}
	__PATH_RESOLVER::Initialize();
