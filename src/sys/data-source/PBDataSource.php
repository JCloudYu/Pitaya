<?php
	/**
	 ** 1024.QueueCounter - PBDataSource.php
	 ** Created by JCloudYu on 2016/04/14 18:13
	 **/

	using( 'kernel.basis.PBObject' );

	abstract class PBDataSource extends PBObject
	{
		public static function ParseURI( $sourceURI ){
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

		abstract public function __get_source();
	}
