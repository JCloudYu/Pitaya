<?php
	/**
	 ** 1027.BadmintonLa - PBPageMod.php
	 ** Created by JCloudYu on 2015/08/18 09:31
	 **/
	using( 'kernel.core.PBModule' );

	class PBPageMod extends PBModule
	{
		public static function ModCtrl(){
			static $_singleton = NULL;
			if ( $_singleton === NULL )
				$_singleton = new PBPageMod();
			
			return $_singleton;
		}
	
		public function prepare( $moduleRequest = NULL )
		{
			$this->ext->htmlClass	= "module {$this->class_lower}";
			$this->ext->htmlAttr	= "data-module-id='{$this->id_medium}'";
		}
		
		public function __invoke( $args )
		{
			$args = func_get_args();
			
			// Global Module Logic
			if ( $this->class === PBPageMod::class )
			{
				$moduleName = @array_shift( $args );
				$module = PBProcess::Module( "{$moduleName}", FALSE );
				return @call_user_func_array( $module, $args );
			}
			
			
			
			@call_user_func_array( [ $this, 'prepare' ], $args );
			return $this->exec( NULL );
		}
	}



	// DEPRECATED: PBMod class will be removed in v2.0.0
	class_alias( 'PBPageMod', 'PBMod' );
