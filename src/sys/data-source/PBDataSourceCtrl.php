<?php
	/**
	 ** 1024.QueueCounter - PBDataSourceCtrl.php
	 ** Created by JCloudYu on 2016/04/15 00:43
	 **/

	class PBDataSourceCtrl
	{
		public static function Source( $identifier = '', $DSURI = '', $options = array(), $driverOpt = array(), $FORCE_CREATE = FALSE )
		{
			static $_dataSources = array();



			// INFO: Check whether the identifier is skipped
			if ( is_string($identifier) && preg_match( '/^[A-Za-z][A-Za-z0-9]*:\/\/.*$/', $identifier ) )
			{
				$FORCE_CREATE	= $driverOpt;
				$driverOpt		= $options;
				$options		= $DSURI;
				$DSURI			= $identifier;
				$identifier		= 0;
			}


			
			// INFO: If source exists
			$key = md5( empty($identifier) ? "0" : "_IDENTITY_{$identifier}" );
			if ( !empty( $_dataSources[ $key ] ) && !$FORCE_CREATE )
				return $_dataSources[ $key ];

			// INFO: Create source if DSURI is not empty
			if ( !empty($DSURI) )
			{
				if (!empty( $_dataSources[ $key ] ))
					unset( $_dataSources[ $key ] );


				$URI	= PBDataSource::ParseURI( $DSURI );
				$source = NULL;
				switch ( @"{$URI[ 'scheme' ]}" )
				{
					case "mysql":
						$source = new PBMySQLSource( $URI, $options, $driverOpt );
						break;

					case "mongodb":
						$source = new PBMongoSource( $URI, $options, $driverOpt );
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
