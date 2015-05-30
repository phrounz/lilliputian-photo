<?php
	error_reporting(E_ALL);
	require_once("inc/conf.inc.php");
	require_once("inc/media_infos.inc.php");
	require_once("inc/comments.inc.php");
	require_once("inc/admin_interface.inc.php");
	require_once("inc/cache.inc.php");	
	require_once("inc/virtual_albums_conf.inc.php");
	
	require_once("inc/show_virtual_album.inc.php");
	require_once("inc/show_albums_list.inc.php");
	require_once("inc/show_media_page.inc.php");
			
	\VirtualAlbumsConf\createDefaultUserIfNotExists();
	
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
	
	// check and possibly use cache version
	$generate_cache_file = null;
	$cancel_cache_generation = false;
	if (CONST_USE_CACHE)
	{
		$generate_cache_file = Cache\checkAndUseCache($valbum_id, $media_id);
		$cancel_cache_generation = false;
		if (isset($generate_cache_file)) ob_start();
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
		.alb_insight .three_dots {
			width: <?php echo CONST_WIDTH_ALBUM_INSIGHT*30/200; ?>px;
			height: <?php echo CONST_HEIGHT_ALBUM_INSIGHT; ?>px;
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
<h1>EN TRAVAUX, REVENIR DANS 2 HEURES, MERCI</h1>
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
		if ($str_op!='') $str_pst = "<div class='admin_box'>\n<font color='".($res?'blue':'red')."'>\n$str_op ".($res?'succeeded.':'failed.')."</font>\n</div>\n";
	}

	// load list of virtual albums for this user
	$valbum_array = VirtualAlbumsConf\listVirtualAlbums();
	
	// display top page title
	echo "\t\t\t\t\t\t<a href='".getListOfAlbumsUrl()."'>".CONST_MAIN_TITLE." </a>";
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
	//get album from valbum_id
	$album = isset($valbum_id) && isset($valbum_array[$valbum_id]) ? $valbum_array[$valbum_id]["album"] : null;
	
	$nb_elements = null;

	//----------------------------
	// media page
	if (isset($media_id))
	{
		$valbum = $valbum_array[$valbum_id];
			
		$i = 0;
		$next_one = null;
		$is_cut = false;
		$new_media_id = null;
		foreach (\ShowVirtualAlbum\getListOfDatePerMedias($album, $valbum['from_date'], $valbum['to_date'], false, $is_cut) as $media_file => $date)
		{
			if ($media_file == \MediaAccess\getAlbumDir($album)."/".$media_id)
			{
				if ($next)
				{
					$next_one = $i+1;
				}
				else
				{
					$new_media_id = basename($media_file);
					break;
				}
			}
			else if (isset($next_one))
			{
				$new_media_id = basename($media_file);
				break;
			}
			$i++;
		}
		if (!isset($new_media_id)) echo "Reached the end";
		else $media_id = $new_media_id;
		
		if (isset($new_comment)) Comments\insertNewComment($album, $media_id, $new_comment, $valbum['comments_permissions']);
		if (isset($comment_to_delete)) Comments\deleteComment($album, $media_id, $comment_to_delete, $valbum['comments_permissions'], $valbum['user']);
		echo "<div class='media_page'>\n";
		\ShowMediaPage\showMediaPage($valbum_id, $album, $media_id, $valbum['comments_permissions'], $valbum['user']);
		echo "</div>\n";
	}
	//----------------------------
	// album page
	else if (isset($valbum_id))
	{
		echo '<script type="text/javascript" src="ajax.js"></script>'."\n";
	
		if (isset($valbum_array[$valbum_id]))
		{
			$valbum = $valbum_array[$valbum_id];
			
			foreach (ShowVirtualAlbum\getListOfDays($album, $valbum['from_date'], $valbum['to_date']) as $day => $nb_elements)
			{
				echo "\n<div class='new_day'>"
					."\t<h3>".preg_replace('/:/', '-', $day)."</h3>"
					."<div id='day-".$day."'>"
					."<span onClick=\"loadDay(".$valbum_id.",'".$day."')\">";

				ShowVirtualAlbum\showVirtualAlbum(
					$valbum_id, 
					$album, 
					strcmp($day, $valbum['from_date']) < 0 ? $valbum['from_date'] : $day, 
					strcmp($day."ZZZZZZZZZZ", $valbum['to_date']) < 0 ? $day."ZZZZZZZZZZ" : $valbum['to_date'], 
					$valbum['comments_permissions'], 
					isset($_GET['no_insight']) ? false : true);
					
				echo "</span>\n"
					."</div>\n"
					."</div>\n";
			}
			
			echo '<script type="text/javascript">if (media_ids_to_process.length > 0){generateThumbnailAjax(media_ids_to_process[0], "'.$valbum_id.'");}</script>'."\n";
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
		}
	}
?>
		<div class='group'>
			<?php 
				if (isset($nb_elements)) echo "$nb_elements elements - ";
				echo (isset($generate_cache_file) && !$cancel_cache_generation?
					"Cache generated at: ".date('l jS \of F Y h:i:s A')."\n" : 
					"Cache not generated (".(isset($generate_cache_file)?'1':'0')."-".($cancel_cache_generation?'1':'0').")");
			?>
		</div>

<!-- ============================================================================== -->

	</div>

</body>
</html>

<?php
	Cache\finishCache($generate_cache_file, $cancel_cache_generation);
?>
