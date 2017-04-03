<?php
	// region [ Fusion two data ]
	function &data_fuse(&$data1, $data2, $overwrite = TRUE){
		$targetIsObj = is_object($data1);
		foreach ( $data2 as $field => $value ) {
			if ($targetIsObj)
			{
				if ( !property_exists( $data1, $field ) || $overwrite )
					$data1->{$field} = $value;
			}
			else
			{
				if ( !array_key_exists( $field, $data1 ) || $overwrite )
					$data1[$field] = $value;
			}
		}
		
		return $data1;
	}
	function data_merge($data1, ...$sources) {
		$targetIsObj = is_object($data1);
		$destination = ($targetIsObj) ? clone $data1 : $data1;
		
		foreach( $sources as $source ) {
			data_fuse( $destination, $source );
		}
		
		return $destination;
	}
	function data_set($data1, ...$sources) {
		$targetIsObj = is_object($data1);
		$destination = ($targetIsObj) ? clone $data1 : $data1;
		
		foreach( $sources as $source ) {
			data_fuse( $destination, $source, FALSE );
		}
		
		return $destination;
	}
	// endregion
	
	// region [ Looping over data content ]
	function data_filter( $traversable, $filter = NULL, $skipVal = FALSE )
	{
		if ( !is_array($traversable) && !is_object($traversable) && !($traversable instanceof Traversable) ) return FALSE;

		$arguments	= func_get_args();
		$skipMode	= count($arguments) != 2;

		if ( !is_callable($filter) )
		{
			$filter = (!$skipMode) ?
				function( $item ) { return $item; } :
				function( $item ) { return (empty($item)) ? FALSE : $item; };
		}

		$collected = array();
		foreach ( $traversable as $idx => $item )
		{
			$result = $filter($item, $idx);
			if ( $skipMode && ($result === $skipVal) ) continue;

			if ( $idx === NULL )
				$collected[] = $result;
			else
				$collected[$idx] = $result;
		}

		return $collected;
	}
	function ary_filter() { return call_user_func_array( "data_filter", func_get_args() ); }
	function object_filter() { return call_user_func_array( "data_filter", func_get_args() ); }
	// endregion

	// region [ JSON Processing ]
	function pb_json_decode( $jsonString, ...$args ){
		// search and remove comments like /* */ and //
		$json = preg_replace('#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t]//.*)|(^//.*)#', '', $jsonString);
		array_unshift($args, $json);
		return call_user_func_array( 'json_decode', $args );
	}

	function pb_json_encode( ...$args ){ 
		return call_user_func_array( 'json_encode', $args );
	}
	// endregion

	// region [ Hash Functions ]
	function sha256( $content, $rawOutput = FALSE ) {
		return hash( 'sha256', $content, $rawOutput );
	}
	
	function sha256_file( $fileName, $rawOutput = FALSE ) {
		return hash_file( 'sha256', $fileName, $rawOutput );
	}
	
	function sha512( $content, $rawOutput = FALSE ) {
		return hash( 'sha512', $content, $rawOutput );
	}
	
	function sha512_file( $fileName, $rawOutput = FALSE ) {
		return hash_file( 'sha512', $fileName, $rawOutput );
	}
	// endregion