<?php
	/**
	 * 1004.IMSLS - misc.php
	 * Created by JCloudYu on 2014/03/17 07:04
	 */

	function redir($targetAddr, $addHistory = FALSE)
	{
		if ( $addHistory )
		{
			echo "<script> window.location.href = '{$targetAddr}'; </script>"; ob_flush();
			Termination::NORMALLY();
		}



		if (headers_sent())
		{
			echo "<script> window.location.replace('{$targetAddr}'); </script>"; ob_flush();
		}
		else
		{
			header("Location: {$targetAddr}");
		}



		Termination::NORMALLY();
	}
