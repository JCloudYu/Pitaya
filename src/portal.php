<?php
	require_once(dirname(__FILE__) . "/boot.php");

	using('kernel.sys');
	PBKernel::boot( $_SERVER['argv'] );
	