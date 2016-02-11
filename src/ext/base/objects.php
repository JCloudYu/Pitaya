<?php
	/**
	 ** 1027.BadmintonLa - basic.obj.php
	 ** Created by JCloudYu on 2016/02/11 09:57
	 **/

	final class PBScopeTracer
	{
		private static $_singleton = NULL;
		public static function Scope() {
			if ( self::$_singleton !== NULL )
				return self::$_singleton;

			return ( self::$_singleton = new PBScopeTracer() );
		}



		private $_scope_levels;
		public function __construct( $stack = '', $seperator = ' ' )
		{
			if ( !is_array($stack) )
				$stack = empty($stack) ? array() : explode( "{$seperator}", "{$stack}" );

			$this->_scope_levels = $stack;
		}

		public function push( $item )
		{
			if ( func_num_args() < 1 )
				return FALSE;

			array_push( $this->_scope_levels, $item );
			return TRUE;
		}

		public function pop()
		{
			return @array_pop( $this->_scope_levels );
		}

		public function breadcrumb( $glue = '#' )
		{
			return implode( $glue,
				ary_filter( $this->_scope_levels, function($item) use( $glue ) {
					return str_replace( $glue, "_", "{$item}" );
				})
			);
		}

		public function __toString() { return $this->breadcrumb(); }
	}
