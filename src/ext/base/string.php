<?php

	function CheckAccountSyntax($account) { return preg_match("/^[a-zA-Z0-9][a-zA-Z0-9._-]+$/i", $account) ? TRUE : FALSE; }
	function CheckPasswordSyntax($password) { $pass = trim($password); return (strlen($pass) >= 8 && $password === $pass); }
	function CheckEmailSyntax($email) { return (filter_var($email, FILTER_VALIDATE_EMAIL) !== FALSE); }

	function ParseVersion($verStr, $keepEmpty = FALSE)
	{
		if(!preg_match('/^\d+[.-]\d+(([.-]\d+[.-]\d+){0,1}|([.-]\d+){0,1})$/', $verStr)) return NULL;

		$ver = preg_split('/[.-]/', $verStr);
		return array(
			'major'		=> TO($ver[0], 'int'),
			'minor'		=> TO($ver[1], 'int'),
			'build'		=> ($ver[2] === NULL && $keepEmpty) ? NULL : TO($ver[2], 'int'),
			'revision'	=> ($ver[3] === NULL && $keepEmpty) ? NULL : TO($ver[3], 'int')
		);
	}

	function NormalizeVersion($verStr)
	{
		$ver = ParseVersion($verStr);
		return ($ver === NULL) ? NULL : "{$ver['major']}.{$ver['minor']}.{$ver['build']}-{$ver['revision']}";
	}

	function CompareVersion($verA, $verB, $minimalMajored = TRUE)
	{
		$normalize = (func_num_args() > 2) ? TRUE : FALSE;

		$verA = ParseVersion($verA, !$normalize);
		$verB = ParseVersion($verB, !$normalize);

		if (empty($verA) || empty($verB)) return FALSE;

		// major
		if ($verA['major'] > $verB['major']) return  1;
		if ($verA['major'] < $verB['major']) return -1;

		if ($verA['minor'] > $verB['minor']) return  1;
		if ($verA['minor'] < $verB['minor']) return -1;

		if ($verA['build'] !== NULL || $verB['build'] !== NULL)
		{
			if ($verA['build'] === NULL) return ($minimalMajored) ? -1 : 1;
			if ($verB['build'] === NULL) return ($minimalMajored) ?  1 : -1;

			if ($verA['build'] > $verB['build']) return  1;
			if ($verA['build'] < $verB['build']) return -1;


			if ($verA['revision'] !== NULL || $verB['revision'] !== NULL)
			{
				if ($verA['revision'] === NULL) return ($minimalMajored) ? -1 : 1;
				if ($verB['revision'] === NULL) return ($minimalMajored) ?  1 : -1;

				if ($verA['revision'] > $verB['revision']) return  1;
				if ($verA['revision'] < $verB['revision']) return -1;
			}
		}

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

	function ext_strtr($pattern, $replacements, $glue = FALSE, $mapper = NULL)
	{
		// INFO: Fail safe
		if ( !is_array($replacements) ) return "";
		$mapper	 = ( !is_callable($mapper) ) ? function($item){ return $item; } : $mapper;
		$pattern = "{$pattern}";


		$firstElm = reset($replacements);
		if ( !empty($firstElm) && !is_array($firstElm) )
			return strtr( $pattern, $mapper($replacements) );
		else
		{
			$sepMode	= ( $glue === FALSE || $glue === NULL );
			$collector	= ( $sepMode ) ? array() : "{$glue}";

			foreach ( $replacements as $key => $replace )
			{
				$result = strtr( $pattern, $mapper($replace) );

				if ( $sepMode )
					$collector .= $result;
				else
					$collector[$key] = $result;
			}

			return $result;
		}
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

	function LogStr($logMsg, $dateStr = TRUE, $timeSecond = TRUE, $timeDffStr = TRUE) {
		$fmt = array();
		if ( $dateStr ) $fmt[] = "Y/m/d";
		$fmt[] = ($timeSecond) ? "H:i:s" : "H:i";
		if ( $timeDffStr ) $fmt[] = "O";

		$fmt = implode(' ', $fmt);
		return "[" . date($fmt) . "] {$logMsg}";
	}
