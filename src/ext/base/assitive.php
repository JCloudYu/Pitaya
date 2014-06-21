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

	// INFO: System-wide and hierachical data storage (PBStorage)
	function S()
	{
		static $_singleton = NULL;

		if ($_singleton === NULL)
			$_singleton = new PBStorage();

		$args = func_get_args();

		if (count($args) > 0)
			return $_singleton[$args[0]];
		else
			return $_singleton;
	}
