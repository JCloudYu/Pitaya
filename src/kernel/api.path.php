<?php
	function path($referencingContext = '', $appendItem = '') {
		$tokens = explode('.', $referencingContext);
		$completePath = __PATH_RESOLVER::Resolve(array_shift($tokens));

		foreach( $tokens as $token)
			$completePath .= "/{$token}";

		$appendItem = trim($appendItem);
		return $completePath . (empty($appendItem) ? '' : "/{$appendItem}");
	}
	function using($referencingContext = '', $important = TRUE) {
		static $registeredInclusions = array();
		if ( func_num_args() == 1 && $referencingContext === TRUE ) return $registeredInclusions;

		$tokens = explode('.', $referencingContext);
		$tokens = array_reverse($tokens);

		if ( isset($registeredInclusions[($referencingContext)]) )
			return $registeredInclusions[($referencingContext)];

		if($tokens[0] == '*')
		{
			array_shift($tokens);
			$tokens = array_reverse($tokens);
			$completePath = __PATH_RESOLVER::Resolve(array_shift($tokens));


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
			$completePath = __PATH_RESOLVER::Resolve(array_shift($tokens));

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



	// region [ Deprecated ]
	function fileMove($srcPath, $destPath) {
		DEPRECATION_WARNING( "fileMove api is marked as deprecated and will be removed in the following versions!" );
		exec(CMD_MOVE." {$srcPath} {$destPath}"); 
	}
	function fileCopy($srcPath, $destPath) {
		DEPRECATION_WARNING( "fileCopy api is marked as deprecated and will be removed in the following versions!" );
		exec(CMD_COPY." {$srcPath} {$destPath}");
	}
	function available($referencingContext = '', $cache = TRUE) {
		DEPRECATION_WARNING( "avaiable api is marked as deprecated and will be removed in the following versions!" );
	
		static $registeredInclusions = array();

		if ( $cache && isset($registeredInclusions[ $referencingContext ]) )
			return $registeredInclusions[ $referencingContext ];



		$tokens = explode('.', $referencingContext);
		$completePath = __PATH_RESOLVER::Resolve(array_shift($tokens));
		if ( empty($completePath) )
			$result = FALSE;
		else
		{
			array_unshift( $tokens, $completePath );
			$completePath = implode( '/', $tokens ) . '.php';
			$result = file_exists($completePath);
		}

		if ( $cache )
			$registeredInclusions[ $referencingContext ] = $result;

		return $result;
	}
	function package($referencingContext = '', $output = true) {
		DEPRECATION_WARNING( "package api is marked as deprecated and will be removed in the following versions!" );
	
		$tokens = explode('.', $referencingContext);
		$tokens = array_reverse($tokens);

		if($tokens[0] == '*')
			throw(new Exception("Star notation is not allowed in function [package]"));
		else
		{
			$tokens = array_reverse($tokens);
			$completePath = __PATH_RESOLVER::Resolve(array_shift($tokens));

			foreach( $tokens as $token)
				$completePath .= "/{$token}";

			$completePath .= '.php';

			if ($output) return $completePath;
			elseif(file_exists($completePath)) include($completePath);
		}

		return '';
	}
	// endregion
