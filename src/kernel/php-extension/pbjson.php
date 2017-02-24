<?php
	class PBJSON {
		public static function decode( $jsonString, ...$args ){
			// search and remove comments like /* */ and //
			$json = preg_replace('#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t]//.*)|(^//.*)#', '', $jsonString);
			array_unshift($args, $json);
			return call_user_func_array( 'json_decode', $args );
		}

		public static function encode( ...$args ){ 
			return call_user_func_array( 'json_encode', $args );
		}
	}
