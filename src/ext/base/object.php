<?php
/**
 * VWIMSIS - object.php
 * Created by JCloudYu on 2013/09/07 11:13
 */

	/**
	 * Singleton producer
	 *
	 * @param string $name the name of the object to be singleton
	 *
	 * @return mixed the singleton object
	 */
	function singleton($name)
	{
		static $singletons = array();

		$name = trim($name);

		if (array_key_exists($name, $singletons)) return $singletons[$name];

		$singletons[$name] = new $name();
		return $singletons[$name];
	}