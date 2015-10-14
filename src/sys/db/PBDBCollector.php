<?php
	/**
	 ** 1028.CSMS-BDF - PBDBCollector.php
	 ** Created by JCloudYu on 2015/10/14 18:01
	 **/
	class PBDBCollector
	{
		public static function CollectByField( PDOStatement $stmt, $field = 'id' )
		{
			$result	= array();

			if ( !empty($field) )
				while ( ($row = $stmt->fetch()) !== FALSE ) $result[$row["{$field}"]] = $row;
			else
				while ( ($row = $stmt->fetch()) !== FALSE ) $result[] = $row;

			return $result;
		}

		public static function CollectByFilter( PDOStatement $stmt, Callable $filterFunc, $skipValue = FALSE )
		{
			$result	= array();

			while ( ($row = $stmt->fetch()) !== FALSE )
			{
				$index = NULL;
				$filterResult = $filterFunc($row, $index);

				if ( (func_num_args() > 2) && ($filterResult === $skipValue) ) continue;

				if ( $index !== NULL )
					$result[ $index ] = $filtered;
				else
					$result[] = $filtered;
			}

			return $result;
		}
	}
