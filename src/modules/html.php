<?php
/**
 * VWIMSIS - html.php
 * Created by JCloudYu on 2013/09/02 18:25
 */
	using('kernel.core.PBModule');

	class html extends PBModule
	{
		protected $_basePath = '';

		private $_js = array('prepend' => array(), 'append' => array());
		private $_css = array();
		private $_cssFiles = array();
		private $_jsFiles = array();

		private $_prop = array('title' => '');

		public function exec($param)
		{
			$js = array('prepend' => '', 'append' => '', 'file' => '');
			$css = array('inline' => '', 'file' => '');


			// INFO: Process JS
			$js['prepend'] = implode("\r\n", $this->_js['prepend']);
			$js['prepend'] = "<script type='text/javascript'>{$js['prepend']}</script>";

			$js['append']  = implode("\r\n", $this->_js['append']);
			$js['append'] = "<script type='text/javascript'>{$js['append']}</script>";

			foreach ($this->_jsFiles as $filePath)
				$js['file'] .= "<script type='text/javascript' src='{$filePath}'></script>\r\n";


			// INFO: Process CSS
			$css['inline'] = implode("\r\n", $this->_css);
			$css['inline'] = "<style type='text/css'>{$css['inline']}</style>";

			foreach ($this->_cssFiles as $filePath)
				$css['file'] .= "<link href='{$filePath}' rel='stylesheet' />\r\n";


			echo <<<HTML
				<HTML>
					<HEAD>
						<TITLE>{$this->_prop['title']}</TITLE>
						{$js['file']}
						{$js['prepend']}
						{$css['file']}
						{$css['inline']}
					</HEAD>
					<BODY>
						{$param}
						{$js['append']}
					</BODY>
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
			switch (strtolower($type))
			{
				case 'js':
					$this->_jsFiles[] = "{$this->_basePath}/js/{$name}.js";
					break;
				case 'css':
					$this->_jsFiles[] = "{$this->_basePath}/css/{$name}.css";
					break;
				default: break;
			}
		}
		public function __get_jsFiles() { return $this->_jsFiles; }
		public function __get_cssFiles() { return $this->_cssFiles; }
		public function __set_jsFiles($value) { $this->addFile($value, 'js'); }
		public function __set_cssFiles($value) { $this->addFile($value, 'css'); }


		public function property($name, $value) { $this->_prop[$name] = $value; }
	}