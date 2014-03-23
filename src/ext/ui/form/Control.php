<?php
/**
 * VWIMSIS - Control.php
 * Created by JCloudYu on 2013/09/15 13:45
 */
	using('kernel.basis.PBObject');

	final class Control extends PBObject
	{
		public static function SelectOptions($options = array())
		{
			$content = array();
			foreach ($options as $opt)
			{
				$opt['selected'] = empty($opt['selected']) ? '' : 'selected';
				$content[] = @"<option value='{$opt['value']}' title='{$opt['title']}' {$opt['selected']} >{$opt['label']}</option>";
			}

			return implode("\n", $content);
		}
	}