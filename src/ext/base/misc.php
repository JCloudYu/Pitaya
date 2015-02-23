<?php

	using('ext.base.math');

	define('KB', 	  1024.0, TRUE);	// KiloByte
	define('MB', KB * 1024.0, TRUE);	// MegaByte
	define('GB', MB * 1024.0, TRUE);	// GigaByte
	define('TB', GB * 1024.0, TRUE);	// TeraByte
	define('PB', TB * 1024.0, TRUE);	// PetaByte
	define('EB', PB * 1024.0, TRUE);	// ExaByte
	define('ZB', EB * 1024.0, TRUE);	// ZetaByte
	define('YB', ZB * 1024.0, TRUE);	// YotaByte


	function TO($value, $type, $options = NULL)
	{
		if (is_array($type))
		{
			$criteria	= @$type['criteria'];
			$default	= @$type['default'];

			// NOTE: The following line must put at the end of this scope for overwriting prevention!!!
			$type		= @$type['type'];
		}
		else
			$default = $criteria = NULL;

		if ( !is_array( $options ) ) $options = array();



		$type = is_string($type) ? strtolower($type) : 'raw';


		$illustrator = explode(' ', trim($type));
		$baseType = array_shift($illustrator);

		switch($baseType)
		{
			// INFO: int [strict]
			case 'int':
				$value = trim("$value");

				if (in_array('strict', $illustrator))
					return EXPR_INT($value) ? intval($value) : 0;
				else
					return EXPR_NUMERIC($value) ? intval($value) : 0;

			// INFO: float [strict]
			case 'float':
				$value = trim("$value");

				if (in_array('strict', $illustrator))
					return EXPR_FLOAT($value) ? floatval($value) : 0.0;
				else
					return EXPR_NUMERIC($value) ? floatval($value) : 0.0;

			// INFO: string [decode-url] [encode-url] [purge-html]
			case 'string':
				$value = trim("$value");

				if (in_array('lower-case', $illustrator))
					$value = strtolower($value);
				else
				if (in_array('upper-case', $illustrator))
					$value = strtoupper($value);

				if (in_array('encode-url', $illustrator))
					$value = urlencode($value);

				if (in_array('decode-url', $illustrator))
					$value = urldecode($value);

				if (in_array('purge-html', $illustrator))
					$value = htmlspecialchars($value);

				if (in_array('json', $illustrator) || in_array('json-array', $illustrator))
					$value = json_decode($value, TRUE);

				if (in_array('json-object', $illustrator))
					$value = json_decode($value);

				return $value;

			// INFO: boolean
			case 'boolean':
			case 'bool':
				if (in_array('is-true', $illustrator))
					return ($value == TRUE);
				else
				if (in_array('is-false', $illustrator))
					return ($value == FALSE);
				else
					return !(empty($value));

			case 'array':
				if ( is_array( $value ) )
					return $value;

				if ( !array_key_exists('delimiter', $options) ) return array();

				$value = "{$value}";
				if ( !empty( $options['regex'] ) || empty($options['delimiter']) )
					return preg_split("/{$options['delimiter']}/", $value);
				else
					return explode( $options['delimiter'], $value );


			// INFO: time string
			case 'time':
				$value = trim("$value");

				$val = strtotime("{$value}");
				return ($val === FALSE || $val < 0) ? 0 : $val;

			// INFO: range
			case 'range':
				if (!is_array($criteria)) $criteria = array();
				return (in_array($value, $criteria)) ? $value : $default;



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
	 * Returns the global variables defined in target package
	 *
	 * @param string $____path_of_the_package_file_to_be_imprinted the targeted package to be imprinted
	 *
	 * @return array|null returns array if the package exists, null otherwise
	 */
	function Imprint($____path_of_the_package_file_to_be_imprinted = '')
	{
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

	/**
	 * Execute a php script and returns the output generated by the script
	 *
	 * @param string $____path_of_the_script_to_be_executed
	 * @param array $____parameters_used_in_the_executed_script
	 * @param array $____script_defined_variables
	 *
	 * @return string the output generated by the executed script
	 */
	function Script($____path_of_the_script_to_be_executed, $____parameters_used_in_the_executed_script = array(), &$____script_defined_variables = NULL)
	{
		$____pre_cached_to_be_deleted_existing_variables = get_defined_vars();

		if (!is_string($____path_of_the_script_to_be_executed) || empty($____path_of_the_script_to_be_executed))
			return '';


		ob_start();
		extract($____parameters_used_in_the_executed_script);
		require $____path_of_the_script_to_be_executed;
		$____output_buffer_generated_by_executed_script = ob_get_clean();
		$____variables_that_are_used_in_executed_script = get_defined_vars();



		foreach ($____pre_cached_to_be_deleted_existing_variables as $varName => $varValue)
			unset($____variables_that_are_used_in_executed_script[$varName]);

		foreach ($____parameters_used_in_the_executed_script as $varName => $varValue)
			unset($____variables_that_are_used_in_executed_script[$varName]);


		$____script_defined_variables = $____variables_that_are_used_in_executed_script;
		return $____output_buffer_generated_by_executed_script;
	}

	/**
	 * Execute a php script and directly display the output generated by the script
	 *
	 * @param string $____path_of_the_script_to_be_executed
	 * @param array $____parameters_used_in_the_executed_script
	 * @param array $____script_defined_variables
	 */
	function ScriptOut($____path_of_the_script_to_be_executed, $____parameters_used_in_the_executed_script = array(), &$____script_defined_variables = NULL)
	{
		$____pre_cached_to_be_deleted_existing_variables = get_defined_vars();

		if (!is_string($____path_of_the_script_to_be_executed) || empty($____path_of_the_script_to_be_executed)) return;


		extract($____parameters_used_in_the_executed_script);
		require $____path_of_the_script_to_be_executed;
		$____variables_that_are_used_in_executed_script = get_defined_vars();



		foreach ($____pre_cached_to_be_deleted_existing_variables as $varName => $varValue)
			unset($____variables_that_are_used_in_executed_script[$varName]);

		foreach ($____parameters_used_in_the_executed_script as $varName => $varValue)
			unset($____variables_that_are_used_in_executed_script[$varName]);


		$____script_defined_variables = $____variables_that_are_used_in_executed_script;
	}
