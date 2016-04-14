<?php
	/**
	 ** 1024.QueueCounter - PBMongoSource.php
	 ** Created by JCloudYu on 2016/04/14 18:27
	 **/

	using( 'sys.data-source.PBDataSource' );

	class PBMongoSource extends PBDataSource
	{
		private $_mongoConnection = NULL;

		public function __construct( $DSURI = "//127.0.0.1:27017/db", $options = array(), $driverOpt = array() )
		{
			if ( !is_array($DSURI) )
				$DSURI = parse_url( "{$DSURI}" );

			$host = @"{$DSURI['host']}";
			$port = CAST( @$DSURI[ 'port' ], 'int strict', 27017 );
			$port = empty( $port ) ? 27017 : $port;
			$db   = @"{$DSURI['path'][0]}";
			$db	  = empty($db) ? "" : "/{$db}";



			$URI = "mongodb://{$host}:{$port}{$db}";
			$this->_mongoConnection = new \MongoDB\Driver\Manager( $URI, $options, $driverOpt );
		}

		public function __get_source(){
			return $this->_mongoConnection;
		}
	}
