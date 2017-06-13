<?php
	DEPRECATION_WARNING( "Package 'ext.base.math' is no longer available!" );
	
	// region [ Deprecated ]
	function iTrans($data, $encType) {
		DEPRECATION_WARNING( "Function iTrans is marked as deprecated and will be removed in the following versions!" );
		switch ($encType)
		{
			case 'urlencoded':
				$data = urldecode($data);
				break;
			case 'base64':
				$data = base64_decode($data);
				break;
			default:
				break;
		}

		return $data;
	}
	function Trans($data, $encType) {
	
		DEPRECATION_WARNING( "Function Trans is marked as deprecated and will be removed in the following versions!" );
		switch ($encType)
		{
			case 'urlencoded':
				$data = urlencode($data);
				break;
			case 'base64':
				$data = base64_encode($data);
				break;
			default:
				break;
		}

		return $data;
	}
	function Imprint($____path_of_the_package_file_to_be_imprinted = '') {
		DEPRECATION_WARNING( "Function Imprint is marked as deprecated and will be removed in the following versions!" );
	
		$____pre_cached_to_be_deleted_existing_variables = get_defined_vars();

		if (!is_string($____path_of_the_package_file_to_be_imprinted) ||
			empty($____path_of_the_package_file_to_be_imprinted))
			return NULL;

		require package($____path_of_the_package_file_to_be_imprinted, TRUE);
		$____path_of_the_package_file_to_be_imprinted = get_defined_vars();

		foreach ($____pre_cached_to_be_deleted_existing_variables as $varName => $varValue)
			unset($____path_of_the_package_file_to_be_imprinted[$varName]);

		return $____path_of_the_package_file_to_be_imprinted;
	}
	function Script($____path_of_the_script_to_be_executed, $____parameters_used_in_the_executed_script = array(), &$____script_defined_variables = NULL) {
	
		DEPRECATION_WARNING( "Function Script is marked as deprecated and will be removed in the following versions!" );
	
		$____pre_cached_to_be_deleted_existing_variables = get_defined_vars();

		if (!is_string($____path_of_the_script_to_be_executed) || empty($____path_of_the_script_to_be_executed))
			return '';


		ob_start();
		extract($____parameters_used_in_the_executed_script);
		require $____path_of_the_script_to_be_executed;
		$____output_buffer_generated_by_executed_script = ob_get_clean();
		$____variables_that_are_used_in_executed_script = get_defined_vars();



		foreach ($____pre_cached_to_be_deleted_existing_variables as $varName => $varValue)
			unset($____variables_that_are_used_in_executed_script[$varName]);

		foreach ($____parameters_used_in_the_executed_script as $varName => $varValue)
			unset($____variables_that_are_used_in_executed_script[$varName]);


		$____script_defined_variables = $____variables_that_are_used_in_executed_script;
		return $____output_buffer_generated_by_executed_script;
	}
	function ScriptOut($____path_of_the_script_to_be_executed, $____parameters_used_in_the_executed_script = array(), &$____script_defined_variables = NULL) {	
		DEPRECATION_WARNING( "Function ScriptOut is marked as deprecated and will be removed in the following versions!" );
	
		$____pre_cached_to_be_deleted_existing_variables = get_defined_vars();

		if (!is_string($____path_of_the_script_to_be_executed) || empty($____path_of_the_script_to_be_executed)) return;


		extract($____parameters_used_in_the_executed_script);
		require $____path_of_the_script_to_be_executed;
		$____variables_that_are_used_in_executed_script = get_defined_vars();



		foreach ($____pre_cached_to_be_deleted_existing_variables as $varName => $varValue)
			unset($____variables_that_are_used_in_executed_script[$varName]);

		foreach ($____parameters_used_in_the_executed_script as $varName => $varValue)
			unset($____variables_that_are_used_in_executed_script[$varName]);


		$____script_defined_variables = $____variables_that_are_used_in_executed_script;
	}
	// endregion
