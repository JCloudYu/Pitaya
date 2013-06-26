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