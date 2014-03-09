<?php

	using('kernel.core.PBProcess');

	class PBChain extends PBObject
	{
		public static function Chain()
		{
			static $_singleton = NULL;

			if ($_singleton !== NULL)
				return $_singleton;


			$_singleton = new PBChain();

			$serviceChain = path('service') . '/chain.json';
			$chainData = (file_exists($serviceChain)) ? json_decode(file_get_contents($serviceChain), TRUE) : array();



			// INFO: [ {"event":eventName, "class":className}, {"event":eventName, "class":className} ... ]
			foreach ($chainData as $chainRecord)
				$_singleton->register($chainRecord['event'], $chainRecord['class']);

			return $_singleton;
		}






		private $_registeredEvents = NULL;

		private function __construct() { $this->_registeredEvents = array(); }

		public function register($event, $target, $method = 'onEvent')
		{
			if (!is_string($event)  || trim($event) === '')  return FALSE;
			if (!is_string($target) || trim($target) === '') return FALSE;
			if (!is_string($method) || trim($method) === '') return FALSE;


			if (!array_key_exists($event, $this->_registeredEvents))
				$this->_registeredEvents[$event] = array();

			if (@$this->_registeredEvents[$event][$target]['method'] === $method) return TRUE;


			$this->_registeredEvents[$event][$target] = array('target' => $target, 'method' => $method);
			return TRUE;
		}

		public function deregister($event, $target, $method = 'onEvent')
		{
			if (!is_string($event)  || trim($event) === '')  return FALSE;
			if (!is_string($target) || trim($target) === '') return FALSE;
			if (!is_string($method) || trim($method) === '') return FALSE;


			if (@$this->_registeredEvents[$event][$target]['method'] !== $method)
				return FALSE;


			unset($this->_registeredEvents[$event][$target]);
			return TRUE;
		}

		public function trigger($event, $param)
		{
			if (!array_key_exists($event, $this->_registeredEvents)) return FALSE;

			foreach ($this->_registeredEvents[$event] as $listener)
			{
				$class = $listener['target'];
				$method = $listener['method'];

				$module = PBProcess::Process()->getModule($class, TRUE);
				$ret = $module->{$method}(array('event' => $event, 'param' => $param));

				if ($ret === FALSE) break;
			}

			return TRUE;
		}
	}