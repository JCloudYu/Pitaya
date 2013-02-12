<?php
/*
 * File: PBStorage.php
 * Created by JCloudYu.
 * DateTime: 13/2/9 PM3:30
 */

class PBStorage
{
	private $_storage = NULL;
	private $_storedData = NULL;
	private $_autoMerge = TRUE;

	public function __construct($value = NULL, $autoMerge = TRUE) {

		$this->_autoMerge = $autoMerge;
		$this->_storage = array();

		if(is_array($value))
		{
			foreach($value as $key => $val)
			{
				if(!is_numeric($key)) $this->{$key} = $val;
			}

			return;
		}
	}

	public function __get($name){

		if($name == 'data') return $this->_retrieveData();

		if(!array_key_exists($name, $this->_storage))
			$this->_storage[$name] = new PBStorage(NULL, $this->_autoMerge);

		return $this->_storage[$name];
	}

	public function __set($name, $value) {

		if(!array_key_exists($name, $this->_storage) || !$this->_autoMerge)
			$this->_storage[$name] = new PBStorage(NULL, $this->_autoMerge);

		// INFO: storage->a = array('b' => 1, 'c' => array('index' => 'something'))
		if(is_array($value))
		{
			foreach($value as $key => $val)
			{
				if(!is_numeric($key))
				{
					$this->_storage[$name]->{$key} = $val;
					$this->_storage[$name]->_storedData = NULL;
				}
			}
		}
		// INFO: storage->a = 'abc';
		else
		{
			$this->_storage[$name]->_storage = array();
			$this->_storage[$name]->_storedData = $value;
		}

		// INFO: This is for multiple assignment such as $a = $storage->a->b->e->f = data3;
		// INFO: Hence we must throw the value back
		return $value;
	}

	public function clear($target = NULL)
	{
		if(is_null($target))
		{
			$this->_storedData = NULL;
			$this->_storage = array();
		}
		else
		{
			unset($this->_storage[$target]);
		}
	}

	public function merge($value)
	{
		if(!is_array($value)) return;

		foreach($value as $key => $val)
		{
			if(!is_numeric($key))
			{
				$this->{$key} = $val;
				$this->_storedData = NULL;
			}
		}
	}

	private function _retrieveData()
	{
		if(count($this->_storage) <= 0) return $this->_storedData;

		$returnData = array();
		foreach($this->_storage as $key => $item)
			$returnData[$key] = $item->_retrieveData();

		return $returnData;
	}
}