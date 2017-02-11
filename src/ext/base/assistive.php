<?php
	using( 'sys.tool.PBVarVault' );
	using( 'sys.tool.PBDataTree' );
	
	

	// INFO: System-wide shared variable container (PBVariable)
	function V() {
		static $_singleton = NULL;
		if ($_singleton) return $_singleton;

		$_singleton = new PBVarVault();
		return $_singleton;
	}

	// INFO: System-wide and hierarchical data storage (PBDataTree)
	function& D( $selector = NULL, $value = NULL ) {
		static $_singleton = NULL;
		if ( $_singleton === NULL ) $_singleton = new PBDataTree();

		$numArgs = func_num_args();
		if ( $numArgs == 0 ) return $_singleton;

		return ( $numArgs > 1 ) ? $_singleton->set( $selector, $value ) : $_singleton->get( $selector );
	}





	
	// region [ Deprecated ]
	function S( $selector = NULL ) {
		DEPRECATION_WARNING( "Function S is marked as deprecated! Please refer to D instead!" ); 
		return D( $selector );
	}
	// endregion
