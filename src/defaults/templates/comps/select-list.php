<?php
	$options = !is_array($options) ? [] : $options;
	$multiple = empty($multiple) ? '' : 'multiple';
?>
<select id="<?=@$tmplId?>" <?=$multiple?>>
	<?php
		foreach( @$options as $option ) {
			$selected = !empty($option['selected']) ? 'selected' : '';
			echo @"<option value='{$option['value']}' title='{$option['title']}' {$selected}>{$option['label']}</option>";
		}
	?>
</select>
