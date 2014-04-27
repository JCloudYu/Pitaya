<?php
	/**
 * Pitaya - db.php
 * Created by JCloudYu on 2013/09/25 22:37
 */

	using('sys.db.ExtPDO');

	/**
	 * Prepare and return a database connection singleton.
	 * This function will return NULL if there's no connection parameter given.
	 *
	 * @param array|null $param connection related parameter that is defined in env.ini
	 *
	 * @return ExtPDO|null the database connection singleton
	 */
	function DB($param = NULL, $option = array('CREATE_VAR'))
	{
		static $__singleton_db = NULL;

		if ($__singleton_db) return $__singleton_db;

		if ($param)
		{
			$dsn = ExtPDO::DSN($param['host'], $param['db'], $param['port']);
			$__singleton_db = new ExtPDO($dsn, $param['account'], $param['password'], $option);
			$__singleton_db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			$__singleton_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return $__singleton_db;
		}

		return NULL;
	}

	function LIMIT($SQL, $page = NULL, $pageSize = NULL, &$pageInfo = NULL)
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
		$countResult = DB()->fetch($sql, $param);


		$totalCount = $countResult['count'];
		$page		= TO($page,		'int');
		$pageSize	= TO($pageSize,	'int');

		if (!empty($pageSize))
		{
			$totalPages = ceil((float)$totalCount/(float)$pageSize);
			$page = min(max($page, 1), max($totalPages, 1));

			$from = ($page - 1) * $pageSize;
			$limitClause = "LIMIT {$from},{$pageSize}";
		}
		else
		{
			$totalPages = $page = 1;
			$pageSize = $totalCount;
			$limitClause = '';
		}



		$pageInfo = array(
			'pageSize'		=> $pageSize,
			'page'			=> $page,
			'totalPages'	=> $totalPages,
			'totalRecords'	=> $totalCount
		);

		return $limitClause;
	}

	function SET($data, &$param = NULL, $varIndex = FALSE)
	{
		$param = $sql = array();
		foreach ($userData as $col => $val)
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