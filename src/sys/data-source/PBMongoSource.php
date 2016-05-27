<?php
	/**
	 ** 1024.QueueCounter - PBMongoSource.php
	 ** Created by JCloudYu on 2016/04/14 18:27
	 **/

	use \MongoDB\Driver\Query;
	use \MongoDB\Driver\BulkWrite;
	use \MongoDB\Driver\Command;
	use \MongoDB\BSON\ObjectID;



	using( 'sys.data-source.PBDataSource' );

	class PBMongoSource extends PBDataSource
	{
		private $_mongoConnection = NULL;

		public function __construct( $DSURI = "//127.0.0.1:27017/db", $options = array(), $driverOpt = array() ) {
			if ( !preg_match( '/^([A-Za-z][A-Za-z0-9]*:)*(\/\/.*)$/', $DSURI, $matches ) )
				throw new PBException( "Given data source URI is incorrect!" );
			$URI = @"mongodb:{$matches[2]}";
			$this->_mongoConnection = new \MongoDB\Driver\Manager( $URI, $options, $driverOpt );
		}
		public function __get_source() {
			return $this->_mongoConnection;
		}



		public function get( $dataNS, $filter, &$additional = [] ) {

			$range		= $this->range( $dataNS, $filter, $additional );
			$queryOpt	= [
				'skip'	=> $range[ 'skip' ],
				'limit'	=> $range[ 'limit' ]
			];

			if ( $additional[ 'order' ] )
				$queryOpt[ 'order' ] = $additional[ 'order' ];



			// INFO: Query and collect results
			$cursor = $this->_mongoConnection->executeQuery( $dataNS, new Query( $filter, $queryOpt ) );
			return empty($additional[ 'fetch-anchor' ]) ? PBDataSource::CollectData( $cursor, 'PBMongoSource::MongoCollect' ) : $cursor;
		}
		public function insert( $dataNS, $insertData, $additional = [] ) {

			// INFO: Prepare write info
			$bulkWrite = new BulkWrite();

			if ( empty($additional['multiple']) )
			{
				unset( $insertData['_id'] );
				$id = $bulkWrite->insert( $insertData );
				$sessionId = "{$id}";
			}
			else
			{
				$sessionId = [];
				foreach ( $insertData as $doc )
				{
					unset( $doc['_id'] );
					$id = $bulkWrite->insert( $doc );
					$sessionId[] = "{$id}";
				}
			}



			// INFO: Write and collect results
			$result = $this->_mongoConnection->executeBulkWrite( $dataNS, $bulkWrite );
			return ( is_a( $result, '\MongoDB\Driver\WriteResult' ) ? $sessionId: FALSE );
		}
		public function update( $dataNS, $filter, $updatedData = [], $additional = [] ) {

			// INFO: Prepare update info
			$bulkWrite 	= new BulkWrite();
			unset( $updatedData['_id'] );
			$bulkWrite->update( $filter, [ '$set' => $updatedData ], [ 'multi' => TRUE ] );



			// INFO: Update and collect results
			$result = $this->_mongoConnection->executeBulkWrite( $dataNS, $bulkWrite );
			return ( is_a( $result, '\MongoDB\Driver\WriteResult' ) ? $result->getModifiedCount(): FALSE );
		}
		public function delete( $dataNS, $filter, $additional = [] ) {

			// INFO: Prepare delete info
			$bulkWrite = new BulkWrite();
			$bulkWrite->delete( $filter, [ 'multi' => TRUE ] );



			// INFO: Delete and collect results
			$result = $this->_mongoConnection->executeBulkWrite( $dataNS, $bulkWrite );
			return ( is_a( $result, '\MongoDB\Driver\WriteResult' ) ? $result->getDeletedCount(): FALSE );
		}



		public function count( $dataNS, $filter ) {
			$ns = self::ResolveNameSpace( $dataNS );

			$cursor = $this->_mongoConnection->executeCommand(
				$ns['database'],
				new Command([ 'count' => $ns['collection'], 'query' => $filter ])
			);

			return $cursor->toArray()[0]->n;
		}
		public function range( $dataNS, $filter, &$additional = [] ) {

			$page 		= CAST( @$additional['page'], 'int' );
			$pageSize 	= CAST( @$additional['pageSize'], 'int' );
			$totalCount = $this->count( $dataNS, $filter );




			if( empty( $pageSize ) )
			{
				$totalPages = $page = 1;
				$pageSize	= $totalCount;
				$range		= array( 'skip' => 0, 'limit' => $totalCount );
			}
			else
			{
				$totalPages = ceil( (float)$totalCount / (float)$pageSize );
				$page		= min( max( $page, 1 ), max( $totalPages, 1 ) );
				$range		= array( 'skip' => ( $page - 1 ) * $pageSize, 'limit' => $pageSize );
			}



			// INFO: Write information back
			if ( !is_array($additional) ) $additional = [];

			$additional[ 'page' ]		= $page;
			$additional[ 'pageSize' ] = $pageSize;
			$additional[ 'pageAmt' ]	= $totalPages;
			$additional[ 'total' ]	= $totalCount;



			return $range;
		}



		private static function ResolveNameSpace( $namespace ) {
			$ns = explode( '.', $namespace );
			$collection	= @array_pop( $ns );
			$database	= @array_pop( $ns );

			return [ 'database' => $database, 'collection' => $collection ];
		}
		public static function MongoCollect( $document, &$idx ) {
			$idx = "{$document->_id}";
			return $document;
		}
		public static function ObjectID( $hexStr ) {
			try{
				return new ObjectID( "{$hexStr}" );
			} catch(Exception $e) {
				return NULL;
			}
		}
	}
