<?php
	srand(time());
	$GLOBALS['randomCert'] = md5(rand());

	function encode($appendInfo = NULL, $referenceBase = NULL)
	{

		static $cacheServer = NULL;
		static $cacheRawRequest = NULL;
		static $baseEncoding = NULL;
		static $mergeDelegate = NULL;

		if(is_null($cacheServer)) $cacheServer = $_SERVER;
		if(is_null($cacheRawRequest)) $cacheRawRequest = preg_replace('/\/+/', '/', preg_replace('/^\/*|\/*$/', '', @$_SERVER['REQUEST_URI']));
		if(is_null($baseEncoding))
		{
			$REQUEST_TIME = PITAYA_BOOT_TIME;
			@$baseEncoding = "#|#{$cacheServer['HTTP_USER_AGENT']}#|#{$cacheServer['REMOTE_ADDR']}#|#{$cacheServer['REMOTE_PORT']}#|#".
							"{$cacheRawRequest}#|#{$REQUEST_TIME}#|#".uniqid("", TRUE)."#|#";
			$baseEncoding = hash('sha512', $baseEncoding);
		}

		if(is_null($mergeDelegate)) $mergeDelegate = "___{$GLOBALS['randomCert']}__sys_merge";

		if(!is_array($appendInfo))
			$appendInfo = array(rand(), $appendInfo);
		else
			$appendInfo[] = rand();

		$extended = '|';
		foreach($appendInfo as $key => $value) $extended.="$key|$value|";

		$extended = hash('sha512', $extended);

		$base = is_null($referenceBase) ? $baseEncoding : $referenceBase;

		return $mergeDelegate($base, $extended);
	}

	eval(
		<<<INVISIBLE
	function ___{$GLOBALS['randomCert']}__sys_merge(\$base, \$extended)
	{
		\$base = substr(\$base, 0, 64).strrev(substr(\$base, 64));
		\$extended = substr(\$extended, 0, 17).strrev(substr(\$extended, 17));

		\$result = '';

		for(\$i=0; \$i<128; \$i++)
			\$result.=\$base[\$i].\$extended[\$i];

		return \$result;
	}

	function ___{$GLOBALS['randomCert']}__sys_divide(\$hash)
	{
		\$base = '';
		\$extended = '';

		for(\$i=0;\$i<128;\$i++)
		{
			\$base .= \$hash[2 * \$i];
			\$extended .= \$hash[2 * \$i + 1];
		}

		\$base = substr(\$base, 0, 64).strrev(substr(\$base, 64));
		\$extended = substr(\$extended, 0, 17).strrev(substr(\$extended, 17));

		return array('base' => \$base, 'extended' => \$extended);
	}
INVISIBLE
	);

	function divide($sys_hash)
	{
		static $divideDelegate = NULL;
		if(is_null($divideDelegate)) $divideDelegate = "___{$GLOBALS['randomCert']}__sys_divide";

		return $divideDelegate($sys_hash);
	}

	// INFO: Force the encode function to cache the system parameters
	divide(encode(''));
