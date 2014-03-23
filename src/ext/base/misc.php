<?php

	define('KB', 	  1024.0, TRUE);	// KiloByte
	define('MB', KB * 1024.0, TRUE);	// MegaByte
	define('GB', MB * 1024.0, TRUE);	// GigaByte
	define('TB', GB * 1024.0, TRUE);	// TeraByte
	define('PB', TB * 1024.0, TRUE);	// PetaByte
	define('EB', PB * 1024.0, TRUE);	// ExaByte
	define('ZB', EB * 1024.0, TRUE);	// ZetaByte
	define('YB', ZB * 1024.0, TRUE);	// YotaByte

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

			case 'time':
				$val = strtotime("{$value}");
				return ($val === FALSE || $val < 0) ? 0 : $val;


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

	/**
	 * Returns the global variables defined in target source file
	 *
	 * @param string $____path_of_the_source_file_to_be_imprinted the targeted source file to be imprinted
	 *
	 * @return array|null returns array if the target file exists, null otherwise
	 */
	function ImprintFile($____path_of_the_source_file_to_be_imprinted = '')
	{
		$____pre_cached_to_be_deleted_existing_variables = array();
		$____pre_cached_to_be_deleted_existing_variables = get_defined_vars();

		if (!is_string($____path_of_the_source_file_to_be_imprinted) ||
			 empty($____path_of_the_source_file_to_be_imprinted))
			return NULL;

		require $____path_of_the_source_file_to_be_imprinted;
		$____path_of_the_source_file_to_be_imprinted = get_defined_vars();

		foreach ($____pre_cached_to_be_deleted_existing_variables as $varName => $varValue)
			unset($____path_of_the_source_file_to_be_imprinted[$varName]);

		return $____path_of_the_source_file_to_be_imprinted;
	}

	/**
	 * Returns the global variables defined in target package
	 *
	 * @param string $____path_of_the_package_file_to_be_imprinted the targeted package to be imprinted
	 *
	 * @return array|null returns array if the package exists, null otherwise
	 */
	function Imprint($____path_of_the_package_file_to_be_imprinted = '')
	{
		$____pre_cached_to_be_deleted_existing_variables = array();
		$____pre_cached_to_be_deleted_existing_variables = get_defined_vars();

		if (!is_string($____path_of_the_package_file_to_be_imprinted) ||
			empty($____path_of_the_package_file_to_be_imprinted))
			return NULL;

		require package($____path_of_the_package_file_to_be_imprinted, TRUE);
		$____path_of_the_package_file_to_be_imprinted = get_defined_vars();

		foreach ($____pre_cached_to_be_deleted_existing_variables as $varName => $varValue)
			unset($____path_of_the_package_file_to_be_imprinted[$varName]);

		return $____path_of_the_package_file_to_be_imprinted;
	}