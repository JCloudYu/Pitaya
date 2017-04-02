<?php
	$total = @$total ?: FALSE;
	$total = CAST( @$total, 'int strict positive', 0 );
	
	
	
	$size = CAST( @$size, 'int strict positive', 0 ) ?: 10;
	$current = CAST( @$current, 'int strict positive', 0 ) ?: 1;
	if ( $total > 0 ) {
		$current = ($current <= $total) ? $current : $total;
	}
	
	if ( $total !== FALSE && $total < 1 ) {
		$current = $total = $size = 1;
	}
	
	$generator	= is_callable(@$generator) ? $generator: function( $pageNum, $active = FALSE, $boundary = 0 ) {
		if ( $boundary > 0 ) {
			$label = '>';
		}
		else
		if ( $boundary < 0 ) {
			$label = '<';
		}
		else {
			$label = $pageNum;
		}
		
		return [
			'label' => $label,
			'url'	=> $active ? '#' : $pageNum
		];
	}
?>
<div id="<?=@$tmplId?>" class="pagination clearfix">
	<?php
		if ( $current != 1 ) {
			$itemInfo = $generator( $current-1, FALSE, -1 );
			echo "<div class='page-item prev'><a href='{$itemInfo['url']}'>{$itemInfo['label']}</a></div>";
		}
		
		$basePage = (($current / $size)|0) * $size;
		if ( $size > 1 ) {
			for ($i=1; $i <= $size; $i++ ) {
				$page = $basePage + $i;
				if ( $page > $total ) break;
				
				$itemInfo = $generator( $page, ($active = $page == $current), 0 );
				$active = $active ? 'active' : '';
				$url = ( empty($itemInfo['url']) ) ? '' : "href='{$itemInfo['url']}'";
				echo "<div class='page-item {$active}'><a {$url}>{$itemInfo['label']}</a></div>";
			}
		}
		else {
			$itemInfo = $generator( $current, TRUE, 0 );
			$url = ( empty($itemInfo['url']) ) ? '' : "href='{$itemInfo['url']}'";
			echo "<div class='page-item active'><a {$url}>{$itemInfo['label']}</a></div>";
		}
		
		if ( $total !== FALSE && $current != $total ) {
			$itemInfo = $generator( $current+1, FALSE, 1 );
			echo "<div class='page-item next'><a href='{$itemInfo['url']}'>{$itemInfo['label']}</a></div>";
		}
	?>
</div>