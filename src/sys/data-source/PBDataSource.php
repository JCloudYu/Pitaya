<?php
	/**
	 ** 1024.QueueCounter - PBDataSource.php
	 ** Created by JCloudYu on 2016/04/14 18:13
	 **/

	using( 'kernel.basis.PBObject' );

	abstract class PBDataSource extends PBObject
	{
		abstract public function __get_source();



		abstract public function get( $dataNS, $filter, &$additional = [] );
		abstract public function insert( $dataNS, $insertData, $additional = [] );
		abstract public function update( $dataNS, $filter, $updatedData = [], $additional = [] );
		abstract public function delete( $dataNS, $filter, $additional = [] );
		abstract public function count( $dataNS, $filter );
		abstract public function range( $dataNS, $filter, &$additional = [] );



		// INFO: Supportive APIs
		public static function CollectData( $anchor, $map = NULL ) {

			$mapFunc = ( is_callable($map) ) ? $map : "PBDataSource::NoProc";

			$result	 = [];
			foreach ( $anchor as $data )
			{
				$idx = NULL;
				$newData = call_user_func_array( $mapFunc, [ &$data, &$idx ] );
				if ( $idx === NULL )
					$result[] = $newData;
				else
					$result[ $idx ] = $newData;
			}

			return $result;
		}
		public static function ParseURI( $sourceURI ) {

			$URI = parse_url( $sourceURI );
			if ( $URI === FALSE ) return NULL;



			foreach( [ 'user', 'pass', 'fragment' ] as $field )
				$URI[ $field ] = urldecode( "{$URI[$field]}" );

			$URI[ 'path' ] = @substr( trim( "{$URI['path']}" ), 1 );
			$URI[ 'path' ] = ary_filter(
				( $URI[ 'path' ] !== ""  ? explode( '/', $URI['path'] ) : []),
				function( $item ){ return urldecode($item); }
			);
			return $URI;
		}
		public static function& NoProc( &$input, &$idx ) { $idx = NULL; return $input; }
	}
