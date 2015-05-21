<?php
	error_reporting(E_ALL);
	require_once("inc/conf.inc.php");
	require_once("inc/virtual_albums_conf.inc.php");
	require_once("inc/media_infos.inc.php");
	require_once("inc/comments.inc.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<meta content="text/html;charset=iso-8859-1" http-equiv="Content-Type" />
	<link href="style.css" rel="stylesheet" type="text/css" />
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
		
		<?php
			if (isset($_GET['rot']))
				echo 'video{-moz-transform:rotate(90deg);-webkit-transform:rotate(90deg);-o-transform:rotate(90deg);-ms-transform:rotate(90deg);transform:rotate(90deg);}';
			else if (isset($_GET['anti_rot']))
				echo 'video{-moz-transform:rotate(-90deg);-webkit-transform:rotate(-90deg);-o-transform:rotate(-90deg);-ms-transform:rotate(-90deg);transform:rotate(-90deg);}';
		?>
		
	</style>
</head>
<body>

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
	if (isset($new_comment))
		$new_comment = str_replace("\r", '<br />', str_replace("\n", '<br />', str_replace("|", '&#124;', $new_comment)));
		
	// POST virtual album creation/deletion
	$str_pst = '';
	if ($_SERVER['REMOTE_USER'] == CONST_ADMIN_USER)
	{
		if (isset($_POST['valbum_add__type']) && $_POST['valbum_add__type']=='ALBUM')
		{
			VirtualAlbumsConf\createVirtualAlbum($_POST['valbum_add__title'], $_POST['valbum_add__album'], $_POST['valbum_add__beginning'], $_POST['valbum_add__end'], $_POST['valbum_add__user']);
			$str_pst.="<div class='admin_box'>\n<font color='red'>Virtual album <i>".$_POST['valbum_add__title']."</i> created for user <i>".$_POST['valbum_add__user']."</i>.</font>\n</div>\n";
		}
		if (isset($_POST['valbum_add__type']) && $_POST['valbum_add__type']=='GROUP_TITLE')
		{
			VirtualAlbumsConf\createGroupTitle($_POST['valbum_add__title'], $_POST['valbum_add__user']);
			$str_pst.="<div class='admin_box'>\n<font color='red'>Group title <i>".$_POST['valbum_add__title']."</i> created for user <i>".$_POST['valbum_add__user']."</i>.</font>\n</div>\n";
		}
		if (isset($_POST['valbum_removal__title']))
		{
			VirtualAlbumsConf\removeVirtualAlbumOrTitle($_POST['valbum_removal__title'], $_POST['valbum_removal__user']);
			$str_pst.="<div class='admin_box'>\n<font color='red'>Virtual album or group title <i>".$_POST['valbum_removal__title']."</i> of user <i>".$_POST['valbum_removal__user']."</i> removed.</font>\n</div>\n";
		}
		if (isset($_POST['valbum_newuser']))
		{
			VirtualAlbumsConf\createNewUser($_POST['valbum_newuser']);
			$str_pst.="<div class='admin_box'>\n<font color='red'>Added <i>".$_POST['valbum_newuser']."</i> in specific rights.</font>\n</div>\n";
		}
		if (isset($_POST['valbum_removeuser']))
		{
			VirtualAlbumsConf\removeUser($_POST['valbum_removeuser']);
			$str_pst.="<div class='admin_box'>\n<font color='red'>Removed all specific rights of <i>".$_POST['valbum_removeuser']."</i>.</font>\n</div>\n";
		}
	}
	
	// GET parameters
	$valbum_id = isset($_GET['q']) ? $_GET['q'] : null;
	$media_id = isset($_GET['img']) ? $_GET['img'] : null;
	
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
	echo "\n";
?>

					</h2>
					<p>Connected as: <i><?php echo $_SERVER['REMOTE_USER']; ?></i></p>
					<div id='main'></div>
				</td>
			</tr>
		</table>
	</div>

	<div class='body_contents'>

<?php 
	echo $str_pst;

	//----------------------------
	//get album from valbum_id
	$album = null;
	if (isset($valbum_id))
	{
		if (isset($valbum_array[$valbum_id]))
			$album = $valbum_array[$valbum_id]["album"];
	}

	//----------------------------
	// media page
	if (isset($media_id))
	{
		if (isset($new_comment)) Comments\insertNewComment($album, $media_id, $new_comment);
		if (isset($comment_to_delete)) Comments\deleteComment($album, $media_id, $comment_to_delete);
		echo "<div class='media_page'>\n";
		showMediaPage($valbum_id, $album, $media_id);
		echo "</div>\n";
	}
	//----------------------------
	// album page
	else if (isset($valbum_id))
	{
		echo '<script type="text/javascript" src="ajax.js"></script>'."\n";
	
		if (isset($valbum_array[$valbum_id]))
		{
			showVirtualAlbum($valbum_id, $album, CONST_MEDIA_DIR."/$album", CONST_THUMBNAILS_DIR."/$album", $valbum_array[$valbum_id]["from_date"], $valbum_array[$valbum_id]["to_date"], false);
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
		showListOfAlbums($valbum_array, CONST_THUMBNAILS_DIR);
		if ($_SERVER['REMOTE_USER'] == CONST_ADMIN_USER)
		{
			showVirtualAlbumsEdit($valbum_array);
		}
	}
?>

	</div>

</body>
</html>

<?php
//--------------------------------------------------------------------------
// functions
//--------------------------------------------------------------------------

//----------------------------------------------

function showListOfAlbums($valbum_array, $album_thumbnails_root_dir)
{
	$curr_user = $_SERVER['REMOTE_USER'];
	if ($_SERVER['REMOTE_USER'] == CONST_ADMIN_USER)
	{
		echo "\n<div class='admin_box'>\n<h2>Albums</h2><p>An album is a subfolder of the <i>albums/</i> directory. As the administrator, you can see them all.</p>"
			."<p>The albums must be created manually (with an FTP client, for example).</p>\n"
			."<p>The thumbnails are automatically generated when watching the album for the first time.</p>\n";
	}
		
	echo "<table><tr>\n";
	$j=0;
	
	foreach ($valbum_array as $valbum_id => $valbum)
	{
		if ($_SERVER['REMOTE_USER'] == CONST_ADMIN_USER && $valbum['user'] != $curr_user)
		{
			$curr_user=$valbum['user'];
			echo "</tr></table></div>\n<div class='admin_box'><table><tr><td><h2>Virtual albums visible by: <i>".$curr_user."</i></h2></td></tr>\n<tr>";
			$j=0;
		}
			
		if ($valbum['type'] == 'GROUP_TITLE')
		{
			echo "</tr>\n<tr><td class='group'><h3>".$valbum["title"]."</h3></td></tr>\n<tr>";//</table>\n\n<table>
			$j=0;
		}
		else if ($valbum['type'] == 'ALBUM')
		{
			$album_title = $valbum['title'];
			
			$album_folder = CONST_MEDIA_DIR."/".$valbum['album'];
			
			$media_files_this_album = glob($album_folder."/*");
			
			$album_name = basename($album_folder);
			echo "\n<td class='alb_insight'><a href='".getAlbumUrl($valbum_id)."'>\n"
				."<h3 style='position:absolute;'>"
				."<span class='".($curr_user == CONST_ADMIN_USER?"admin":"normal")."'>"
				."$album_title</span></h3><span>";// - ".count($media_files_this_album)." elements
			
			showVirtualAlbum($valbum_id, $valbum['album'], $album_folder, $album_thumbnails_root_dir."/".basename($album_folder), $valbum["from_date"], $valbum["to_date"], true);
			echo "</span></a></td>\n";
			$j++;
			if (($j%CONST_NB_COLUMNS_LIST_ALBUMS)==0) {echo "</tr><tr>";$j = 0;}
		}
		else
		{
			die("l.".__LINE__." ".$valbum["type"]);
		}
	}
	echo "</tr></table>";
	if ($_SERVER['REMOTE_USER'] == CONST_ADMIN_USER) echo "</div>\n";
}

function showVirtualAlbumsEdit($valbum_array)
{
	VirtualAlbumsConf\createDefaultUserIfNotExists();
	
	//----------------------------
	// Create a virtual album
	echo "\n<div class='admin_box'>\n<h2>Create a virtual album</h2>\n"
		."<p>This allows to create a visibility on an album or a part of an album, for some authenticated user(s).</p>\n"
		."<p><form action='".getListOfAlbumsUrl()."' method='POST'><input type='hidden' name='valbum_add__type' value='ALBUM' /><ul>\n"
		."<li>Create a new virtual album named <input type='text' name='valbum_add__title' value='' /></li>"
		."<li>for user <select name='valbum_add__user'>";
	foreach (VirtualAlbumsConf\getUsers() as $registered_user) echo "<option>$registered_user</option>";
	echo "</select><small> (Note: <i>".CONST_DEFAULT_USER."</i> applies to all users without specific rights; if you want to make specific rights for a given user, see below)</small>.</li>"
		."<li>allowing visibility on the album <select name='valbum_add__album'></li>";
	foreach (glob(CONST_MEDIA_DIR."/*") as $album_folder) echo "<option>".basename($album_folder)."</option>";
	echo "</select></li>"
		."<li>starting from <input type='text' name='valbum_add__beginning' value='0' /><small> (0 means the beginning of the album, otherwise use format YYYY:MM:dd hh:mm:ss)</small></li>"
		."<li>until <input type='text' name='valbum_add__end' value='INFINITE' /><small> (INFINITE means the end of the album, otherwise use format YYYY:MM:dd hh:mm:ss)</small></li>"
		."<li><input type='submit' value='Add virtual album' /></li>\n"
		."</ul></form></p>\n"
		."</div>\n";
		
	//----------------------------
	// Create a group title
	echo "\n<div class='admin_box'>\n<h2>Create a group title</h2>\n"
		."<p><form action='".getListOfAlbumsUrl()."' method='POST'><input type='hidden' name='valbum_add__type' value='GROUP_TITLE' />\n"
		."Create a new group title named <input type='text' name='valbum_add__title' value='' /> "
		."visible by user <select name='valbum_add__user'>";
	foreach (VirtualAlbumsConf\getUsers() as $registered_user) echo "<option>$registered_user</option>";
	echo "</select> "
		."<input type='submit' value='Add group title' />\n"
		."</form></p>\n"
		."</div>\n";
	
	//----------------------------
	// Remove a virtual album or a group title
	echo "\n<div class='admin_box'>\n<h2>Remove a virtual album or a group title</h2>\n";
	$curr_user = CONST_ADMIN_USER;
	$i=0;
	foreach ($valbum_array as $valbum)
	{
		if ($valbum['user'] != $curr_user)
		{
			if ($curr_user!=CONST_ADMIN_USER) echo "</select></form></p>";
			$curr_user=$valbum['user'];			
			echo ""
				."<p><form action='".getListOfAlbumsUrl()."' method='POST'>"
				."<input type='hidden' name='valbum_removal__user' value='$curr_user' />"
				."<input type='submit' value='Remove $curr_user&#39;s' />"
				."<select name='valbum_removal__title'>";
			$i+=1;
		}
		if ($curr_user!=CONST_ADMIN_USER) echo "<option>".$valbum['title']."</option>";
	}
	if ($i > 0) echo "</select></form></p>";
	else echo "There is no virtual album nor group title to remove.";
	echo "</div>\n";
	
	//----------------------------
	// Create a new user
	echo "\n<div class='admin_box'>\n<h2>Create specific rights for a user</h2>\n"
		."<p>By default all authenticated users see what the <i>".CONST_DEFAULT_USER."</i> user sees. This allows to write specific rules for a given user.</p>"
		//Note: you also need to add authentication for this user (e.g. in the <i>.htpasswd</i> file)
		."<form action='".getListOfAlbumsUrl()."' method='POST'>\n"
		."<input type='text' name='valbum_newuser' value='' />"
		."<input type='submit' value='Add user' />\n"
		."</form>\n"
		."</div>\n";
	
	//----------------------------
	// Remove a user
	$removable_users = array();
	foreach (VirtualAlbumsConf\getUsers() as $registered_user)
		if ($registered_user!=CONST_DEFAULT_USER)
			array_push($removable_users, $registered_user);
	echo "\n<div class='admin_box'>\n<h2>Remove specific rights for a user</h2>\n";
	if (count($removable_users) > 0)
	{
		echo "<p>Note: a removed user still gets <i>".CONST_DEFAULT_USER."</i> virtual albums.</p>";
		//"<p>Note 2: you also need to remove authentication for this user (e.g. in the <i>.htpasswd</i> file).</p>"
		echo "<form action='".getListOfAlbumsUrl()."' method='POST'><select name='valbum_removeuser'></li>";
		foreach ($removable_users as $removable_user) echo "<option>$removable_user</option>\n";
		echo "</select><br /><input type='submit' value='Remove user' />\n</form>\n";
	}
	else
	{
		echo "<p>There is no user with specific rights.</p>\n";
	}
	echo "</div>\n";
}

//----------------------------------------------

function showVirtualAlbum($valbum_id, $album, $album_media_dir, $album_thumbnails_dir, $from_date, $to_date, $is_insight)
{
	echo "<div class='added_padding'>";
	$date_media_files = array();
	$i = 0;
	foreach (glob($album_media_dir."/*") as $media_file)
	{
		$ext = pathinfo($media_file, PATHINFO_EXTENSION);
		if (MediaInfos\isReallyAMediaFile($media_file))
		{
			$date_file = MediaInfos\getDateTaken($media_file);
			if (strcmp($date_file, $from_date) >= 0 && strcmp($date_file, $to_date) <= 0)
			{
				$date_media_files[$media_file] = $date_file;
				$i += 1;
			}
		}
		if ($is_insight && $i>=CONST_NB_INSIGHT_PICTURES) break;
	}
	
	asort($date_media_files);
	
	$day_mark = null;
	
	$i = 0;
	foreach($date_media_files as $media_file => $date_file)
	{
		$ext = pathinfo($media_file, PATHINFO_EXTENSION);
		$day = substr($date_file, 0, 10);
		if (($day != $day_mark || is_null($day_mark)) && !$is_insight)
		{
			echo "</div><div class='new_day'><h3>".preg_replace('/:/', '-', $day)."</h3>\n";
			$day_mark = $day;
		}
		showMediaThumb($valbum_id, $album, basename($media_file), $album_thumbnails_dir, !$is_insight, !$is_insight);
		$i++;
	}
	
	echo "</div>";
	if (!$is_insight) echo "<p>($i elements)</p>";
}

//----------------------------------------------

function showMediaThumb($valbum_id, $album, $media_id, $album_thumbnails_dir, $add_link, $add_comment_insight)
{
	if (!file_exists(getRealThumbFileFromMedia($album, $media_id)))
	{
		echo '<script type="text/javascript">media_ids_to_process.push("'.$media_id.'");</script>';
	}
	$comments_insight = "";
	$is_video = MediaInfos\isMediaFileAVideo($media_id);
	//if ($is_video) $comments_insight = "[VIDEO] ";
	if ($add_comment_insight)
	{
		$comments_array = Comments\readComments($album, $media_id);
		if (count($comments_array)>0)
		{
			$first_comment = array_shift($comments_array);
			$first_comment_comment = strlen($first_comment['comment']) > CONST_NB_CHARS_COMMENTS_INSIGHT ?
				substr($first_comment['comment'], 0, CONST_NB_CHARS_COMMENTS_INSIGHT).'[...]' : 
				$first_comment['comment'];
			$com_str = '';
			if ($first_comment['user'] == '') $com_str .= "description";
			$nb_comments = count($comments_array)+1;
			if ($first_comment['user'] == '' && $nb_comments-1 > 0) $com_str .= " &amp; ".strReplies($nb_comments-1);
			else if ($first_comment['user'] != '' && $nb_comments > 0) $com_str .= "".strReplies($nb_comments);
			$comments_insight .= "[$com_str] $first_comment_comment";
		}
	}
	if ($comments_insight != '') $comments_insight = "<span class='comments_insight'>$comments_insight</span>";
	
	echo ($add_link ? "<a class='media_thumb_link' href='".getMediaPageUrl($valbum_id, $media_id)."'>" : "")."$comments_insight";
	//if ($is_video)
	//	echo "<video class='insight_video' controls src='".getMediaUrl($valbum_id, $media_id)."' width='".CONST_WIDTH_THUMBNAIL."px;' preload='metadata' controls>";
	//else
		echo "<img class='".($is_video?'vid':'pic')."' src='".getMediaUrlThumb($valbum_id, $media_id)."' alt='(img)' />";
	echo "</img>";
	echo "".($add_link?"</a>":"")."\n";
}

//----------------------------------------------

function strReplies($nb) { return $nb>1 ? "$nb replies" : "$nb reply"; }

//----------------------------------------------

function showMediaPage($valbum_id, $album, $media_id)
{
	$is_video = MediaInfos\isMediaFileAVideo($media_id);
	$php_media_file = getMediaUrl($valbum_id, $media_id);
	$media_html = $is_video ?
		"<video src='$php_media_file' controls width='100%' />" :
		"<a href='$php_media_file'><img src='$php_media_file' alt='' style='height: 750px;'/></a>";
	
	$all_commenting = '';
	$php_this_media = getMediaPageUrl($valbum_id, $media_id);

	$i = 0;
	foreach (Comments\readComments($album, $media_id) as $comment)
	{
		$span_delete = '';
		if ($_SERVER['REMOTE_USER'] == $comment['user'] || $_SERVER['REMOTE_USER'] == CONST_ADMIN_USER)
		{
			$span_delete = "<form style='float: right;' action='$php_this_media' method='POST'>"
				."<input type='hidden' name='comment_to_delete' value='$i' />"
				."<input type='submit' value='Delete' />"
				."</form>";
		}
		$all_commenting .= "\n<div class='comment_box'>".($comment['user']==''?'':'<b>'.$comment['user'].': </b>').$comment['comment']."$span_delete</div><br />\n";
		$i += 1;
	}
	
	$all_commenting .= ""
		."<div class='comment_box'><form action='$php_this_media' method='POST'>"
		.'<textarea rows="5" name="new_comment"></textarea>'
		.'<br /><input type="submit" value="Submit comment" />'
		.'</form></div>';
	
	if ($is_video)
	{
		$all_commenting .= "<br /><br />Video orientation: <a href='$php_this_media&amp;rot'>90&deg;</a> / "
			."<a href='$php_this_media'>normal</a> / <a href='$php_this_media&amp;anti_rot'>-90&deg;</a>.";
	}
	
	echo "\n<table width='100%'><tr><td style='width: 60%;'>".$media_html."</td><td class='media_file_page_right'>".$all_commenting."</td></tr></table>\n";
}

//----------------------------------------------

function getListOfAlbumsUrl() { return '?'; }
function getAlbumUrl($valbum_id) { return "?q=$valbum_id"; }
function getMediaUrlThumb($valbum_id, $media_id) { return getMediaUrl($valbum_id, $media_id).'&amp;thumbnail'; }
function getMediaUrl($valbum_id, $media_id) { return "media.php?q=$valbum_id&amp;img=$media_id&amp;content_type=".MediaInfos\getMimeType($media_id); }
function getMediaPageUrl($valbum_id, $media_id) { return "?q=$valbum_id&amp;img=$media_id"; }

// end of functions
//----------------------------------------------
	
?>
