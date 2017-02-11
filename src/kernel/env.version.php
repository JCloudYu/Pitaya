<?php
	define("PITAYA_VERSION_MAJOR",	2);
	define("PITAYA_VERSION_MINOR",	0);
	define("PITAYA_VERSION_BUILD",	0);
	define("PITAYA_VERSION_PATCH",	1);
	
	define("PITAYA_VERSION_ID",		PITAYA_VERSION_MAJOR * 10000 + PITAYA_VERSION_MINOR * 100 + PITAYA_VERSION_BUILD );
	define('PITAYA_VERSION_SHORT',	PITAYA_VERSION_MAJOR . '.' . PITAYA_VERSION_MINOR);
	define('PITAYA_VERSION',		PITAYA_VERSION_MAJOR . '.' . PITAYA_VERSION_MINOR . '.' . PITAYA_VERSION_BUILD);
	define('PITAYA_VERSION_DETAIL', PITAYA_VERSION . '-' . PITAYA_VERSION_PATCH);
