<?php

	/**
	 * Parse the given $value into specified $type
	 *
	 * @param mixed $value thevalue to be parse
	 * @param string $type expected converting type
	 *
	 * @return mixed the converted value
	 */
	function TO($value, $type)
	{
		$type = is_string($type) ? strtolower($type) : 'raw';

		switch($type)
		{
			case 'int':
				return intval($value);

			case 'float':
				return floatval($value);

			case 'string':
				return trim("$value");

			case 'boolean':
				return $value == TRUE;

			case 'null':
				return NULL;

			case 'raw':
			default:
				return $value;
		}
	}