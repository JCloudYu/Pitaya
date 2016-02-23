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


	abstract class PBJSONContainer implements ArrayAccess, Iterator
	{
		protected $_container = array();
		protected $_end		  = TRUE;

		abstract function safe_cast();

		public function offsetSet( $offset, $value ) {
			if ( $offset === NULL )
				$this->_container[] = $value;
			else
				$this->_container[ $offset ] = $value;
		}
		public function& offsetGet( $offset ) {
			return $this->_container[ $offset ];
		}
		public function offsetExists( $offset ) {
			return array_key_exists( $offset, $this->_container );
		}
		public function offsetUnset( $offset ) {
			unset( $this->_container[ $offset ] );
		}

		public function& __get( $name ) {
			return $this[$name];
		}
		public function __set( $name, $value ) {
			$this[ $name ] = $value;
		}
		public function __isset( $name ) {
			return isset( $this[$name] );
		}
		public function __unset( $name ) {
			unset( $this[$name] );
		}

		public function& current () {
			return current($this->_container);
		}
		public function key() {
			return key($this->_container);
		}

		public function next() {
			$this->_end = !next($this->_container);
		}

		public function rewind() {
			reset($this->_container);
		}

		public function valid() {
			return ( count($this->_container) <= 0 ) ? FALSE : $this->_end;
		}

		public static function Flatten( $content ) {

			if ( is_a( $content, 'stdClass' ) )
			{
				$result = new stdClass();
				foreach ( $content as $key => $value )
					$result->{$key} = self::Flatten($value);
				return $result;
			}
			else
			if ( is_array( $content ) )
			{
				foreach ( $content as $key => $value )
					$content[$key] = self::Flatten($value);
				return $content;
			}
			else
			if ( is_a( $content, 'PBJSONContainer' ) )
				return $content->safe_cast();


			return $content;
		}
	}

	class PBJSONObject extends PBJSONContainer
	{
		public static function JSONObject( $data = NULL ) {
			$obj = new PBJSONObject();

			if ( func_num_args() > 0 && is_array($data) )
				$obj->_container = $data;

			return $obj;
		}

		public function safe_cast() {
			foreach ( $this->_container as $key => $value )
				$this->_container[$key] = PBJSONContainer::Flatten( $value );

			return (object)$this->_container;
		}
	}

	class PBJSONArray extends PBJSONContainer
	{
		public static function JSONArray( $data = NULL ) {
			$obj = new PBJSONArray();

			if ( func_num_args() > 0 && is_array($data) )
				$obj->_container = $data;

			return $obj;
		}

		public function safe_cast() {
			foreach ( $this->_container as $key => $value )
				$this->_container[$key] = PBJSONContainer::Flatten( $value );

			return array_values($this->_container);
		}
	}
