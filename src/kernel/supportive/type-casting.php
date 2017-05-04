<?php
	function object($item = NULL, $force = FALSE) {
		return call_user_func_array( 'stdClass', func_get_args() );
	}