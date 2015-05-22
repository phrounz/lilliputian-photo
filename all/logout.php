<?php
	$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	$redirect_url = $protocol.''.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
?>
<html>
<head>
	<meta http-equiv="refresh" content="0; URL=<?php echo $redirect_url;?>" />
</head>
<body>
</body>
</html>