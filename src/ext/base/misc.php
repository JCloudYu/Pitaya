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
				return $value != TRUE;

			case 'null':
				return NULL;

			// INFO: Experimental Conversions
			case 'uint':
				if (!is_numeric($value)) return 0;
				return (float)sprintf('%u', $value);

			case 'raw':
			default:
				return $value;
		}
	}

	/**
	 * Decode the data according to the given encoding type
	 *
	 * @param mixed $data the data to be deocded
	 * @param string $encType the encoding type of the given data
	 *
	 * @return mixed the decoded data
	 */
	function iTrans($data, $encType)
	{
		switch ($encType)
		{
			case 'urlencoded':
				$data = urldecode($data);
				break;
			case 'base64':
				$data = base64_decode($data);
				break;
			default:
				break;
		}

		return $data;
	}

	/**
	 * Encode the data according to the given encoding type
	 *
	 * @param mixed $data the data to be encoded
	 * @param string $encType the encoding type of the given data
	 *
	 * @return mixed the encoded data
	 */
	function Trans($data, $encType)
	{
		switch ($encType)
		{
			case 'urlencoded':
				$data = urlencode($data);
				break;
			case 'base64':
				$data = base64_encode($data);
				break;
			default:
				break;
		}

		return $data;
	}