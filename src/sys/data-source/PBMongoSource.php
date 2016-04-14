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
			if ( !preg_match( '/^([A-Za-z][A-Za-z0-9]*:)*(\/\/.*)$/', $DSURI, $matches ) )
				throw new PBException( "Given data source URI is incorrect!" );

			$URI = @"mongodb:{$matches[2]}";
			$this->_mongoConnection = new \MongoDB\Driver\Manager( $URI, $options, $driverOpt );
		}

		public function __get_source(){
			return $this->_mongoConnection;
		}
	}
