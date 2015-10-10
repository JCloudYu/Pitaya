<?php
	/**
	 ** 1027.BadmintonLa - PBPageMod.php
	 ** Created by JCloudYu on 2015/08/18 09:31
	 **/
	using( 'kernel.core.PBModule' );

	class PBPageMod extends PBModule
	{
		public function prepare( $moduleRequest )
		{
			$this->ext->htmlClass	= "module {$this->class_lower}";
			$this->ext->htmlAttr	= "data-module-id='{$this->id_medium}'";
		}
	}



	// DEPRECATED: PBMod class will be removed in v1.4.0
	class_alias( 'PBPageMod', 'PBMod' );
