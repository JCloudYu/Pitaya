<?php
/**
 * 1004.IMSLS - assitive.php
 * Created by JCloudYu on 2014/06/22 00:38
 */

	// INFO: System-wide shared variable container (PBVariable)
	function V()
	{
		static $_singleton = NULL;
		if ($_singleton) return $_singleton;

		$_singleton = new PBVarVault();
		return $_singleton;
	}

	// INFO: System-wide and hierachical data storage (PBDataTree)
	function& D( $selector = NULL, $value = NULL )
	{
		static $_singleton = NULL;
		if ( $_singleton === NULL ) $_singleton = new PBDataTree();


		$numArgs = func_num_args();
		if ( $numArgs == 0 ) return $_singleton;


		return ( $numArgs > 1 ) ? $_singleton->set( $selector, $value ) : $_singleton->get( $selector );
	}

	function S( $selector = NULL ) { return D( $selector ); } // DEPRECATED: S will be deprecated in v1.4.0
