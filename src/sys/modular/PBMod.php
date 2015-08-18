<?php
	/**
	 ** 1027.BadmintonLa - PBMod.php
	 ** Created by JCloudYu on 2015/08/18 09:31
	 **/
	using( 'kernel.core.PBModule' );

	final class PBMod extends PBModule
	{
		public function prepare( $moduleRequest )
		{
			$this->ext->htmlClass	= "{$this->class_lower}";
			$this->ext->htmlAttr	= "data-viewId='{$this->id_medium}'";
		}
	}
