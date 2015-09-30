<?php
/**
 * VWIMSIS - html.php
 * Created by JCloudYu on 2013/09/02 18:25
 */
	using('kernel.core.PBModule');

	class html extends PBModule
	{
		private $_baseRCPath = '';

		private $_js = array('prepend' => array(), 'append' => array(), 'last' => array());
		private $_css = array();

		private $_jsFiles = array( 'prepend' => array(), 'append' => array() );
		private $_cssFiles = array();

		private $_header = array();

		private $_prop = array();

		public function exec($param)
		{
			$js = array('prepend' => '', 'append' => '', 'file prepend' => '', 'file append' => '');
			$css = array('inline' => '', 'file' => '');
			$header = '';


			// INFO: Process JS
			$js['prepend'] = implode("\r\n", $this->_js['prepend']);
			$js['prepend'] = (!empty($js['prepend'])) ? "<script type='application/javascript'>{$js['prepend']}</script>" : '';

			$js['append']  = implode("\r\n", $this->_js['append']);
			$js['append']  = (!empty($js['append'])) ? "<script type='application/javascript'>{$js['append']}</script>" : '';

			$js['last']	   = implode("\r\n", $this->_js['last']);
			$js['last']    = (!empty($js['last'])) ? "<script type='application/javascript'>{$js['last']}</script>" : '';


			$this->_jsFiles[ 'prepend' ] = array_unique( $this->_jsFiles[ 'prepend' ] );
			foreach ($this->_jsFiles[ 'prepend' ] as $filePath)
				$js['file prepend'] .= "<script type='application/javascript' src='{$filePath}'></script>\r\n";

			$this->_jsFiles[ 'append' ] = array_unique( $this->_jsFiles[ 'append' ] );
			foreach ($this->_jsFiles[ 'prepend' ] as $filePath)
				$js['file append'] .= "<script type='application/javascript' src='{$filePath}'></script>\r\n";


			// INFO: Process CSS
			$css['inline'] = implode("\r\n", $this->_css);
			$css['inline'] = (!empty($css['inline'])) ? "<style type='text/css'>{$css['inline']}</style>" : '';

			$this->_cssFiles = array_unique( $this->_cssFiles );
			foreach ($this->_cssFiles as $filePath)
				$css['file'] .= "<link href='{$filePath}' type='text/css' rel='stylesheet' />\r\n";

			$header = implode("\r\n", $this->_header);




			// INFO: Prepare html contents
			$baseBody = "{$param}{$js['append']}{$js['file append']}{$js['last']}";
			$bodyContent = (empty($this->_prop['page'])) ? 	$baseBody : "<div class='{$this->_prop['page']}'>{$baseBody}</div>";


			$lang		= empty($this->_prop['lang']) ? '' : "lang='{$this->_prop['lang']}'";
			$bodyClass	= empty($this->_prop['body']) ? '' : "class='{$this->_prop['body']}'";
			$htmlClass	= empty($this->_prop['html']) ? '' : "class='{$this->_prop['html']}'";
			echo <<<HTML
<!DOCTYPE html>
<HTML {$lang} {$htmlClass}>
	<head>
		{$header}

		{$js['file prepend']}
		{$js['prepend']}
		{$css['file']}
		{$css['inline']}
	</head>
	<body {$bodyClass}>{$bodyContent}</body>
</HTML>
HTML;

			return NULL;
		}

		public function addJS($script, $append = TRUE)
		{
			if ( is_string($append) && TO($append, 'string upper') == "LAST" )
				$pos = "last";
			else
				$pos = ($append) ? 'append' : 'prepend';

			$this->_js[$pos][] = $script;
		}
		public function __get_js() { return $this->_js; }
		public function __set_js($value) { $this->addJS($value, TRUE); }
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


		public function __get_rcPath() { return $this->_baseRCPath; }
		public function __set_rcPath($value) { $this->_baseRCPath = (is_string($value)) ? $value : ''; }

		public function __set_header($value) {$this->_header[] = $value;}

		public function property($name, $value)
		{
			switch (strtolower($name))
			{
				case 'title':
					$this->_header[] = "<title>{$value}</title>";
					break;
				case 'favicon':
					$this->_header[] = "<link rel='shortcut icon' href='{$this->_baseRCPath}{$value}' />";
					break;
				case 'charset':
//					$this->_header[] = "<meta http-equiv='Content-Type' content='text/html; charset={$value}'/>";
					$this->_header[] = "<meta charset='{$value}'>";
					break;
				case 'viewport':
					$this->_header[] = "<meta name='viewport' content='{$value}' />";
					break;
				case 'lang':
				case 'page':
				case 'body':
				case 'html':
				default:
					$this->_prop[$name] = $value;
					break;
			}
		}
	}
