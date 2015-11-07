<?php
	function IS_POSITIVE( $value ) {
		return in_array( strtolower( @"{$value}" ), array( '1', 'yes', 'on', 'true' ));
	}
