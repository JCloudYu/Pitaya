<?php
///////////////////////////////////////////////////////////////////////////////////////////////
////////////////////Object Oriented Pgramming Semantics and Syntaxe////////////////////////////

// The _OOPException is used to extensively maintain the exception of the _OOPCore Object
class ObjectCoreException extends Exception
{
	public function __construct($message, $code = 0, Exception $previous = null)
	{
		// In php version above 5.3, $previous is added for tracing back usage
		// See the official document http://www.php.net/manual/en/exception.construct.php
		if(strnatcmp(phpversion(),'5.3') >= 0)
			parent::__construct($message, $code, $previous);
		else
			parent::__construct($message, $code);
	}
}

	// The class provides the native getter and setter functions
	class ObjectCore
	{
		private static $_getPrefix = "__get_";
		private static $_setPrefix = "__set_";

		public function __get($name)
		{
			$getTarget = self::$_getPrefix.$name;
			$setTarget = self::$_setPrefix.$name;
			if(method_exists($this, $getTarget))
				return $this->{$getTarget}();
			else
			if(method_exists($this, $setTarget))
				throw(new ObjectCoreException("Getting value from an set-only property '{$name}'."));
			else
				throw(new ObjectCoreException("Getting value from an undefined property '{$name}'."));
		}

		public function __set($name, $value)
		{
			$getTarget = self::$_getPrefix.$name;
			$setTarget = self::$_setPrefix.$name;
			if(method_exists($this, $setTarget))
				return $this->{$setTarget}($value);
			else
				if(method_exists($this, $getTarget))
					throw(new ObjectCoreException("Getting value from an get-only property '{$name}'."));
				else
					throw(new ObjectCoreException("Getting value from an undefined property '{$name}'."));
		}

		public function __get_Class()
		{
			return get_class($this);
		}

		public function __get_ClassPrefix()
		{
			return get_class($this)."_";
		}
	}
///////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////