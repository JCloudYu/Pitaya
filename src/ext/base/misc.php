<?php
	using( 'ext.base.math' );
	using( 'ext.base.array' );

	define('KB', 	  1024.0);	// KiloByte
	define('MB', KB * 1024.0);	// MegaByte
	define('GB', MB * 1024.0);	// GigaByte
	define('TB', GB * 1024.0);	// TeraByte
	define('PB', TB * 1024.0);	// PetaByte
	define('EB', PB * 1024.0);	// ExaByte
	define('ZB', EB * 1024.0);	// ZetaByte
	define('YB', ZB * 1024.0);	// YotaByte


	function CAST( $value, $type, $filter = NULL, $default = NULL )
	{
		$opt	= explode( ' ', strtolower(trim("{$type}")) );
		$base	= @array_shift( $opt );
		$nArgs	= func_num_args();

		switch( $base )
		{
			// region int [strict] [no-casting]
			/*
			 *	CAST( $value, 'int strict no-casting', $default )
			 */
			case 'int':
				$default = $filter;

				$value = trim("$value");
				$defaultVal = ($nArgs > 2) ? $default : 0;
				$procFunc	= ( in_array('strict', $opt) ) ? "EXPR_INT" : "EXPR_NUMERIC";
				$status = $procFunc($value);
				
				if ( in_array( 'positive', $opt ) )
				{
					$str = "{$value}";
					$status = $status && !( $str[0] == "-" );
				}
				else
				if ( in_array( 'negative', $opt ) )
				{
					$str = "{$value}";
					$status = $status && ( $str[0] == "-" );
				}

				
				if ( !$status )
					return $defaultVal;
				else
					return ( in_array( 'no-casting', $opt ) ) ? $value : @intval($value);
			// endregion

			// region float [strict] [no-casting]
			/*
			 *	CAST( $value, 'float strict no-casting', $default )
			 */
			case 'float':
				$default = $filter;
				
				$value = trim("$value");
				$defaultVal = ($nArgs > 2) ? $default : 0.0;
				$procFunc	= ( in_array('strict', $opt) ) ? "EXPR_FLOAT" : "EXPR_NUMERIC";
				$status = $procFunc($value);
				
				if ( in_array( 'positive', $opt ) )
				{
					$str = "{$value}";
					$status = $status && !( $str[0] == "-" );
				}
				else
				if ( in_array( 'negative', $opt ) )
				{
					$str = "{$value}";
					$status = $status && ( $str[0] == "-" );
				}

				
				if ( !$status )
					return $defaultVal;
				else
					return ( in_array( 'no-casting', $opt ) ) ? $value : @floatval($value);
			// endregion

			// region string [force] [lower-case] [upper-case] [decode-url] [encode-url] [purge-html]
			/*
			 *	CAST( $value, 'string purge-html', $default )
			 */
			case 'string':
				$default = $filter;

				if ( !is_string( $value ) && ($nArgs > 2) && !in_array( 'force', $opt ) ) return $default;

				$value = in_array( 'no-trim', $opt ) ? "{$value}" : trim("{$value}");

				if (in_array('encode-url', $opt))
					$value = urlencode($value);

				if (in_array('decode-url', $opt))
					$value = urldecode($value);

				if (in_array('lower-case', $opt))
					$value = strtolower($value);
				else
				if (in_array('upper-case', $opt))
					$value = strtoupper($value);

				if (in_array('purge-html', $opt))
					$value = htmlspecialchars($value);

				return $value;
			// endregion

			// region range [op-and] [op-or] [strict]
			/*
			 *	CAST( $value, 'range op-and op-or', array(...), $default );
			 */
			case 'range':
				$defaultVal = ( $nArgs > 3 ) ? $default : NULL;

				if ( !is_array( $filter ) )
					return $defaultVal;


				$booleanOperator = ( in_array( 'op-and', $opt ) ) ? IN_ARY_MODE_AND : IN_ARY_MODE_OR;
				$strictTyping	 = ( in_array( 'strict', $opt ) ) ? IN_ARY_MODE_STRICT : IN_ARY_MODE_NONE_STRICT;
				return in_ary( $value, $filter, $booleanOperator | $strictTyping ) ? $value : $defaultVal;
			// endregion

			// region array [purge-empty] [regex] [delimiter] [json]
			/*
			 *	CAST( $value, 'array', $default ) // TYPING MODE
			 *	CAST( $value, 'array regex', $pattern, $default )	// SPLIT MODE
			 *	CAST( $value, 'array delimiter', $pattern, $default )	// SPLIT MODE
			 */
			case 'array':
				$typingOptions	= array( 'delimiter', 'regex' );
				$typingMode		= (CAST( $typingOptions, 'range', $opt ) === NULL);

				if ( $typingMode === NULL )	// INFO: TYPING MODE
					$defaultVal = ($nArgs > 2) ? $filter : array();
				else						// INFO: SPLIT MODE
					$defaultVal = ($nArgs > 3) ? $filter : array();



				if ( trim("{$value}") === "" ) return $defaultVal;



				if ( is_array($value) )
					$converted = $value;
				else
				if ( in_array( 'delimiter', $opt ) )
					$converted = @explode( "{$filter}", "{$value}" );
				else
				if ( in_array( 'regex', $opt ) )
					$converted = @preg_split( "{$filter}", "{$value}" );
				else
					$converted = $defaultVal;

				return ( in_array( 'purge-empty', $opt ) && is_array($converted) && (count($converted) == 0) ) ? $defaultVal : $converted;
			// endregion

			// region time [format]
			/*
			 *	CAST( $val, 'time', $default )					// Epoch Mode
			 *	CAST( $val, 'time format', $format, $default )	// Format Text Mode
			 */
			case 'time':
				$val	= strtotime(trim("{$value}"));
				$fmtErr	= (($val === FALSE || $val < 0) && !EXPR_INT($val = $value));



				// INFO: Format Text Mode
				if ( in_array( 'format', $opt ) )
				{
					if ( $fmtErr && ($nArgs > 3) )
						return $default;
					else
						return date( "{$filter}", ($fmtErr) ? 0 : $val );
				}



				// INFO: Epoch Mode
				$defaultVal = ($nArgs > 2) ? $filter : 0;
				return $fmtErr ? $defaultVal : $val;
			// endregion

			// region bool [is-true] [is-false]
			case 'boolean':
			case 'bool':
				if (in_array('is-true', $opt))
					return ($value === TRUE);
				else
				if (in_array('is-false', $opt))
					return !($value === FALSE);
				else
					return !(empty($value));
			// endregion

			case 'raw':
			default:
				return $value;
		}
	}

	// DEPRECATED: This api will be removed from v2.0.0
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
					$value = EXPR_INT($value) ? $value : 0;
				else
					$value = EXPR_NUMERIC($value) ? $value : 0;

				return (in_array( 'no-casting', $illustrator )) ? $value : @intval($value);

			// INFO: float [strict]
			case 'float':
				$value = trim("$value");

				if (in_array('strict', $illustrator))
					$value = EXPR_FLOAT($value) ? $value : 0.0;
				else
					$value = EXPR_NUMERIC($value) ? $value : 0.0;

				return (in_array( 'no-casting', $illustrator )) ? $value : @floatval($value);

			// INFO: string [decode-url] [encode-url] [purge-html]
			case 'string':
				$value = trim("$value");

				if (in_array('encode-url', $illustrator))
					$value = urlencode($value);

				if (in_array('decode-url', $illustrator))
					$value = urldecode($value);

				if (in_array('lower-case', $illustrator))
					$value = strtolower($value);
				else
				if (in_array('upper-case', $illustrator))
					$value = strtoupper($value);

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

				$value = "{$value}";

				if ( empty($value) )
					return array();
				else
				if ( !empty( $options['regex'] ) )
					return preg_split("{$options['regex']}", $value);
				else
				if ( !empty($options['delimiter']) )
					return explode( $options['delimiter'], $value );

				return array();


			// INFO: time string
			case 'time':
				$value = trim("$value");

				$val = strtotime("{$value}");
				return ($val === FALSE || $val < 0) ? 0 : $val;

			// INFO: range
			case 'range':
				if (!is_array($criteria)) $criteria = array();
				return (in_array($value, $criteria)) ? $value : $default;

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





	// DEPRECATED: Following APIs will be removed in v2.0.0
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
