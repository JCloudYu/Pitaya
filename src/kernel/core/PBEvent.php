<?php

	using('kernel.core.PBProcess');

	final class PBEvent extends PBObject
	{
		public static function Event($events = array())
		{
			static $_singleton = NULL;
			if ($_singleton === NULL) $_singleton = new PBEvent();


			$_singleton->registerEvents($events);
			return $_singleton;
		}



		private $_registeredEvents = NULL;

		public function __construct($events = array())
		{
			$this->_registeredEvents = array();
			$this->registerEvents($events);
		}

		public function registerEvents($events = array())
		{
			if (!is_array($events)) return FALSE;

			foreach ($events as $event)
				$this->register($event['event'], $event['listener']);

			return TRUE;
		}

		public function is_register($event, $listener)
		{
			if (!is_object($listener) && !is_string($listener))
			{
				throw(new Exception("Given listener is not acceptable!"));
				die();
			}

			if (!is_array($this->_registeredEvents[$event])) return FALSE;
			foreach ($this->_registeredEvents[$event] as $idx => $item)
				if ($listener === $item) return $idx;

			return FALSE;
		}

		public function register($event, $listener)
		{
			if ($this->is_register($event, $listener) !== FALSE) return FALSE;

			if (!is_array($this->_registeredEvents[$event]))
				$this->_registeredEvents[$event] = array();

			$this->_registeredEvents[$event][] = $listener;
		}

		public function deregister($event, $listener)
		{
			$index = $this->is_register($event, $listener);
			if ($index === FALSE) return FALSE;

			array_splice($this->_registeredEvents[$event], $index, 1);
			return TRUE;
		}

		public function fire($event, $param)
		{
			if (!is_array($this->_registeredEvents[$event])) return FALSE;

			$param['type'] = $event;
			foreach ($this->_registeredEvents[$event] as $listener)
			{
				$module =  (is_string($listener)) ? PBProcess::Process()->getModule($listener) : $listener;
				$ret = $module->event($param);
				if ($ret === FALSE) break;
			}

			return TRUE;
		}
	}
