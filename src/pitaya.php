<?php
	require_once __DIR__ . '/kernel/_env/env.independent.php';
	function Pitaya($initArgs=[]) {
		$config = [
			'default-basis' => @$initArgs[ 'default-basis' ] ?: 'main',
			'attach-depth' => @$initArgs[ 'attach-depth' ] ?: 0,
		
		
			'packages' => is_array(@$initArgs['packages']) ? $initArgs['packages'] : [],
			'module-packages' => is_array(@$initArgs['module-packages']) ? $initArgs['packages'] : [],
			'leading-modules' => is_array(@$initArgs['leading-modules']) ? $initArgs['leading-modules'] : [],
			'tailing-modules' => is_array(@$initArgs['tailing-modules']) ? $initArgs['tailing-modules'] : [],
			'debug-mode' => !!@$initArgs[ 'debug-mode' ],
			'debug-console-width' => @$initArgs[ 'debug-console-width' ] ?: 350,
			'system-timezone' => @$initArgs[ 'system-timezone' ] ?: 'UTC',
			'thrown-exceptions' => FALSE,
			'log-exceptions' => TRUE,
			'log-dir' => @$initArgs[ 'log-dir' ] ?: sys_get_temp_dir()
		];
	
		PBStaticConf( 'pitaya-env', $config );
		require_once __DIR__ . '/boot.php';
		PBKernel::boot();
	}