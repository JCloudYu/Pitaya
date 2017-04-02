<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8"/>
		<!--
		<meta http-equiv="Content-Security-Policy" content="default-src 'self' data: gap: 'unsafe-eval'; style-src 'self' 'unsafe-inline'; media-src *;">
		<meta name="format-detection" content="telephone=no">
		<meta name="msapplication-tap-highlight" content="no">
		<meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width">
		-->
		<?=@$meta?>
		<?=@$lib?>
		<title><?=@$title?></title>
	</head>
	<body <?=@$bodyAttr?>>
		<?=@$initScript?>
		<?=@$view?>
		<?=@$bootScript?>
	</body>
</html>
