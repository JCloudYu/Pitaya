<?php
/**
 * VWIMSIS - html.php
 * Created by JCloudYu on 2013/09/02 18:25
 */
	using('kernel.core.PBModule');

	class html extends PBModule
	{
		private $_baseRCPath = '';

		private $_js = array('prepend' => array(), 'append' => array());
		private $_css = array();
		private $_cssFiles = array();
		private $_jsFiles = array();

		private $_header = array();

		private $_prop = array();

		public function exec($param)
		{
			$js = array('prepend' => '', 'append' => '', 'file' => '');
			$css = array('inline' => '', 'file' => '');
			$header = '';


			// INFO: Process JS
			$js['prepend'] = implode("\r\n", $this->_js['prepend']);
			$js['prepend'] = (!empty($js['prepend'])) ? "<script type='text/javascript'>{$js['prepend']}</script>" : '';

			$js['append']  = implode("\r\n", $this->_js['append']);
			$js['append'] = (!empty($js['append'])) ? "<script type='text/javascript'>{$js['append']}</script>" : '';

			foreach ($this->_jsFiles as $filePath)
				$js['file'] .= "<script type='text/javascript' src='{$filePath}'></script>\r\n";


			// INFO: Process CSS
			$css['inline'] = implode("\r\n", $this->_css);
			$css['inline'] = (!empty($css['inline'])) ? "<style type='text/css'>{$css['inline']}</style>" : '';

			foreach ($this->_cssFiles as $filePath)
				$css['file'] .= "<link href='{$filePath}' rel='stylesheet' />\r\n";

			$header = implode("\r\n", $this->_header);


			$bodyClass = empty($this->_prop['body']) ? '' : "class='{$this->_prop['body']}'";
			$bodyContent = (empty($this->_prop['page'])) ? 	"{$param}{$js['append']}" :
															"<div class='{$this->_prop['page']}'>{$param}{$js['append']}</div>";



			echo <<<HTML
<!DOCTYPE html>
<HTML>
	<head>
		{$header}

		{$js['file']}
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
			$pos = ($append) ? 'append' : 'prepend';
			$this->_js[$pos][] = $script;
		}
		public function __get_js() { return $this->_js; }
		public function __set_js($value) { $this->addJS($value, TRUE); }

		public function addCSS($css) { $this->_css[] = $css; }
		public function __get_css() { return $this->_css; }
		public function __set_css($value) { $this->addCSS($value); }


		public function addFile($name, $type)
		{
			$type = explode(' ', strtolower($type));
			$paddingPath = in_array('external', $type) ? '' : $this->_baseRCPath;

			switch (strtolower($type[0]))
			{
				case 'js':
					$this->_jsFiles[] = "{$paddingPath}{$name}";
					break;
				case 'css':
					$this->_cssFiles[] = "{$paddingPath}{$name}";
					break;
				default: break;
			}
		}
		public function __get_jsFiles() { return $this->_jsFiles; }
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
				case 'page':
				case 'body':
				default:
					$this->_prop[$name] = $value;
					break;
			}
		}
	}
