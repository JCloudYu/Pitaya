<?php
	final class PBTmplRenderer {
		private static $_tplPath = "";
		public static function SetTplPath( $path ) {
			self::$_tplPath = $path;
		}
		public static function Tpl( $tmplName ){
			return new TemplateRenderer( $tmplName );
		}
	

		
		private $_tplName = "";
		private function __construct( $tmplName ) {
			$this->_tplName = $tmplName;
		}
		public function __toString() {
			return $this();
		}
		public function __invoke( $output = FALSE ) {
			$path = str_replace( '.', '/', $this->_tplName );
			$scriptPath = self::$_tplPath . "/{$path}.php";
			if (!$output) ob_start();
			$this->render( $scriptPath, $this->_variables );
			return (!$output) ? ob_get_clean() : "";
		}
		
		private $_variables = array();
		public function __set( $name, $value ) {
			$this->_variables[ $name ] = $value;
		}
		public function &__get($name) {
			return $this->_variables[$name];
		}
		
		private function render( $scriptPath, $variables = array()) {
			extract( $variables, EXTR_OVERWRITE );
			require $scriptPath;
		}
	}
