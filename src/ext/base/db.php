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