<?php
	function fileMove($srcPath, $destPath) { exec(CMD_MOVE." {$srcPath} {$destPath}"); }
	function fileCopy($srcPath, $destPath) { exec(CMD_COPY." {$srcPath} {$destPath}"); }


	// INFO: Path processing
	function ____________env_path($token = 'root')
	{
		static $delegate = '';
		if ( empty($delegate) )
		{
			$delegate = call_user_func(function()
			{
				$_cachedPath = array();

				if (empty($GLOBALS['extPath'])) $GLOBALS['extPath'] = array();
				foreach ($GLOBALS['extPath'] as $identifier => $path)
				{
					if ( !is_string($path) ) continue;
					$_cachedPath[$identifier] = $path;
				}


				$list = scandir(__ROOT__);
				foreach ($list as $file)
				{
					$absPath = __ROOT__ . "/{$file}";
					if (is_dir($absPath))
						$_cachedPath[strtolower($file)] = $absPath;
				}



				// INFO: service and share are reserved keywords
				$GLOBALS['servicePath'] = $_cachedPath['service'] = (empty($GLOBALS['servicePath'])) ? __WEB_ROOT__ . '/Services' : "{$GLOBALS['servicePath']}";
				$GLOBALS['sharePath']	= $_cachedPath['share']	  = (empty($GLOBALS['sharePath'])) ?   __WEB_ROOT__ . '/Share'	  : "{$GLOBALS['sharePath']}";
				$GLOBALS['dataPath']	= $_cachedPath['data']	  = (empty($GLOBALS['dataPath'])) ?	   __WEB_ROOT__ . '/Data'	  : "{$GLOBALS['dataPath']}";


				$_cachedPath['root']	= __WEB_ROOT__;
				$_cachedPath['working']	= ( empty($GLOBALS['STANDALONE_EXEC']) ) ? $_cachedPath['service'] : $GLOBALS['STANDALONE_EXEC']['cwd'];




				return function($package = 'root') use ($_cachedPath) {
					$pCache = array_key_exists("{$package}", $_cachedPath) ? $_cachedPath[$package] : '';
					return ($package == 'service' && defined('__WORKING_ROOT__')) ? __WORKING_ROOT__ : $pCache;
				};
			});
		}

		return $delegate($token);
	}
	____________env_path();

	function using($referencingContext = '', $important = true, $output = false)
	{

		static $registeredInclusions = array();

		if($output === TRUE)
			return preg_replace('/\n|\r/', '<br />', var_export($registeredInclusions, TRUE));

		$tokens = explode('.', $referencingContext);
		$tokens = array_reverse($tokens);

		if ( isset($registeredInclusions[($referencingContext)]) )
			return $registeredInclusions[($referencingContext)];

		if($tokens[0] == '*')
		{
			array_shift($tokens);
			$tokens = array_reverse($tokens);
			$completePath = ____________env_path(array_shift($tokens));


			foreach( $tokens as $token)
				$completePath .= "/{$token}";
			$completePath .= '/';

			$dirHandle = file_exists($completePath) ? opendir($completePath) : NULL;

			if($dirHandle === NULL && $important)
				throw(new Exception("Cannot locate package: {$completePath}"));

			if($dirHandle !== NULL)
			while(($entry = readdir($dirHandle)) !== FALSE)
			{
				if($entry == '.' || $entry == '..') continue;
				if(preg_match('/.*php$/', $entry) === 1)
				{
					$givenContainer = substr($referencingContext, 0, -2);
					$validEntry = substr($entry, 0, -4);

					if(isset($registeredInclusions[("$givenContainer.$validEntry")])) continue;

					$targetPath = "$completePath/$entry";

					$registeredInclusions[("$givenContainer.$validEntry")] = TRUE;

					if($important) require($targetPath);
					else include($targetPath);
				}
			}

			$registeredInclusions[($referencingContext)] = $dirHandle !== NULL;
		}
		else
		{
			$tokens = array_reverse($tokens);
			$completePath = ____________env_path(array_shift($tokens));

			foreach( $tokens as $token)
				$completePath .= "/{$token}";

			$completePath .= '.php';

			if(file_exists($completePath)) $registeredInclusions[($referencingContext)] = TRUE;
			else $registeredInclusions[($referencingContext)] = FALSE;

			if($important) require($completePath);
			else @include($completePath);
		}

		return $registeredInclusions[($referencingContext)];
	}

	function package($referencingContext = '', $output = true)
	{
		$tokens = explode('.', $referencingContext);
		$tokens = array_reverse($tokens);

		if($tokens[0] == '*')
			throw(new Exception("Star notation is not allowed in function [package]"));
		else
		{
			$tokens = array_reverse($tokens);
			$completePath = ____________env_path(array_shift($tokens));

			foreach( $tokens as $token)
				$completePath .= "/{$token}";

			$completePath .= '.php';

			if ($output) return $completePath;
			elseif(file_exists($completePath)) include($completePath);
		}

		return '';
	}
	package(''); // Force cache

	function available($referencingContext = '', $cache = TRUE)
	{
		static $registeredInclusions = array();

		if($cache && isset($registeredInclusions[($referencingContext)])) return $registeredInclusions[($referencingContext)];

		$tokens = explode('.', $referencingContext);

		$completePath = ____________env_path(array_shift($tokens));

		foreach( $tokens as $token)
			$completePath .= "/{$token}";
		$completePath .= '.php';

		$result = file_exists($completePath);

		if ($cache)
			$registeredInclusions[($referencingContext)] = $result;

		return $result;
	}
	available(''); // Force cache

	function path($referencingContext = '', $appendItem = '')
	{

		$tokens = explode('.', $referencingContext);
		$completePath = ____________env_path(array_shift($tokens));

		foreach( $tokens as $token)
			$completePath .= "/{$token}";

		$appendItem = trim($appendItem);

		return $completePath . (empty($appendItem) ? '' : "/{$appendItem}");
	}



	// INFO: Miscellaneous
	function s_define($name, $value, $sensitive = TRUE, $REPETITIVE_EXCEPTION = FALSE)
	{
		if (!defined($name))
			define($name, $value, $sensitive);
		else
		if ($REPETITIVE_EXCEPTION)
			throw(new Exception("Constant {$name} has been defined!"));

	}

	function caller()
	{
		$backtrace = debug_backtrace(0);
		return $backtrace[2]['class'];
	}
