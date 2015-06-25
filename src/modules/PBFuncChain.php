<?php
/**
 * 0002.pitaya.workbench - PBFuncChain.php
 * Created by JCloudYu on 2015/05/12 01:44
 */
	using('kernel.core.PBModule');

	class PBFuncChain extends PBModule
	{
		private $_preprocessChain	= array();
		private $_postprocessChain	= array();

		public function prepare( $moduleRequest )		{ $this->preprocess( $moduleRequest ); }
		public function prepareShell( $moduleRequest )	{ $this->preprocess( $moduleRequest ); }
		public function prepareEvent( $moduleRequest )	{ $this->preprocess( $moduleRequest ); }
		protected function preprocess( $moduleRequest )
		{
			foreach ( $this->_preprocessChain as $call )
			{
				if ( !is_callable( $call ) ) continue;
				$call( $moduleRequest );
			}
		}


		public function exec( $param )	{ $this->postprocess( $param ); }
		public function shell( $param ) { $this->postprocess( $param ); }
		public function event( $param ) { $this->postprocess( $param ); }
		protected function postprocess( $param )
		{
			foreach ( $this->_postprocessChain as $call )
			{
				if ( !is_callable( $call ) ) continue;
				$param = $call( $param );
			}

			return $param;
		}


		public function __set_pre( $call )	{ $this->add( $call, "preprocess" ); }
		public function __set_post( $call ) { $this->add( $call, "postprocess" ); }

		public function add( $callable, $position = "preprocess" )
		{
			if ( strtolower( $position ) == "preprocess" )
				$this->_preprocessChain[] = $callable;
			else
				$this->_postprocessChain[] = $callable;
		}
	}
