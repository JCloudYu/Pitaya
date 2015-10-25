<?php
/**
 * 1027.BadmintonLa - DBCtrl.php
 * Created by JCloudYu on 2015/04/12 01:01
 */
	using('sys.db.ExtPDO');

	final class PBDBCtrl
	{
		/**
		 * Prepare and return a database connection singleton.
		 * This function will return NULL if there's no connection parameter given.
		 *
		 * @param null|array $param connection related parameter that is defined in env.ini
		 * @param array $option addition parameters for database linkage initialization
		 *
		 * @return ExtPDO|null
		 */
		public static function DB($param = NULL, $option = array('CREATE_VAR'))
		{
			static $__singleton_db = NULL;

			if ( $__singleton_db && !in_array('FORCE_CREATE', $option) )
				return $__singleton_db;

			if ($param)
			{
				if ( $__singleton_db )
				{
					unset($__singleton_db);
					$__singleton_db = NULL;
				}

				return ($__singleton_db = self::CONNECT( $param, $option ));
			}

			return NULL;
		}

		public static function GEN_CONNECT_INFO( $db, $account, $password, $host = "localhost", $port = 3306 )
		{
			return array(
				'host'		=> $host,
				'port'		=> $port,
				'db'		=> $db,
				'account'	=> $account,
				'password'	=> $password
			);
		}

		public static function CONNECT($param = NULL, $option = array('CREATE_VAR'))
		{
			$dsn = ExtPDO::DSN($param['host'], $param['db'], $param['port']);
			$connection = new ExtPDO($dsn, $param['account'], $param['password'], $option);
			$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return $connection;
		}

		public static function LIMIT($SQL, $page = NULL, $pageSize = NULL, &$pageInfo = NULL, &$DB = NULL)
		{
			if (is_array($SQL))
			{
				$sql	= $SQL['sql'];
				$param	= $SQL['param'];
			}
			else
			{
				$sql	= $SQL;
				$param	= array();
			}

			$sql = trim($sql);
			$sql = preg_replace('/(select)(.|[\n])*(from([^;]|[\n])*);*/i', "$1 count(*) as count $3", $sql, -1);
			$DB_CONNECTION = empty($DB) ? PBDBCtrl::DB() : $DB;
			$countResult = $DB_CONNECTION->fetch($sql, $param);


			$totalCount = $countResult['count'];
			$page		= TO($page,		'int');
			$pageSize	= TO($pageSize,	'int');

			if (!empty($pageSize))
			{
				$totalPages = ceil((float)$totalCount/(float)$pageSize);
				$page = min(max($page, 1), max($totalPages, 1));

				$from = ($page - 1) * $pageSize;
				$limitClause = "{$from},{$pageSize}";
			}
			else
			{
				$totalPages = $page = 1;
				$pageSize = $totalCount;

				$LIMIT_MAX = ($totalCount < 1) ? 1 : $totalCount;
				$limitClause = "0,{$LIMIT_MAX}";
			}



			$pageInfo = array(
				'pageSize'		=> $pageSize,
				'page'			=> $page,
				'totalPages'	=> $totalPages,
				'totalRecords'	=> $totalCount
			);

			return $limitClause;
		}

		public static function SET($data, &$param = NULL, $varIndex = FALSE)
		{
			$param = $sql = array();
			foreach ($data as $col => $val)
			{
				if ($varIndex)
				{
					$stmt = "`{$col}` = :{$col}";
					$param[":{$col}"] = $val;
				}
				else
				{
					$stmt = "`{$col}` = ?";
					$param[] = $val;
				}

				$sql[] = $stmt;
			}
			return implode(', ', $sql);
		}

		public static function ORDER($orderOpt = array())
		{
			if (!is_array($orderOpt)) return '';

			$orderStmt = array();
			$hasRandom = FALSE;
			foreach ($orderOpt as $colName => $sequence)
			{
				if ( strtoupper($colName) == "RANDOM" && strtoupper($sequence) == "RANDOM" )
				{
					$hasRandom = TRUE;
					break;
				}
				else
				{
					$seq = (in_array(strtoupper("{$sequence}"), array('ASC', 'DESC'))) ? " $sequence" : "";
					$orderStmt[] = "`{$colName}`{$seq}";
				}
			}
			$orderStmt = ($hasRandom) ? "RAND()" : implode(', ', $orderStmt);

			if (empty($orderStmt)) return '';

			return $orderStmt;
		}



		// DEPRECATED: The following tools will be removed in v1.4.0
		public static function FieldBasedDataCollector(PDOStatement $stmt, $field = 'id')
		{
			$result	= array();

			if ( !empty($field) )
				while ( ($row = $stmt->fetch()) !== FALSE ) $result[$row["{$field}"]] = $row;
			else
				while ( ($row = $stmt->fetch()) !== FALSE ) $result[] = $row;

			return $result;
		}

		public static function FilterBasedDataCollector(PDOStatement $stmt, $filterFunc, $field = NULL)
		{
			$result	= array();

			while ( ($row = $stmt->fetch()) !== FALSE )
			{
				$index = NULL;
				$filtered = $filterFunc($row, $index);

				if ( !empty($field) )
					$result[$filtered["{$field}"]] = $filtered;
				else
				if ( $index !== NULL )
					$result[$index] = $filtered;
				else
					$result[] = $filtered;
			}

			return $result;
		}
	}
