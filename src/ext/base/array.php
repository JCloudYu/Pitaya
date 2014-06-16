<?php

	using('ext.base.misc');

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
		$andMode 	= $mode & IN_ARY_MODE_AND;
		$strictMode = $mode & IN_ARY_MODE_STRICT;

		$state = (empty($andMode)) ? FALSE : TRUE;
		foreach ($needle as $content)
		{
			if ($andMode)
				$state = $state && in_array($content, $candidates, !empty($strictMode));
			else
				$state = $state || in_array($content, $candidates, !empty($strictMode));
		}

		return $state;
	}

	/**
	 * The replacement of array_fill function which seldom uses its startIndex parameter and has warning when $size is smaller or equal to zero.
	 * In this version, $sizes that are smaller or equal to zero will only result in empty array, and the $startIndex parameter now has its default value 0
	 *
	 * @param int $size the size of the generated array
	 * @param mixed $element the element that is used to fill the array
	 * @param int $startIndex the start index of the elements
	 *
	 * @return array the generated array
	 */
	function ary_fill($size, $element, $startIndex = 0)
	{
		$rtAry = array();

		for($i = 0; $i <$size; $i++, $startIndex++)
			$rtAry[$startIndex] = $element;

		return $rtAry;
	}

	function ary_intersect() { return call_user_func_array('array_intersect', func_get_args()); }

	function ary_union() { return array_unique(call_user_func_array('array_merge', func_get_args())); }

	function ary_exclude($src, $ref)
	{
		if (!is_array($src)) return array();

		$args = func_get_args();
		array_shift($args);


		$left = $src;
		foreach ($args as $param)
		{
			if (!is_array($param)) continue;


			$stayed = array();
			foreach ($left as $src_content)
			{
				if (in_array($src_content, $ref)) continue;
				$stayed[] = $src_content;
			}
			$left = $stayed;
		}

		return $left;
	}

	function ary_flag($ary, $flag, $matchCase = TRUE, $compareMode = IN_ARY_MODE_OR)
	{
		if (!is_array($ary)) $ary = array();
		if (!is_array($flag)) $flag = array($flag);

		if (!$matchCase)
			foreach ($flag as $id => $content) $flag[$id] = trim(strtolower("{$content}"));


		$candidates = array();
		foreach ($ary as $idx => $item)
		{
			if (!preg_match('/^\d+$/', "{$idx}")) continue;
			$candidates[] = trim(((!$matchCase) ? strtolower("{$item}") : "{$item}"));
		}

		return in_ary($flag, $candidates, $compareMode);
	}

	function ary_data($ary, $idx, $type = 'raw', $default = NULL)
	{
		if (!is_array($ary)) $ary = array();
		if (preg_match('/^\d+$/', "{$idx}") || !isset($ary[$idx])) return $default;

		return TO($ary[$idx], $type);
	}

	function ary_pick($ary, $indices)
	{
		if (empty($indices) || (!is_array($indices) && !is_string($indices)))
			return array();

		$indices = (is_string($indices)) ? explode(',', $indices) : $indices;
		$collected = array();
		foreach ($indices as $idx) { $collected[] = $ary[$idx]; }

		return $collected;
	}
