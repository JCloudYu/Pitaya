<?php
/*
 * File: PBObject.php
 * Created by JCloudYu.
 * DateTime: 13/2/9 PM3:40
 */

///////////////////////////////////////////////////////////////////////////////////////////////
////////////////////Object Oriented Pgramming Semantics and Syntaxe////////////////////////////

	// The class provides the native getter and setter functions
	class PBObject
	{
		private static $_getPrefix = "__get_";
		private static $_setPrefix = "__set_";

		public function &__get($name) {

			$getTarget = self::$_getPrefix.$name;
			$setTarget = self::$_setPrefix.$name;
			if(method_exists($this, $getTarget))
				return $this->{$getTarget}();
			else
			if(method_exists($this, $setTarget))
				throw(new Exception("Getting value from an set-only property '{$name}'."));
			else
				throw(new Exception("Getting value from an undefined property '{$name}'."));
		}
		public function __set($name, $value) {

			$getTarget = self::$_getPrefix.$name;
			$setTarget = self::$_setPrefix.$name;
			if(method_exists($this, $setTarget))
				return $this->{$setTarget}($value);
			else
			if(method_exists($this, $getTarget))
				throw(new Exception("Setting value to an get-only property '{$name}'."));
			else
				throw(new Exception("Setting value to an undefined property '{$name}'."));
		}
		public function __get_Class() {

			return get_class($this);
		}
		public function __get_ClassPrefix() {

			return get_class($this)."_";
		}
		protected function __get_caller() {

			$tempBacktrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
			$tempBacktrace = array_reverse($tempBacktrace);

			$backtrace = array();
			$item = array_shift($tempBacktrace);
			while( $item !== NULL)
			{
				$backtrace[] = $item;
				if("{$item['function']}" == '__get' || "{$item['function']}" == '__set')
				{
					$item = array_shift($tempBacktrace);
					if(preg_match('/^__set_|^__get_/', $item['function']) !== 1)
					{
						$backtrace[] = $item;
					}
				}

				$item = array_shift($tempBacktrace);
			}
			$backtrace = array_reverse($backtrace);

			array_shift($backtrace);

			if(count($backtrace) <= 1)
				return NULL;
			else
				return $backtrace[1];
		}
		public function __get_id() {

			return 0;
		}
		public function friend() {

			$friendList = array();
			$args = func_get_args();
			foreach($args as $arg)
			{
				if(is_array($arg))
					foreach($arg as $innerArg) $friendList[] = $innerArg;
				else
					$friendList[] = $arg;
			}


			$tempBacktrace = debug_backtrace(0);
			$tempBacktrace = array_reverse($tempBacktrace);

			$backtrace = array();
			$item = array_shift($tempBacktrace);
			while( $item !== NULL)
			{
				$backtrace[] = $item;
				if("{$item['function']}" == '__get' || "{$item['function']}" == '__set')
				{
					$item = array_shift($tempBacktrace);
					if(preg_match('/^__set_|^__get_/', $item['function']) !== 1)
					{
						$backtrace[] = $item;
					}
				}

				$item = array_shift($tempBacktrace);
			}
			$backtrace = array_reverse($backtrace);

			array_shift($backtrace);

			if(count($backtrace) <= 1)
				return in_array('GLOBAL', $friendList);
			else
				return in_array($backtrace[1]['class'], $friendList);
		}
	}
///////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////