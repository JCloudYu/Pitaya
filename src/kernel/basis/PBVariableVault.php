<?php
/*
 * File: PBVariableVault.php
 * Created by Cloud.
 * DateTime: 13/2/10 PM12:25
 */

class PBVariableVault {

	private $_vault = NULL;
	public function __construct() {

		$this->_vault = array();
	}

	public function __destruct() {

		$this->_vault = NULL;
	}

	// INFO: Fast setter (without type casting)
	public function __set($name, $value) {

		$this->_vault[$name] = $value;
		return $value;
	}


	public function get($name, $type = 'raw', $default = NULL) {

		switch($type)
		{
			case 'int':
				if(array_key_exists($name, $this->_vault))
				{
					if(is_numeric($this->_vault[$name])) return intval($this->_vault[$name]);
					else return $default;
				}
				else
				{
					return $default;
				}
				break;
			case 'float':
				if(array_key_exists($name, $this->_vault))
				{
					if(is_numeric($this->_vault[$name])) return floatval($this->_vault[$name]);
					else return $default;
				}
				else
				{
					return $default;
				}
				break;
			case 'string':
				if(array_key_exists($name, $this->_vault))
				{
					return "$this->_vault[$name]";
				}
				else
				{
					return $default;
				}
				break;
			case 'raw':
			default:
				if(array_key_exists($name, $this->_vault))
				{
					return $this->_vault[$name];
				}
				else
				{
					return $default;
				}
				break;
		}
	}

	public function set($name, $value, $type = 'raw') {

		switch($type)
		{
			case 'int':
				if(is_numeric($this->_vault[$name]))
					$this->_vault[$name] = intval($value);
				else
					$this->_vault[$name] = $value;
				break;
			case 'float':
				if(is_numeric($this->_vault[$name]))
					$this->_vault[$name] = floatval($value);
				else
					$this->_vault[$name] = $value;
				break;
			case 'string':
				$this->_vault[$name] = "$value";
				break;
			case 'raw':
			default:
				$this->_vault[$name] = $value;
				break;
		}
	}

	public function clear($name = NULL)
	{
		if(is_null($name))
			$this->_vault = array();
		else
		if(array_key_exists($name, $this->_vault))
			unset($this->_vault[$name]);
	}
}