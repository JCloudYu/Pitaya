<?php
	function stdClass($item = NULL, $force = FALSE) {
		if ( func_num_args() == 0 ) {
			return new stdClass();
		}
		else
		if ( is_array($item) ) {
			return (object)$item;
		}
		else
		if ( is_a($item,stdClass::class) ) {
			return $item;
		}
		
		return $force ? new stdClass() : NULL;
	}
	
	if ( !function_exists( 'object' ) ) {
		function object($item = NULL, $force = FALSE) {
			return call_user_func_array( 'stdClass', func_get_args() );
		}
	}