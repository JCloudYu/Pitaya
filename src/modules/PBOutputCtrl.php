<?php
	/**
	 ** 1028.CSMS-BDF - PBOutputCtrl.php
	 ** Created by JCloudYu on 2015/12/07 15:49
	 **/
	using( 'kernel.core.PBModule' );
	using('sys.net.PBHTTP');

	class PBHtmlOutput extends PBModule
	{
		private $_baseRCPath = '';

		private $_js = array('prepend' => [], 'append' => [], 'last' => []);
		private $_css = [];

		private $_jsFiles = array( 'prepend' => [], 'append' => [] );
		private $_cssFiles = [];

		private $_header = [];

		private $_prop	= [];
		private $_elm	= [];
		private $_meta	= [];

		public function exec($param)
		{
			$js = array('prepend' => '', 'append' => '', 'file prepend' => '', 'file append' => '');
			$css = array('inline' => '', 'file' => '');
			$header = '';


			// region [ JS Processing ]
			$js['prepend'] = implode("\r\n", $this->_js['prepend']);
			$js['prepend'] = (!empty($js['prepend'])) ? "<script type='application/javascript'>{$js['prepend']}</script>" : '';

			$js['append']  = implode("\r\n", $this->_js['append']);
			$js['append']  = (!empty($js['append'])) ? "<script type='application/javascript'>{$js['append']}</script>" : '';

			$js['last']	   = implode("\r\n", array_reverse($this->_js['last']));
			$js['last']    = (!empty($js['last'])) ? "<script type='application/javascript'>{$js['last']}</script>" : '';


			$this->_jsFiles[ 'prepend' ] = array_unique( $this->_jsFiles[ 'prepend' ] );
			foreach ($this->_jsFiles[ 'prepend' ] as $filePath)
				$js['file prepend'] .= "<script type='application/javascript' src='{$filePath}'></script>\r\n";

			$this->_jsFiles[ 'append' ] = array_unique( $this->_jsFiles[ 'append' ] );
			foreach ($this->_jsFiles[ 'append' ] as $filePath)
				$js['file append'] .= "<script type='application/javascript' src='{$filePath}'></script>\r\n";
			// endregion

			// region [ CSS Processing ]
			$css['inline'] = implode("\r\n", $this->_css);
			$css['inline'] = (!empty($css['inline'])) ? "<style type='text/css'>{$css['inline']}</style>" : '';

			$this->_cssFiles = array_unique( $this->_cssFiles );
			foreach ($this->_cssFiles as $filePath)
				$css['file'] .= "<link href='{$filePath}' type='text/css' rel='stylesheet' />\r\n";

			$header	 = implode("", $this->_header);
			$metaTag = implode('', ary_filter( $this->_meta, function( $meta, &$idx ) {
				if ( !empty($meta['property']) )
					$idx = "property:{$meta['property']}";
				else
				if ( !empty($meta['name']) )
					$idx = "name:{$meta['name']}";
				else
					$idx = NULL;



				$attributes = [];
				foreach( $meta as $attr => $value )
					$attributes[] = "{$attr}=\"" . htmlentities($value) . "\"";
				$attributes = implode( ' ', $attributes );
				return "<meta {$attributes}/>";
			}));
			// endregion

			// region [ Prepare HTML contents ]
			$baseBody = "{$param}";
			$elm = $this->_elm;

			// region [ Generate Page Wrapper ]
			$contentWrapper = call_user_func(function() use($baseBody, $elm) {
				if ( empty($elm['page']) || !is_array( $elm['page'] ) ) return $baseBody;

				$attributes = ary_filter( $elm['page'], function( $val, $attr ){
					$attr	= strip_tags( $attr );

					if ( is_bool($val) )
						return ($val) ? "{$attr}" : FALSE;

					$val	= htmlentities( $val, ENT_QUOTES | ENT_IGNORE, 'UTF-8' );
					return "{$attr}=\"{$val}\"";
				}, FALSE);
				$attributes = trim(implode( ' ', $attributes ));
				return empty( $attributes ) ? $baseBody : "<div {$attributes}>{$baseBody}</div>";
			});
			// endregion

			// region [ Generate body Attributes ]
			$bodyAttr = call_user_func(function() use($elm) {
				if ( empty($elm['body']) || !is_array( $elm['body'] ) ) return  '';

				$attributes = ary_filter( $elm['body'], function( $val, $attr ){
					$attr	= strip_tags( $attr );

					if ( is_bool($val) )
						return ($val) ? "{$attr}" : FALSE;

					$val	= htmlentities( $val, ENT_QUOTES | ENT_IGNORE, 'UTF-8' );
					return "{$attr}=\"{$val}\"";
				}, FALSE);

				return trim(implode( ' ', $attributes ));
			});
			// endregion

			// region [ Generate html Attributes ]
			$htmlAttr = call_user_func(function() use($elm) {
				if ( empty($elm['html']) || !is_array( $elm['html'] ) ) return  '';

				$attributes = ary_filter( $elm['html'], function( $val, $attr ){
					$attr	= strip_tags( $attr );

					if ( is_bool($val) )
						return ($val) ? "{$attr}" : FALSE;

					$val	= htmlentities( $val, ENT_QUOTES | ENT_IGNORE, 'UTF-8' );
					return "{$attr}=\"{$val}\"";
				}, FALSE);

				return trim(implode( ' ', $attributes ));
			});
			// endregion
			// endregion


			$appendedScript = "{$js['append']}{$js['file append']}{$js['last']}";
			echo "<!DOCTYPE html><html {$htmlAttr}><head>{$metaTag}{$header}{$js['file prepend']}{$js['prepend']}{$css['file']}{$css['inline']}</head><body {$bodyAttr}>{$contentWrapper}{$appendedScript}</body></html>";

			return NULL;
		}

		public function addJS($script, $append = TRUE)
		{
			if ( is_string($append) && CAST( $append, 'string upper-case' ) == "LAST" )
				$pos = "last";
			else
				$pos = ($append) ? 'append' : 'prepend';

			$this->_js[$pos][] = $script;
		}
		public function __get_js() { return $this->_js; }
		public function __set_js($value) { $this->addJS($value, TRUE); }
		public function __set_jsBegin($value) { $this->addJS($value, FALSE); }
		public function __set_jsEnd( $value ) { $this->addJS($value, "LAST"); }
		public function __set_jsLast($value) { $this->addJS($value, "LAST"); }

		public function addCSS($css) { $this->_css[] = $css; }
		public function __get_css() { return $this->_css; }
		public function __set_css($value) { $this->addCSS($value); }



		public function addFile($name, $type)
		{
			$type = explode(' ', strtolower($type));
			$path = in_array('external', $type) ? "{$name}" : "{$this->_baseRCPath}{$name}";
			$order = in_array( 'append', $type ) ? 'append' : 'prepend';

			switch (strtolower($type[0]))
			{
				case 'js':
					$this->_jsFiles[ $order ][] = $path;
					break;

				case 'css':
					$this->_cssFiles[] = $path;
					break;

				default:
					break;
			}
		}
		public function removeFile($name, $type)
		{
			$type = explode(' ', strtolower($type));
			$path = in_array('external', $type) ? "{$name}" : "{$this->_baseRCPath}{$name}";

			switch (strtolower($type[0]))
			{
				case 'js':
					foreach ( array( 'prepend', 'append' ) as $order )
					foreach ( $this->_jsFiles[ $order ] as $idx => $fPath )
					{
						if ( $fPath == $path )
							unset( $this->_jsFiles[ $order ][ $idx ] );
					}
					break;

				case 'css':
					foreach ( $this->_cssFiles as $idx => $fPath )
					{
						if ( $fPath == $path )
							unset( $this->_cssFiles[ $idx ] );
					}
					break;

				default:
					break;
			}
		}
		public function replaceFile($name, $replacement, $type)
		{
			$type = explode(' ', strtolower($type));
			$path = in_array('external', $type) ? "{$name}" : "{$this->_baseRCPath}{$name}";
			$rep  = in_array('external', $type) ? "{$replacement}" : "{$this->_baseRCPath}{$replacement}";

			switch (strtolower($type[0]))
			{
				case 'js':
					foreach ( array( 'prepend', 'append' ) as $order )
					foreach ( $this->_jsFiles[ $order ] as $idx => $fPath )
						if ( $fPath == $path ) $this->_jsFiles[ $order ][ $idx ] = $rep;
					break;

				case 'css':
					foreach ( $this->_cssFiles as $idx => $fPath )
						if ( $fPath == $path ) $this->_cssFiles[ $idx ] = $rep;
					break;

				default:
					break;
			}
		}
		public function __get_jsFiles() { return $this->_jsFiles; }
		public function __get_jsFilesAppended() { return $this->_jsFiles['append']; }
		public function __get_jsFilesPrepended() { return $this->_jsFiles['prepend']; }
		public function __get_cssFiles() { return $this->_cssFiles; }
		public function __set_jsFile($value) { $this->addFile($value, 'js'); }
		public function __set_cssFile($value) { $this->addFile($value, 'css'); }
		public function __set_file( $value ) {
			if ( $value instanceof stdClass ) $value = (array)$value;
			if ( !is_array($value) ) return;
			
			$this->addFile( @$value[ 'path' ], @$value[ 'type' ] );
		}
		public function __set_files( $value ) {
			if ( !is_array($value) ) return;
			foreach( $value as $fileDes ) $this->file = $fileDes;
		}


		public function __get_rcPath() { return $this->_baseRCPath; }
		public function __set_rcPath($value) { $this->_baseRCPath = (is_string($value)) ? $value : ''; }
		public function __set_header($value) { $this->_header[] = $value; }


		public function &__get_html() {
			if ( empty($this->_elm[ 'html' ]) )
				$this->_elm[ 'html' ] = [];

			return $this->_elm[ 'html' ];
		}
		public function &__get_body() {
			if ( empty($this->_elm[ 'body' ]) )
				$this->_elm[ 'body' ] = [];

			return $this->_elm[ 'body' ];
		}
		public function &__get_page() {
			if ( empty($this->_elm[ 'page' ]) )
				$this->_elm[ 'page' ] = [];

			return $this->_elm[ 'page' ];
		}
		public function &__get_meta() {
			return $this->_meta;
		}


		public function property($name, $value)
		{
			$name = strtolower($name);
			switch ( $name )
			{
				case 'title':
					$this->_header[] = "<title>{$value}</title>";
					break;
				case 'favicon':
					$this->_header[] = "<link rel='shortcut icon' href='{$this->_baseRCPath}{$value}' />";
					break;
				case 'charset':
					$this->_meta[] = [ "charset" => $value ];
//					$this->_meta[] = [ "http-equiv" => "Content-Type" "content" => "text/html; charset={$value}" ];
					break;
				case 'viewport':
					$this->_meta[] = [ "name" => "viewport", "content" => $value ];
					break;


				case 'lang':
					$this->_elm[ 'html' ][ 'lang' ] = "{$value}";
					break;

				case 'page':
				case 'body':
				case 'html':
					$this->_elm[ $name ][ 'class' ] = "{$value}";

				default:
					$this->_prop[ $name ] = $value;
					break;
			}
		}
	}

	class PBAJAXOutput extends PBModule
	{
		const STATUS_ALERT 	=  1;
		const STATUS_NORMAL	=  0;
		const STATUS_ERROR 	= -1;


		private $_noWrap = FALSE;
		public function __get_noWrap(){
			return $this->_noWrap;
		}
		public function __set_noWrap( $value ){
			$this->_noWrap = ($value === TRUE);
		}


		public function event($event) { $this->exec($event); }
		public function exec($param)
		{
			if ( $this->_noWrap )
			{
				PBHTTP::ResponseJSON( $param );
				return;
			}

			if ($param === NULL) return;



			$ajaxReturn = [];
			if (!is_array($param))
			{
				$ajaxReturn['status'] 	= self::STATUS_NORMAL;
				$ajaxReturn['msg']		= $param;
			}
			else
			{
				$ajaxReturn['status'] = (is_int(@$param['status'])) ? intval($param['status']) : self::STATUS_NORMAL;
				$ajaxReturn['msg'] = (@$param['msg']) ? $param['msg'] : '';
				unset($param['status']); unset($param['msg']);

				$ajaxReturn = array_merge($ajaxReturn, $param);
			}

			PBHTTP::ResponseJSON($ajaxReturn);
		}
	}

	class PBRawOutput extends PBModule
	{
		private $_mime = "text/plain";
		public function __get_mime() {
			return $this->_mime;
		}
		public function __set_mime( $value ) {
			$this->_mime = "{$value}";
		}

		public function prepare( $moduleRequest ) {
			$mime = trim("{$moduleRequest['mime']}");
			if ( !empty($mime) ) $this->_mime = $mime;
		}

		public function exec( $param ) {
			PBHTTP::ResponseContent( $param, $this->_mime );
		}
		public function shell( $param ) {
			PBStdIO::STDOUT( $param );
		}
	}
