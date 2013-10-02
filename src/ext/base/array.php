<?php

	function ary_merge(&$ary1, $ary2)
	{
		$args = func_get_args();

		// INFO: Pop out the first two arguments
		array_shift($args);

		foreach ($args as $arg)
		{
			if (!is_array($arg)) continue;
			foreach ($arg as $key => $value) $ary1[$key] = $value;
		}

		return $ary1;
	}

	function ary_merge_recursive(&$ary1, $ary2)
	{
		$args = func_get_args();

		// INFO: Pop out the first two arguments
		array_shift($args);

		foreach ($args as $arg)
		{
			if (!is_array($arg)) continue;
			foreach ($arg as $key => $value)
			{
				if (is_array($value))
				{
					if (!is_array($ary1[$key])) $ary1[$key] = array();

					ary_merge_recursive($ary1[$key], $value);
				}
				else
					$ary1[$key] = $value;
			}
		}

		return $ary1;
	}

	function ary_set_recursiv($ary1, $ary2, $forceKeep = FALSE)
	{
		$buff = NULL;
		if (!is_array($ary1)) {
			if ($forceKeep) {
				if (!is_array($ary2))
					return array($ary1, $ary2);
				else {
					$ary2[] = $ary1;

					return $ary2;
				}
			}
			else
				return $ary2;
		}
		else {
			if (!is_array($ary2)) {
				if ($forceKeep) {
					$ary1[] = $ary2;

					return $ary1;
				}
				else
					return $ary2;
			}
			else {
				foreach ($ary2 as $key => $value) {
					if (!array_key_exists($key, $ary1))
						$ary1[$key] = $value;
					else
						$ary1[$key] = array_merge($ary1[$key], $ary2[$key]);
				}

				return $ary1;
			}
		}
	}


	define('IN_ARY_MODE_AND', 			1 << 0, TRUE);
	define('IN_ARY_MODE_OR', 			0 << 0, TRUE);

	define('IN_ARY_MODE_STRICT', 		1 << 1, TRUE);
	define('IN_ARY_MODE_NONE_STRICT', 	0 << 1, TRUE);
	function in_ary($needle, $candidates, $mode = IN_ARY_MODE_OR)
	{
		if (!is_array($needle)) $needle = array($needle);


		if (!is_int($mode)) $mode = 0;
		$andMode 	= $mode & IN_ARY_MODE_OR;
		$strictMode = $mode & IN_ARY_MODE_STRICT;

		$state = ($andMode === TRUE) ? TRUE : FALSE;
		foreach ($needle as $content)
		{
			if ($andMode)
				$state = $state && in_array($content, $candidates, $strictMode);
			else
				$state = $state || in_array($content, $candidates, $strictMode);
		}

		return $state;
	}