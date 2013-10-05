<?php
/**
 * 0026.litchi - locale.php
 * Created by JCloudYu on 2013/10/05 13:41
 */

	using('kernel.basis.PBObject');

	final class PBLocale extends PBObject implements ArrayAccess
	{
		public $basePackage = '';
		public $locale = '';

		public static function Locale() { return new PBLocale(); }
		public static function basePackage($packagePath = NULL) {}
	}