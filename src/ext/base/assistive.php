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
	function D( $selector = NULL )
	{
		static $_singleton = NULL;

		if ($_singleton === NULL)
			$_singleton = new PBDataTree();

		if ( $selector !== NULL )
			return $_singleton[ $selector ];
		else
			return $_singleton;
	}

	function S() { return D(); } // DEPRECATED: S will be deprecated in v1.4.0
