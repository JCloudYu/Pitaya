<?php

class ExtPDO extends PDO
{
	const VARIABLE_TABLE = '__extpdo_syswide_variables';

	private $__use_Variable = FALSE;

	public function __construct($dsn, $username, $userpass, $option = array()) {

		$forceVar = ($option['CREATE_VAR']) ? TRUE : FALSE;
		unset($option['CREATE_VAR']);

		if (count($option) > 0)
			parent::__construct($dsn, $username, $userpass, $option);
		else
			parent::__construct($dsn, $username, $userpass);

		$this->__use_Variable = $this->__checkVariableCap($forceVar);
	}

	private function __checkVariableCap($forceVariable) {

		$stmt = $this->select("SHOW TABLES;");
		while (($row = $stmt->fetch(PDO::FETCH_NUM)) !== FALSE)
			if ($row[0] === ExtPDO::VARIABLE_TABLE) return TRUE;

		if ($forceVariable)
		{
			return $this->query("CREATE TABLE IF NOT EXISTS `" . ExtPDO::VARIABLE_TABLE . "`(`id` int(11) NOT NULL AUTO_INCREMENT,
								`name` varchar(255) NOT NULL,
						 		`value` blob,

						 		PRIMARY KEY (`id`),
						 		UNIQUE KEY `name_UNIQUE` (`name`));"
			);
		}
	}

	public function select($sqlStmt, $stmtVars = NULL) {

		if ($stmtVars)
		{
			$pdoStmt = parent::prepare($sqlStmt);
			if ($pdoStmt === FALSE) return FALSE;

			$pdoStmt->execute($stmtVars);

			return $pdoStmt;
		}

		return parent::query($sqlStmt);
	}

	public function query($sqlStmt, $stmtVars = NULL) {

		if ($stmtVars)
		{
			$pdoStmt = parent::prepare($sqlStmt);
			if ($pdoStmt === FALSE) return FALSE;

			$pdoStmt->execute($stmtVars);
		}
		else
		{
			$pdoStmt = parent::query($sqlStmt);
			if ($pdoStmt === FALSE) return FALSE;
		}

		return $pdoStmt->rowCount();
	}

	public function fetch($sqlStmt, $stmtVars = NULL) {

		if ($stmtVars)
		{
			$pdoStmt = parent::prepare($sqlStmt);
			if (!$pdoStmt) return NULL;

			$pdoStmt->execute($stmtVars);
			$fetchData = $pdoStmt->fetch();
		}
		else
		{
			$pdoStmt = parent::query($sqlStmt);
			if (!$pdoStmt) return NULL;

			$fetchData = $pdoStmt->fetch();
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

	public function __unset($name) {

		static $table = ExtPDO::VARIABLE_TABLE;
		$this->query("DELETE FROM `$table` WHERE `name` = '$name';");
	}

	public function __get($name) {

		static $table = ExtPDO::VARIABLE_TABLE;
		$row = $this->fetch("SELECT * FROM `$table` WHERE `name` = '$name'");


		return ($row) ? json_decode($row['value'], TRUE) : NULL;
	}

	public function __set($name, $value) {

		static $table = ExtPDO::VARIABLE_TABLE;

		$value = json_encode($value);

		$result = $this->query("UPDATE `$table` SET `value` = :value WHERE `name` = :name;",
							   array(':name' => $name,
									 ':value' => $value));

		if ($result > 0) return $result;

		return $this->query("INSERT INTO `$table`(`name`, `value`) VALUES(:name, :value);",
							 array(':name' => $name,
								   ':value' => $value));
	}
}