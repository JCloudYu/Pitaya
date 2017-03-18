<?php
	/**
	 * @param String $targetAddr Target address
	 * @param bool $addHistory Whether the redirection will add history to browser
	 * @param int $delay Delay before adding history
	 */
	function redir( $targetAddr, $addHistory = FALSE, $delay = 0 ) {
		DEPRECATION_WARNING( "redir api is marked as deprecated! Please refer to PBRequest::redirect api!" );
	
		if ( headers_sent() || $addHistory || func_num_args() > 2 )
		{
			$targetAddr = json_encode( "{$targetAddr}" );
			echo "<script>(function(){setTimeout(function(){ window.location.href = {$targetAddr}; }, $delay );})();</script>"; ob_flush();
			Termination::NORMALLY();
		}



		PBRequest()->redirect( $targetAddr );
	}
