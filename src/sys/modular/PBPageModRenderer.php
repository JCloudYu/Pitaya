<?php
	/**
	 ** 1038.boat - PBPageModRenderer.php
	 ** Created by JCloudYu on 2015/10/11 02:13
	 **/
	using( 'kernel.basis.PBObject' );

	class PBPageModRenderer extends PBObject implements ArrayAccess
	{
		private $_modChain = array();

		private $_htmlCache = NULL;
		public function render( $force = FALSE )
		{
			if ( ($this->_htmlCache !== NULL) && empty($force) ) return $this->_htmlCache;

			$collectedHtml = "";
			foreach ( $this->_modChain as $moduleInfo )
			{
				$moduleName		= @"{$moduleInfo['module']}";
				$moduleRequest	= @$moduleInfo['request'];
				$moduleReuse	= !empty($moduleInfo['reuse']);
				$collectedHtml .= PBProcess::Execute( $moduleName, $moduleRequest, $moduleReuse );
			}

			return $collectedHtml;
		}

		public function __get_html() { return $this->render( FALSE ); }

		public function &offsetGet( $offset ){
			return PBProcess::Execute( $offset );
		}



		public function offsetSet( $offset, $value ){}
		public function offsetExists( $offset ){}
		public function offsetUnset( $offset ){}
	}
