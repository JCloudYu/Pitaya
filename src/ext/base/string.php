<?php

	function CheckAccountSyntax($account) { return preg_match("/^[a-zA-Z0-9][a-zA-Z0-9._-]+$/i", $account) ? TRUE : FALSE; }
	function CheckEmailSyntax($email) { return (filter_var($email, FILTER_VALIDATE_EMAIL) !== FALSE); }