<?php

	// $value, $type, $filter = NULL, $default = NULL
	function TYPE_CAST( $value, $type, ...$args )
	{
		$opt  = explode( ' ', strtolower(trim("{$type}")) );
		$base = @array_shift( $opt );

		switch( $base )
		{
			case 'int':
				return TYPE_CAST_INT( $value, $opt, ...$args );
			case 'float':
				return TYPE_CAST_FLOAT( $value, $opt, ...$args );

			// region string [force] [lower-case] [upper-case] [decode-url] [encode-url] [purge-html]
			/*
			 *	CAST( $value, 'string purge-html', $default )
			 */
			case 'string':
				return TYPE_CAST_STRING( $value, $opt, ...$args );
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
			 *	CAST( $val, 'time parse', $format, $default )	// Get time from format
			 */
			case 'time':
				// Parse time according to format
				if ( in_array( 'parse' ) )
				{
					$dateObj = date_create_from_format( "{$filter}", "{$value}" );
					if ( $dateObj === FALSE )
						return $nArgs > 3 ? $default : -1;
					
					return $dateObj->getTimestamp();
				}
			
			
			
				// INFO: Automatically parse time from string
				$val	= strtotime(trim("{$value}"));
				$fmtErr	= ($val === FALSE || $val < 0);

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
	function TYPE_CAST_INT( $value, $options = [], $default = 0 ) {

		$value		= trim("$value");
		$validate	= (in_array( 'strict', $options )) ? "EXPR_INT" : "EXPR_NUMERIC";
		$status		= $validate($value);
		
		if ( $status )
		{
			if (in_array( 'positive', $options ))
				$status = $status && ($value[0] != "-");
			elseif ( in_array( 'negative', $options ) )
				$status = $status && ($value[0] == "-");
		}

		
		if ( !$status )
			return $default;
		else
			return (in_array( 'no-casting', $options )) ? $value : @intval($value);
	}
	function TYPE_CAST_FLOAT( $value, $options = [], $default = 0.0 ) {

		$value		= trim("$value");
		$validate	= (in_array( 'strict', $options )) ? "EXPR_FLOAT" : "EXPR_NUMERIC";
		$status		= $validate($value);
		
		if ( $status )
		{
			if (in_array( 'positive', $options ))
				$status = $status && ($value[0] != "-");
			elseif ( in_array( 'negative', $options ) )
				$status = $status && ($value[0] == "-");
		}

		
		if ( !$status )
			return $default;
		else
			return (in_array( 'no-casting', $options )) ? $value : @floatval($value);
	}
	function TYPE_CAST_STRING( $value, $options = [], ...$args ) {
		
		$value = in_array( 'no-trim', $options ) ? @"{$value}" : @trim("{$value}");

		if (in_array( 'encode-url', $options ))
			$value = urlencode($value);

		if (in_array( 'decode-url', $options ))
			$value = urldecode($value);

		if (in_array( 'lower-case', $options ))
			$value = strtolower($value);

		if (in_array( 'upper-case', $options ))
			$value = strtoupper($value);

		if (in_array( 'purge-html', $options ))
			$value = htmlspecialchars($value);
			
		if (in_array( 'remove-html', $options ))
			$value = strip_tags( $value );

		return $value;
	}
