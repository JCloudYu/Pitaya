<?php
	require_once( dirname( __FILE__ ) . "/kernel/base.php" );

	using('kernel.sys');
	PBKernel::boot( $_SERVER['argv'] );
