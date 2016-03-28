<?php
	/**
	 * 1004.IMSLS - misc.php
	 * Created by JCloudYu on 2014/03/17 07:04
	 */

	function redir($targetAddr, $addHistory = FALSE) {
		$targetAddr = "{$targetAddr}";

		if ( $addHistory )
		{
			$targetAddr = json_encode( $targetAddr );
			echo "<script>(function(){setTimeout(function(){ window.location.href = {$targetAddr}; }, $delay );})();</script>"; ob_flush();
			Termination::NORMALLY();
		}



		if ( !headers_sent() && $delay == 0 )
			header("Location: {$targetAddr}");
		else
		{
			$targetAddr = json_encode( $targetAddr );
			echo "<script>(function(){setTimeout(function(){ window.location.replace('{$targetAddr}'); }, $delay );})();</script>"; ob_flush();
		}



		Termination::NORMALLY();
	}
