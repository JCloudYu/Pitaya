<?php
	require_once( dirname( __FILE__ ) . "/pitaya.php" );

	using('kernel.sys');
	PBKernel::boot( $_SERVER['argv'] );
	