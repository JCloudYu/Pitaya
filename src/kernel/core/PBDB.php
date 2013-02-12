<?php
/*
 * File: PBDB.php
 * Created by Cloud.
 * DateTime: 13/2/9 PM6:32
 */

class PBDB
{
	private $_PHPDataObject = NULL;
	private $_PDOStatement = NULL;
	private $_lastResult = NULL;
	private $_lastAffectedRows = 0;

	public function __construct($host, $database, $username, $password, $driver = 'mysql')
	{
		$this->_PHPDataObject = new PDO("$driver:dbname=$database;host=$host", $username, $password);
		$this->_PHPDataObject->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		$this->_PHPDataObject->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	}

	public function __destruct()
	{
		$this->_PHPDataObject = NULL;
		$this->_PDOStatement = NULL;
		$this->_lastResult = NULL;
	}

	public function query($sql, $param)
	{
		$this->_lastResult = NULL;
		$this->_lastAffectedRows = 0;

		if(count($param) <= 0)
		{
			$this->_PDOStatement = $this->_PHPDataObject->query($sql);
			$this->_lastAffectedRows = $this->_PDOStatement->rowCount();
		}
		else
		{
			$this->_PDOStatement = $this->_PHPDataObject->prepare($sql);
			if($this->_PDOStatement->execute($param))
				$this->_lastAffectedRows = $this->_PDOStatement->rowCount();
		}
	}

	public function fetch()
	{
		$this->_lastResult = $this->_PDOStatement->fetch();
		if($this->_lastResult === FALSE) $this->_lastResult = NULL;
	}

	public function __get($name)
	{
		if(is_null($this->_lastResult)) return NULL;

		if($name == 'data') return $this->_lastResult;
		if($name == 'effectedRows') return $this->_lastAffectedRows;

		return $this->_lastResult[$name];
	}
}