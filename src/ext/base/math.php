<?php

	s_define('DEG2RAD',	0.017453292519943, 					TRUE);
	s_define('RAD2DEG',	57.29577951412932, 					TRUE);
	s_define('PI',		3.141592653589793238462643383279, 	TRUE);

	function EXPR_NUMERIC($val) { return EXPR_INT($val) || EXPR_FLOAT_DOT($val) || EXPR_FLOAT_SCIENCE($val); }


	function EXPR_INT($val) 			{ return (preg_match('/^[-+]{0,1}\d+$/', "{$val}") > 0); }
	function EXPR_UINT($val) 			{ return (preg_match('/^\d+$/', "{$val}") > 0); }
	function EXPR_FLOAT($val)			{ return EXPR_FLOAT_DOT($val) || EXPR_FLOAT_SCIENCE($val); }
	function EXPR_FLOAT_DOT($val)		{ return (preg_match('/^[-+]{0,1}((\d*\.\d+)|(\d+\.\d*))$/', "{$val}") > 0); }
	function EXPR_FLOAT_SCIENCE($val)
	{
		return (preg_match('/^[-+]{0,1}((\d*\.\d+)|(\d+\.\d*))[eE][-+]{0,1}\d+$/', "{$val}") > 0);

		/*
		// INFO: Alternative implementation of science expression syntax detection
		$parts = explode('e', strtolower("{$val}"));
		if (count($parts) != 2) return FALSE;

		return FLOAT_DOT_EXPR($parts[0]) && INT_EXPR($parts[1]);
		*/
	}

