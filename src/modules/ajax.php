<?php
	using('kernel.core.PBModule');
	using('sys.net.PBHTTP');

	class ajax extends PBModule
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

			$ajaxReturn = array();

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
