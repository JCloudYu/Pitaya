<?php

class ExtPDO extends PDO
{
	public function __construct($dsn, $username, $userpass, $option = array()) {
		if (count($option) > 0)
			parent::__construct($dsn, $username, $userpass, $option);
		else
			parent::__construct($dsn, $username, $userpass);
	}

	public function select($sqlStmt, $stmtVars = NULL) {

		if ($stmtVars)
		{
			$pdoStmt = parent::prepare($sqlStmt);
			$pdoStmt->execute($stmtVars);

			return $pdoStmt;
		}

		return parent::query($sqlStmt);
	}

	public function query($sqlStmt, $stmtVars = NULL) {

		if ($stmtVars)
		{
			$pdoStmt = parent::prepare($sqlStmt);
			$pdoStmt->execute($stmtVars);
		}
		else
		{
			$pdoStmt = parent::query($sqlStmt);
		}

		return $pdoStmt->rowCount();
	}

	public function fetch($sqlStmt, $stmtVars = NULL) {

		if ($stmtVars)
		{
			$pdoStmt = parent::prepare($sqlStmt);
			$pdoStmt->execute($stmtVars);

			$fetchData = $pdoStmt->fetch();
		}
		else
		{
			$fetchData =  parent::query($sqlStmt)->fetch();
		}

		return $fetchData ? $fetchData : NULL;
	}

	public function fetchAll($sqlStmt, $stmtVars = NULL) {

		if ($stmtVars)
		{
			$pdoStmt = parent::prepare($sqlStmt);
			$pdoStmt->execute($stmtVars);

			$fetchData = $pdoStmt->fetchAll();
		}
		else
		{
			$fetchData =  parent::query($sqlStmt)->fetchAll();
		}

		return $fetchData;
	}
}