<?php

	class Event extends PBObject
	{
		private static $_eventListenters = array();

		public static function listen($eventName, $target, $func)
		{
			if (empty($eventName) || empty($func)) return FALSE;
			if (!method_exists($target, $func)) return FALSE;

			$eventName = strtolower($eventName);

			if (!array_key_exists($eventName, self::$_eventListenters)) self::$_eventListenters[$eventName] = array();

			if (!array_key_exists($func, self::$_eventListenters[$eventName]) ||
				!in_array($target, self::$_eventListenters[$eventName][$func], TRUE))
			{
				self::$_eventListenters[$eventName][$func][] = $target;
				return TRUE;
			}

			return FALSE;
		}

		public static function unlisten($eventName, $target, $func)
		{
			if (empty($eventName) || empty($func)) return FALSE;

			$eventName = strtolower($eventName);

			if (!in_array($eventName, self::$_eventListenters)) return FALSE;
			if (!in_array($func, self::$_eventListenters[$eventName])) return FALSE;
			if (($key = array_search($target, self::$_eventListenters[$eventName][$func], TRUE)) === FALSE) return FALSE;

			unset(self::$_eventListenters[$eventName][$func][$key]);
			return FALSE;
		}

		public static function trigger($eventName, $param = NULL)
		{
			if (empty($eventName)) return;

			foreach (self::$_eventListenters[$eventName] as $func => $eListeners)
			{
				foreach ($eListeners as $eventListener)
				{
					if (is_object($eventListener))
					{
						if ($param) $eventListener->$func($eventName, $param);
						else 		$eventListener->$func($eventName);
					}
					else
					{
						if (!class_exists($eventListener, FALSE)) return;

						if ($param) $eventListener::$func($eventName, $param);
						else 		$eventListener::$func($eventName);
					}
				}
			}
		}
	}

	class_alias('Event', 'PBEvent');