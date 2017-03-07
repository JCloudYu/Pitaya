<?php
	using( 'ext.base.string' );

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

		public static function Paging($curPage, $totalPages, $options = array())
		{
			$pageWrapperClass = @"{$options['class']['wrapper']}";
			$pageItemClass	  = @"{$options['class']['item']}";

			$urlTpl		= CAST(@$options['page-url'], 'string purge-html');
			$rangeSize	= CAST(@$options['page-range'], 'int sctrict');
			if (empty($rangeSize)) $rangeSize = 5;


			$baseLocale = array(
				'first page' => '',
				'last page'  => ''
			);

			if (is_array(@$options['label']))
			{
				$baseLocale['first page'] = @"{$options['label']['first page']}";
				$baseLocale['last page']  = @"{$options['label']['last page']}";
			}

			// INFO: Don't display if there are no pages
			if ($totalPages < 1) return '';


			// INFO: Prepare current range's buttons
			$totalSec	  = ceil((float)$totalPages / (float)$rangeSize);
			$curSec		  = ceil((float)$curPage / (float)$rangeSize);
			$leadingPage  = (($curSec - 1) * $rangeSize) + 1;
			$taillingPage = min($totalPages, $curSec * $rangeSize);
			$displayRange = range($leadingPage, $taillingPage, 1);

			$pageTplVal	  = array();
			foreach ($displayRange as $pageNum)
			{
				$pageTplVal[] =
					array(
						':page'	 => $pageNum,
						':url' 	 => strtr($urlTpl, array(':page' => $pageNum)),
						':ext'	 => ($pageNum == $curPage) ? "class='{$pageItemClass} active'" : "class='{$pageItemClass}'"
					);
			}
			$pageItems = ext_strtr("<li :ext><a href=':url'>:page</a></li>", $pageTplVal);



			if ($curSec > 1)
			{
				$prevSecPage = ($curSec - 1) * $rangeSize;
				$item = strtr("<li class='{$pageItemClass}'><a href=':url'>&hellip; {$prevSecPage}</a></li>", array(':url' => strtr($urlTpl, array(':page' => $prevSecPage))));
				array_unshift($pageItems, $item);


				if ($prevSecPage > 1)
				{
					$display = empty($baseLocale['first page']) ? 1 : $baseLocale['first page'];
					$item = strtr("<li class='{$pageItemClass}'><a href=':url'>{$display}</a></li>", array(':url' => strtr($urlTpl, array(':page' => 1))));
					array_unshift($pageItems, $item);
				}
			}






			if ($totalSec > $curSec)
			{
				$nextSecPage = min($totalPages, $curSec * $rangeSize + 1);
				$item = strtr("<li class='{$pageItemClass}'><a href=':url'>{$nextSecPage} &hellip;</a></li>", array(':url' => strtr($urlTpl, array(':page' => $nextSecPage))));
				array_push($pageItems, $item);


				if ($nextSecPage != $totalPages)
				{
					$display = empty($baseLocale['last page']) ? $totalPages : $baseLocale['last page'];
					$item = strtr("<li class='{$pageItemClass}'><a href=':url'>{$display}</a></li>", array(':url' => strtr($urlTpl, array(':page' => $totalPages))));
					array_push($pageItems, $item);
				}
			}


			$pageItems = implode('', $pageItems);
			return "<ul class='{$pageWrapperClass}'>{$pageItems}</ul>";
		}

		public static function UploadBtn( $destPath, $text="Upload File", $identifier="", $multipleFiles = FALSE,  $dataName="files[]", $class="upload-btn" ){
			$baseStyle = "position:absolute;opacity:0;top:0;right:0;bottom:0;left:0;";
			$btnTpl = '<div data-comp-identifier=":identifier" class=":class" style="overflow:hidden;position:relative;">:btnText<input style=":style" data-url=":url" id=":identifier" type="file" name=":dataName" :multiple /></div>';

			return strtr( $btnTpl, array(
				':identifier'	=> preg_replace( '/\"/', '\"', $identifier ),
				':class'		=> preg_replace( '/\"/', '\"', $class ),
				':btnText'		=> htmlentities( $text ),
				':url'			=> htmlentities( $destPath ),
				':dataName'		=> preg_replace( '/\"/', '\"', $dataName ),
				':multiple'		=> ($multipleFiles === TRUE) ? 'multiple' : '',
				':style'		=> $baseStyle
			));
		}
	}
