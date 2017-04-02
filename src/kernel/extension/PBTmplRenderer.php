<?php
	class PBTmplRenderer {
		private static $_tplPath = NULL;
		public static function SetTplPath( $path ) {
			self::$_tplPath = "{$path}";
		}
		public static function Tpl( $tmplName, $basePath = NULL ) {
			return new PBTmplRenderer( $tmplName, $basePath );
		}
	
	
		private $_tplBasePath = "";
		private $_tplName = "";
		private function __construct( $tmplName, $basePath ) {
			$this->_tplBasePath = $basePath ?: self::$_tplPath ?: path( 'defaults.templates' );
			$this->_tplName = $tmplName;
			$this->_variables[ 'tmplId' ] = UUID();
		}
		public function __toString() { return $this(); }
		public function __invoke( $output = FALSE ) {
			$path = str_replace( '.', '/', $this->_tplName );
			$scriptPath = "{$this->_tplBasePath}/{$path}.php";
			if (!$output) ob_start();
			$results = self::Render( $scriptPath, data_merge(
				$this->_variables,
				[ 'identity' => $this->_identity ]
			));
			data_fuse( $this->_variables, $results );
			return (!$output) ? ob_get_clean() : "";
		}
		
		private $_variables = [];
		public function __set( $name, $value ) {
			$this->_variables[ $name ] = $value;
		}
		public function &__get($name) {
			return $this->_variables[$name];
		}
		
		private static function Render( $scriptPath, $variables = []) {
			extract( $variables, EXTR_OVERWRITE );
			$variables = [];
			require $scriptPath;
			return $variables;
		}
	}
