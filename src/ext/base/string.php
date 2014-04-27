<?php

	function CheckAccountSyntax($account) { return preg_match("/^[a-zA-Z0-9][a-zA-Z0-9._-]+$/i", $account) ? TRUE : FALSE; }
	function CheckPasswordSyntax($password) { $pass = trim($password); return (strlen($pass) >= 8 && $password === $pass); }
	function CheckEmailSyntax($email) { return (filter_var($email, FILTER_VALIDATE_EMAIL) !== FALSE); }

	function ParseVersion($verStr)
	{
		if(!preg_match('/^\d+.\d+((.\d+[.-]\d+){0,1}|(.\d+){0,1})$/', $verStr)) return NULL;

		$ver = preg_split('/[.-]/', $verStr);
		return array(
			'major'		=> TO($ver[0], 'int'),
			'minor'		=> TO($ver[1], 'int'),
			'build'		=> TO($ver[2], 'int'),
			'revision'	=> TO($ver[3], 'int')
		);
	}

	function CompareVersion($verA, $verB)
	{
		$verA = ParseVersion($verA);
		$verB = ParseVersion($verB);

		if (empty($verA) || empty($verB)) return FALSE;

		// major
		if ($verA['major'] > $verB['major']) return  1;
		if ($verA['major'] < $verB['major']) return -1;

		if ($verA['minor'] > $verB['minor']) return  1;
		if ($verA['minor'] < $verB['minor']) return -1;

		if ($verA['build'] > $verB['build']) return  1;
		if ($verA['build'] < $verB['build']) return -1;

		if ($verA['revision'] > $verB['revision']) return  1;
		if ($verA['revision'] < $verB['revision']) return -1;

		return 0;
	}

	function TimeElapsedQuantum($now, $target) {

		$nowBuff = new DateTime();
		$nowBuff->setTimestamp($now);
		$targetBuff = new DateTime();
		$targetBuff->setTimestamp($target);

		$interval = $nowBuff->diff($targetBuff);

		if ($interval->y)
		{
			$unit = ($interval->y > 1) ? 'years' : 'year';
			return "{$interval->y} {$unit} before";
		}

		if ($interval->m)
		{
			$unit = ($interval->m > 1) ? 'months' : 'month';
			return "{$interval->m} {$unit} before";
		}

		if ($interval->d)
		{
			$unit = ($interval->d > 1) ? 'days' : 'day';
			return "{$interval->d} {$unit} before";
		}

		if ($interval->h)
		{
			$unit = ($interval->h > 1) ? 'hours' : 'hour';
			return "{$interval->h} {$unit} before";
		}

		if ($interval->i)
		{
			$unit = ($interval->i > 1) ? 'minutes' : 'minute';
			return "{$interval->i} {$unit} before";
		}

		$unit = ($interval->s > 1) ? 'seconds' : 'second';
		return "{$interval->s} {$unit} before";
	}

	function ext_strtr($pattern, $replacements)
	{
		if (!is_array($replacements)) return $pattern;

		$firstElm = reset($replacements);
		if (!is_array($firstElm))
			return strtr($pattern, $replacements);
		else
		{
			$result = array();
			foreach ($replacements as $key => $replace)
				$result[$key] = strtr($pattern, $replace);

			return $result;
		}
	}

	function repeat_strtr($pattern, $replacements, $glue = '')
	{
		if (!is_array($replacements)) return $pattern;

		$result = array();
		foreach ($replacements as $replace)
			$result[] = strtr($pattern, $replace);

		return implode($glue, $result);
	}

	function ext_trim($instance)
	{
		if (!is_array($instance))
			return trim($instance);
		else
		{
			$result = array();
			foreach ($instance as $key => $str)
				$result[$key] = trim($str);

			return $result;
		}
	}