<?php
	error_reporting(E_ALL);
	require_once("inc/conf.inc.php");
	require_once("inc/comments.inc.php");
	require_once("inc/admin_interface.inc.php");
	require_once("inc/virtual_albums_conf.inc.php");
	require_once("inc/stats.inc.php");
	
	require_once("inc/show_virtual_album.inc.php");
	require_once("inc/show_albums_list.inc.php");
	require_once("inc/show_media_page.inc.php");
	
	\VirtualAlbumsConf\createDefaultUserIfNotExists();
	
	\Stats\addToStats();
	
	// GET parameters
	$valbum_id = isset($_GET['q']) ? $_GET['q'] : null;
	$media_id = isset($_GET['img']) ? $_GET['img'] : null;
	$next = isset($_GET['next']) ? true : false;

	// double-check password
	if (CONST_HTPASSWD_PATH_TO_CHECK_PASSWORD != '')
	{
		$is_ok = false;
		if (file_exists(CONST_HTPASSWD_PATH_TO_CHECK_PASSWORD) && (isset($_SERVER['HTTP_AUTHORIZATION']) || isset($_SERVER['PHP_AUTH_USER'])))
		{
			list($user_auth, $pass_auth) = isset($_SERVER['PHP_AUTH_USER']) ? array($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) : 
				explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
			foreach (file(CONST_HTPASSWD_PATH_TO_CHECK_PASSWORD) as $line)
			{
				if (rtrim($line) == "$user_auth:$pass_auth" || rtrim($line) == "$user_auth:".crypt($pass_auth)) {$is_ok = true;break;}
			}
		}
		if (!$is_ok) die();
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- Using https://github.com/phrounz/lilliputian-photo -->
<html>
<head>
	<meta content="text/html;charset=iso-8859-1" http-equiv="Content-Type" />
	<link href="style.css" rel="stylesheet" type="text/css" />
	<title><?php echo CONST_MAIN_TITLE; ?></title>
	<style type="text/css">

		.pic {
			margin: 1px;
			height: <?php echo CONST_HEIGHT_THUMBNAIL; ?>px;
		}
		.vid {
			height: <?php echo CONST_HEIGHT_THUMBNAIL; ?>px;
		}
		
		.alb_insight .pic {
			width: <?php echo CONST_WIDTH_ALBUM_INSIGHT; ?>px;
			height: <?php echo CONST_HEIGHT_ALBUM_INSIGHT; ?>px;
		}
		.alb_insight .vid {
			width: <?php echo CONST_WIDTH_ALBUM_INSIGHT; ?>px;
			height: <?php echo CONST_HEIGHT_ALBUM_INSIGHT; ?>px;
		}
		.alb_insight {
			width: <?php echo CONST_WIDTH_ALBUM_INSIGHT_BOX; ?>px;
			height: <?php echo CONST_HEIGHT_ALBUM_INSIGHT_BOX; ?>px;
		}
		
		.album_thumb_picture {
			width: <?php echo CONST_WIDTH_ALBUM_THUMB_PICTURE; ?>px;
			height: <?php echo CONST_HEIGHT_ALBUM_THUMB_PICTURE; ?>px;
		}
		
		<?php
			if (isset($_GET['rot']))
				echo '#the_media {-moz-transform:rotate(90deg);-webkit-transform:rotate(90deg);-o-transform:rotate(90deg);-ms-transform:rotate(90deg);transform:rotate(90deg);}';
			else if (isset($_GET['anti_rot']))
				echo '#the_media {-moz-transform:rotate(-90deg);-webkit-transform:rotate(-90deg);-o-transform:rotate(-90deg);-ms-transform:rotate(-90deg);transform:rotate(-90deg);}';
			else if (isset($_GET['inverse_rot']))
				echo '#the_media {-moz-transform:rotate(180deg);-webkit-transform:rotate(180deg);-o-transform:rotate(180deg);-ms-transform:rotate(180deg);transform:rotate(180deg);}';
		?>
		
	</style>
</head>
<body>

	<script type="text/javascript" src="ajax/ajax_get_day.js"></script>
	
	<div class='header'>
		<table>
			<tr>
				<td>
					<img src='logo.png' alt='(logo)' />
				</td>
				<td style="padding: 15px;">
					<h2>
	
<?php
	// POST parameters
	$new_comment = isset($_POST['new_comment']) ? strip_tags($_POST['new_comment']) : null;
	$comment_to_delete = isset($_POST['comment_to_delete']) ? strip_tags($_POST['comment_to_delete']) : null;
	
	// album and virtual album creations/deletions/modifications
	$str_pst = '';
	if ($_SERVER['REMOTE_USER'] == CONST_ADMIN_USER)
	{
		list($str_op, $res) = AdminInterface\doPostOperations();
		if ($str_op!='') $str_pst = "<div class='admin_success_failure_box'>\n<font color='".($res?'blue':'red')."'>\n$str_op ".($res?'succeeded.':'failed.')."<br /><a href='?'>Ok</a></font>\n</div>\n";// onclick='this.style.display=\"none\";'
	}

	// load list of virtual albums visible by this user
	$valbum_array = VirtualAlbumsConf\listVirtualAlbums();
	
	// display top page title
	echo "\t\t\t\t\t\t<a href='".MediaAccess\getListOfAlbumsUrl()."'>".CONST_MAIN_TITLE." </a>";
	if (isset($media_id) || isset($valbum_id))
	{
		if (!isset($valbum_id)) die("l.".__LINE__);
		echo " &gt; <a href='?q=$valbum_id'>".(isset($valbum_array[$valbum_id])?$valbum_array[$valbum_id]["title"]:$valbum_id).'</a>';
		if (isset($media_id))
			echo ' &gt; '.basename($media_id, ".".pathinfo($media_id, PATHINFO_EXTENSION));
	}
?>

					</h2>
					<p>
						Connected as: <i><?php echo $_SERVER['REMOTE_USER']; ?></i> 
						(<a href="<?php 
							$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
							echo $protocol.'logout:nothegoodpassword@'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/logout.php"; ?>">disconnect or change user</a>)
					</p>
					<div id='main'></div>
				</td>
			</tr>
		</table>
	</div>

	<div class='body_contents'>
<!-- ============================================================================== -->

<?php 
	echo $str_pst;

	//----------------------------
	// media page
	if (isset($valbum_id) && isset($media_id) && isset($valbum_array[$valbum_id]))
	{
		echo "<div class='media_page'>\n";
		$valbum = $valbum_array[$valbum_id];
		
		if ($next)
		{
			$media_id = \ShowVirtualAlbum\getNextMedia($valbum, $media_id);
		}
		
		if (isset($media_id))
		{
			if (isset($new_comment)) Comments\insertNewComment($valbum['album'], $media_id, $new_comment, $valbum['comments_permissions']);
			if (isset($comment_to_delete)) Comments\deleteComment($valbum['album'], $media_id, $comment_to_delete, $valbum['comments_permissions'], $valbum['user']);
			\ShowMediaPage\showMediaPage($valbum_id, $valbum, $media_id);
		}
		else
		{
			echo "Reached the end of the album.";
		}
		
		echo "</div>\n";
	}
	//----------------------------
	// album page
	else if (isset($valbum_id))
	{
		if (isset($valbum_array[$valbum_id]))
		{
			$valbum = $valbum_array[$valbum_id];
			
			foreach (ShowVirtualAlbum\getListOfDays($valbum) as $day => $nb_elements)
			{
				echo "\n<div class='new_day'>"
					."\t<h3>".preg_replace('/:/', '-', $day)."</h3>"
					."<div id='day-".$day."'>"
					."<span onClick=\"loadDay(".$valbum_id.",'".$day."')\">";

				ShowVirtualAlbum\show($valbum_id, $valbum, $day, true, true, null);
				
				echo "</span>\n"
					."</div>\n"
					."</div>\n";
			}
		}
		else
		{
			echo "<p>Not found</p>";
		}
	}
	//----------------------------
	// list of albums page
	else
	{
		ShowAlbumsList\showListOfAlbums($valbum_array);
		if ($_SERVER['REMOTE_USER'] == CONST_ADMIN_USER)
		{
			AdminInterface\showEdition($valbum_array);
			AdminInterface\showStats($valbum_array);
		}
	}
?>

<!-- ============================================================================== -->

	</div>

</body>
</html>
