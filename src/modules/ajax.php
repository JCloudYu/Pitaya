<?php
	using('kernel.core.PBModule');

	class ajax extends PBModule
	{
		const STATUS_ALERT 	=  1;
		const STATUS_NORMAL	=  0;
		const STATUS_ERROR 	= -1;

		public function event($event)
		{
			$this->exec($event['data']);
			$event['propagation'] = FALSE;
			return $event;
		}

		public function exec($param)
		{
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

			$this->respondJSON($ajaxReturn);
		}

		public function respondJSON($jsonData)
		{
			header("Content-type: application/json");
			$response = json_encode($jsonData);
			echo "$response";
		}
	}
