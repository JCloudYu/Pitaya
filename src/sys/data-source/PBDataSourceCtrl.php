<?php
	class PBDataSourceCtrl {
		public static function Source( $identifier = '', $DSURI = '', $options = array(), $driverOpt = array(), $FORCE_CREATE = FALSE ) {
			static $_dataSources = array();
			$matches = NULL;



			// INFO: Check whether the identifier is skipped
			if ( is_string($identifier) && preg_match( '/^([A-Za-z][A-Za-z0-9]*):\/\/.*$/', $identifier, $matches ) )
			{
				$FORCE_CREATE	= $driverOpt;
				$driverOpt		= $options;
				$options		= $DSURI;
				$DSURI			= $identifier;
				$identifier		= "";
			}



			// INFO: Paramter Normalization
			$FORCE_CREATE	= !empty($FORCE_CREATE);
			$driverOpt		= is_array($driverOpt) ? $driverOpt : [];
			$options		= is_array($options) ? $options : [];
			$DSURI			= "{$DSURI}";


			
			// INFO: If source exists
			$key = md5( empty($identifier) ? "0" : "_IDENTITY_{$identifier}" );
			if ( !empty( $_dataSources[ $key ] ) && empty($FORCE_CREATE) )
				return $_dataSources[ $key ];





			// INFO: Check URI
			if ( empty($matches) && !preg_match( '/^([A-Za-z][A-Za-z0-9]*):\/\/.*$/', $DSURI, $matches ) )
				return NULL;

			// INFO: Create source if DSURI is not empty
			if ( !empty($DSURI) )
			{
				if (!empty( $_dataSources[ $key ] ))
					unset( $_dataSources[ $key ] );



				$source = NULL;
				switch ( @"{$matches[1]}" )
				{
					case "mysql":
						using( 'sys.data-source.PBMySQLSource' );
						$URI	= PBIDataSource::ParseURI( $DSURI );
						$source = new PBMySQLSource( $URI, $options, $driverOpt );
						break;

					case "mongodb":
						using( 'sys.data-source.PBMongoSource' );
						$source = new PBMongoSource( $DSURI, $options, $driverOpt );
						break;

					default:
						return NULL;
				}


				// INFO: If there's no any data sources...
				if ( empty($_dataSources) ) $_dataSources[ md5("0") ] = $source;
				
				return ( $_dataSources[ $key ] = $source );
			}

			return NULL;
		}
	}
	
	function PBDataSource($identifier = '', $DSURI = '', $options = array(), $driverOpt = array(), $FORCE_CREATE = FALSE) {
		return PBDataSourceCtrl::Source( $identifier, $DSURI, $options, $driverOpt, $FORCE_CREATE );
	}
