<?php
	/**
	 ** 1024.QueueCounter - PBMongoSource.php
	 ** Created by JCloudYu on 2016/04/14 18:27
	 **/

	use \MongoDB\Driver\Query;
	use \MongoDB\Driver\BulkWrite;
	use \MongoDB\Driver\Command;
	use \MongoDB\BSON\ObjectID;



	using( 'sys.data-source.PBIDataSource' );

	class PBMongoSource extends PBIDataSource
	{
		const AGGREGATION_OPRATORS = [
			'$project',
			'$match',
			'$redact',
			'$limit',
			'$skip',
			'$unwind',
			'$group',
			'$sample',
			'$sort',
			'$geoNear',
			'$lookup',
			'$out',
			'$indexStats'
		];

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

			if ( empty($additional[ 'aggregation' ]) )
				return $this->getQuery( $dataNS, $filter, $additional );
			else
				return $this->getAggregate( $dataNS, $filter, $additional );
		}
		public function getQuery( $dataNS, $filter, &$additional = [] ) {
			$queryOpt = [];
			if ( !empty($additional[ 'page' ]) )
			{
				$range = $this->range( $dataNS, $filter, $additional );
				$queryOpt[ 'skip' ]		= $range[ 'skip' ];
				$queryOpt[ 'limit' ]	= $range[ 'limit' ];
			}

			if ( !empty($additional[ 'order' ]) )
				$queryOpt[ 'sort' ] = $additional[ 'order' ];

			foreach( $additional as $option => $value )
			{
				if ( in_array($option, ["page", "pageSize", "pageAmt", "total", 'order']) ) continue;
				$queryOpt[ $option ] = $value;
			}


			// INFO: Query and collect results
			$cursor = $this->_mongoConnection->executeQuery( $dataNS, new Query( (object)$filter, $queryOpt ) );
			
			if ( !empty($additional[ 'fetch-anchor' ]) )
				return $cursor;
			else
			{
				$mapFunc = ( !array_key_exists( 'id-mapping', $additional ) || !!$additional[ 'id-mapping' ] ) ? 'PBMongoSource::MongoCollect' : NULL;
				return PBIDataSource::CollectData( $cursor, $mapFunc );
			}
		}
		public function getAggregate( $dataNS, $baseQuery, &$additional = [] ) {
			$aggregation = $queryOpt = [];
			$aggregation[] = [ '$match' => (object)$baseQuery ];

			if ( !empty($additional[ 'order' ]) )
				$aggregation[] = [ '$sort' => (object)$additional[ 'order' ] ];

			if ( !empty($additional[ 'projection' ]) )
				$aggregation[] = [ '$project' => (object)$additional[ 'projection' ] ];

			if ( !empty($additional[ 'aggregation' ]) )
			{
				foreach( $additional[ 'aggregation' ] as $op )
				{
					if ( !in_array(key($op), self::AGGREGATION_OPRATORS) ) continue;
					$aggregation[] = (object)$op;
				}
			}

			if ( !empty($additional['page']) )
			{
				$range = $this->range( $dataNS, $aggregation, $additional, TRUE );
				$aggregation[] = [ '$skip'	=> $range[ 'skip' ] ];
				$aggregation[] = [ '$limit' => $range[ 'limit' ] ];
			}


			// INFO: Query and collect results
			$ns = self::ResolveNameSpace( $dataNS );
			$cursor = $this->_mongoConnection->executeCommand( $ns[ 'database' ], new Command([
				'aggregate' => $ns['collection'],
				'pipeline'	=> $aggregation,
				'cursor'	=> (object)[]
			]));
			
			if ( !empty($additional[ 'fetch-anchor' ]) )
				return $cursor;
			else
			{
				$mapFunc = ( !array_key_exists( 'id-mapping', $additional ) || !!$additional[ 'id-mapping' ] ) ? 'PBMongoSource::MongoCollect' : NULL;
				return PBIDataSource::CollectData( $cursor, $mapFunc );
			}
		}

		public function insert( $dataNS, $insertData, $additional = [] ) {

			// INFO: Prepare write info
			$bulkWrite = new BulkWrite();

			if ( empty($additional['multiple']) )
			{
				$insertData = (array)$insertData;
				unset( $insertData['_id'] );
				$id = $bulkWrite->insert( $insertData );
				$sessionId = (empty($additional['cast-object-id'])) ? $id : "{$id}";
			}
			else
			{
				$sessionId = [];
				foreach ( $insertData as $doc )
				{
					$doc = (array)$doc;
					unset( $doc['_id'] );
					$id = $bulkWrite->insert( $doc );
					$sessionId[] = (empty($additional['cast-object-id'])) ? $id : "{$id}";
				}
			}



			// INFO: Write and collect results
			$result = $this->_mongoConnection->executeBulkWrite( $dataNS, $bulkWrite );
			return ( is_a( $result, '\MongoDB\Driver\WriteResult' ) ? $sessionId: FALSE );
		}
		public function update( $dataNS, $filter, $updatedData = [], $additional = [] ) {

			$compoundUpdate	= !empty($additional[ 'compound-update' ]);
			$multipleUpdate	= (!array_key_exists( 'multiple-update', $additional)) ? TRUE : !!$additional[ 'multiple-update' ];
			$shouldInsert	= (!array_key_exists( 'upsert', $additional )) ? FALSE : !!$additional[ 'upsert' ];
			$castResult		= (!array_key_exists( 'cast-result', $additional )) ? TRUE : !!$additional[ 'cast-result' ];

			// INFO: Prepare update info
			$bulkWrite 	= new BulkWrite();
			
			$updatedData = (array)$updatedData;
			unset( $updatedData['_id'] );

			$updateData = $compoundUpdate ? (object)$updatedData : (object)[ '$set' => (object)$updatedData ];
			$bulkWrite->update( (object)$filter, $updateData, [ 'multi' => $multipleUpdate, 'upsert' => $shouldInsert ] );



			// INFO: Update and collect results
			$result = $this->_mongoConnection->executeBulkWrite( $dataNS, $bulkWrite );
			if ( !is_a( $result, '\MongoDB\Driver\WriteResult' ) ) return FALSE;
			
			return ($castResult) ? $result->getModifiedCount() : $result;
		}
		public function delete( $dataNS, $filter, $additional = [] ) {

			$multipleDelete	= (!array_key_exists( 'multiple-delete', $additional)) ? TRUE : !!$additional[ 'multiple-delete' ];
			$castResult		= (!array_key_exists( 'cast-result', $additional )) ? TRUE : !!$additional[ 'cast-result' ];

			// INFO: Prepare delete info
			$bulkWrite = new BulkWrite();
			$bulkWrite->delete( (object)$filter, [ 'limit' => !$multipleDelete ] );



			// INFO: Delete and collect results
			$result = $this->_mongoConnection->executeBulkWrite( $dataNS, $bulkWrite );
			if ( !is_a( $result, '\MongoDB\Driver\WriteResult' ) ) return FALSE;
			
			return ($castResult) ? $result->getDeletedCount() : $result;
		}
		public function bulk( $dataNS, $batchedOps, $additional = [] ) {
			// INFO: Prepare delete info
			$bulkWrite = new BulkWrite( [ 'ordered' => !empty($additional[ 'ordered' ]) ] );
			foreach( $batchedOps as $bulkOp )
			foreach( $bulkOp as $op => $content )
			{
				switch( $op )
				{
					case "insert":
						$bulkWrite->insert( $content[ 'data' ] );
						break;

					case "update":
						$multiple = array_key_exists( 'multiple', $content ) ? !empty($content['multiple']) : TRUE;
						$bulkWrite->update( $content['filter'], $content['data'], [ 'multi' => $multiple ] );
						break;

					case "delete":
						$multiple = array_key_exists( 'multiple', $content ) ? !empty($content['multiple']) : TRUE;
						$bulkWrite->delete( $content['filter'], [ 'limit' => !$multiple ] );
						break;

					default: break;
				}
			}



			// INFO: Delete and collect results
			$result = $this->_mongoConnection->executeBulkWrite( $dataNS, $bulkWrite );
			return ( is_a( $result, '\MongoDB\Driver\WriteResult' ) ? $result: FALSE );
		}
		public function command( $dataNS, $commands ) {
			$ns = self::ResolveNameSpace( $dataNS );
			return $this->_mongoConnection->executeCommand( $ns['database'], new Command($commands) );
		}



		public function count( $dataNS, $filter ) {
			$ns = self::ResolveNameSpace( $dataNS );

			$cursor = $this->_mongoConnection->executeCommand(
				$ns['database'],
				new Command([ 'count' => $ns['collection'], 'query' => $filter ])
			);

			return $cursor->toArray()[0]->n;
		}
		public function countAggregate( $dataNS, $baseAggregation ) {
			$ns = self::ResolveNameSpace( $dataNS );

			$baseAggregation[] = ['$group' => ['_id' => NULL, 'count' => ['$sum' => 1]]];


			$cursor = $this->_mongoConnection->executeCommand( $ns[ 'database' ], new Command([
				'aggregate' => $ns['collection'],
				'pipeline'	=> $baseAggregation,
				'cursor'	=> (object)[]
			]));
			return $cursor->toArray()[0]->count;
		}
		public function range( $dataNS, $filter, &$additional = [], $aggregate = FALSE ) {

			$page 		= CAST( @$additional['page'], 'int' );
			$pageSize 	= CAST( @$additional['pageSize'], 'int' );
			$totalCount = empty($aggregate) ? $this->count($dataNS, $filter) : $this->countAggregate($dataNS, $filter);




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
			$additional[ 'pageSize' ]	= $pageSize;
			$additional[ 'pageAmt' ]	= $totalPages;
			$additional[ 'total' ]		= $totalCount;



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
