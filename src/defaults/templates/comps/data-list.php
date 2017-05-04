<?php
	/**
	 * Input variables
	 * @var $headers array
	 * @var $data array
	 * @var $emptyStr string
	 */

	$headers	= !is_array(@$headers) ? [] : $headers;
	$data		= !is_array(@$data) ? [] : $data;
	$emptyStr	= $emptyStr ?: '';
	
	
	
	$columns	= [];
?>
<div id="<?=@$tmplId?>" class="data-list">
	<div class="list-head"><div class='list-row clearfix'><?php
		foreach( $headers as $header ) {
			$header = ( is_array($header) ) ? object($header) : $header;
		
			$columns[] = $meta = object([
				'type'	=> (($value = @$header->type) == '') ? 'raw' : $value,
				'group'	=> (($value = @$header->group) == '') ? '' : $value
			]);
			
			echo @"<div class='list-col'>{$header->title}</div>";
		}
	?></div></div>
	<div class="list-body"><?php
		if ( count($data) <= 0 ) {
			echo @"<div class='list-row clearfix'><div class='list-col expand'>{$emptyStr}</div></div>";
		}
		else {
			foreach ($data as $dataSeq => $rowData) {
				echo "<div class='list-row clearfix'>";
				foreach ($columns as $idx => $meta) {
					$value = CAST( @$rowData[$idx], $meta->type );
					$group = empty($meta->group) ? '' : "data-list-group='{$meta->group}'";
					echo "<div class='list-col' {$group}>{$value}</div>";
				}
				echo "</div>";
			}
		}
	?></div>
</div>