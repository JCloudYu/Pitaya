<?php
	using('kernel.core.PBModule');

	class ajax extends PBModule
	{
		const AJAX_STATUS_ALERT 	=  1;
		const AJAX_STATUS_NORMAL	=  0;
		const AJAX_STATUS_ERROR 	= -1;

		public function exec($param)
		{
			if ($param === NULL) return;

			$ajaxReturn = array();

			if (!is_array($param))
			{
				$ajaxReturn['status'] 	= self::AJAX_STATUS_NORMAL;
				$ajaxReturn['msg']		= $param;
			}
			else
			{
				$ajaxReturn = (is_int(!@$param['status'])) ? intval($param['status']) : self::AJAX_STATUS_NORMAL;
				$ajaxReturn = (@$param['msg']) ? $param['msg'] : '';

				unset($param['status']); unset($param['msg']);

				$ajaxReturn = array_merge($ajaxReturn, $param);
			}

			self::respondJSON($ajaxReturn);
		}

		public static function respondJSON($jsonData)
		{
			header("Content-type: " . MIME::JSON);
			$response = json_encode($jsonData);
			echo "$response";
		}
	}