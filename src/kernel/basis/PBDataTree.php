<?php
	class PBDataTree implements ArrayAccess {
		private $_anchor = NULL;
		
		public function __construct(stdClass $anchorObj = NULL) {
			$this->_anchor = $anchorObj ?: stdClass();
		}
		public function travel( $path ) {
			$comps = explode( '.', $path );
			$currObj = $this->_anchor;
			while( !empty($comps) ) {
				$item = array_shift($comps);
				if ( !is_a($currObj->{$item}, stdClass::class) ) {
					@$currObj->{$item} = stdClass();
				}
				
				$currObj = $currObj->{$item};
			}
			
			return PBDataTree($currObj);
		}
		
		
		
		public function& __get($name) {
			if ( $name === 'object' ) {
				return $this->_anchor;
			}
		
			if ( is_a($this->_anchor->{$name}, stdClass::class) ) {
				return PBDataTree($this->_anchor->{$name});
			}
			
			
			
			if ( property_exists($this->_anchor, $name) ) {
				return $this->_anchor->{$name};
			}
			
			return NULL;
		}
		public function __set($name, $value) {
			@$this->_anchor->{$name} = $value;
		}
		public function __unset($name) {
			unset($this->_anchor->{$name});
		}
		public function __isset($name) {
			return property_exists($this->_anchor, $name);
		}
		
		
		
		public function& offsetGet($offset) {
			return $this->{$offset};
		}
		public function offsetSet($offset, $value) {
			$this->{$offset} = $value;
		}
		public function offsetUnset($offset) {
			unset($this->{$offset});
		}
		public function offsetExists($offset) {
			return isset($this->{$name});
		}
	}
	
	function PBDataTree(stdClass $anchorObj = NULL) {
		return new PBDataTree($anchorObj);
	}