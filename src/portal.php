<?php
	require_once( dirname( __FILE__ ) . "/kernel/base.php" );

	using('kernel.sys');
	PBSysKernel::boot( $_SERVER['argc'], $_SERVER['argv']);
