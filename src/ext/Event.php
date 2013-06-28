<?php

	class Event extends PBObject
	{
		private static $_event_singleton = NULL;
		public static function Event() {

			if (self::$_event_singleton) return self::$_event_singleton;

			self::$_event_singleton = new Event();
			return self::$_event_singleton;
		}

		private function __construct() {


		}

		public function addEventListener() {

		}
	}