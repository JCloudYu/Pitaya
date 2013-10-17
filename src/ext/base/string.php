<?php

	function CheckAccountSyntax($account) { return preg_match("/^[a-zA-Z0-9][a-zA-Z0-9._-]+$/i", $account) ? TRUE : FALSE; }
	function CheckPasswordSyntax($password) { $pass = trim($password); return (strlen($pass) >= 8 && $password === $pass); }
	function CheckEmailSyntax($email) { return (filter_var($email, FILTER_VALIDATE_EMAIL) !== FALSE); }

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