<?php
	namespace AdminInterface;
	error_reporting(E_ALL);
	require_once("conf.inc.php");
	require_once("virtual_albums_conf.inc.php");
	require_once("media_access.inc.php");
	require_once("show_albums_list.inc.php");
	
//----------------------------------------------------------------------------------------------------------------------------------------------
// public function
//----------------------------------------------------------------------------------------------------------------------------------------------

function showAllAdminInterface($valbum_array)
{	
	$all_get_ids = '';
	$all_spans = '';
	$array_of_valbum_array = \ShowAlbumsList\reorganizeIntoListOfListOfAlbums($valbum_array);
	$array_get_ids = array();
	foreach ($array_of_valbum_array as $user => $valbum_array)
	{
		if ($user != CONST_ADMIN_USER)
		{
			$get_id = collapseId($user);
			array_push($array_get_ids, $get_id);
			$all_get_ids .= '"'.$get_id.'",';
			$title = '';
			if ($user == CONST_DEFAULT_USER)
			{
				$other_users = \VirtualAlbumsConf\getUsers(true);
				$title = "Visibility for all users".(count($other_users)>0 ? " except: <i>".implode(', ', $other_users)."</i>" : '');
			}
			else
			{
				$title = "Visibility for user: <i>$user</i>";
			}
			$all_spans .= '<span class="button_top" id="_'.$get_id.'" onclick=\'displayOnly("'.$get_id.'");\'>'.$title.'</span>';
		}
	}

?>
<div id="top_admin_menu">
	<script type="text/javascript">
		var coll = [
			"collapse_list_of_albums", 
			"collapse_album_management", 
			<?php echo $all_get_ids; ?>
			"collapse_visibility_add_user", 
			"collapse_stats"
			];
		function displayOnly(id)
		{
			coll.forEach(function(entry) {
				document.getElementById("_"+entry).style.backgroundColor="inherit";
				document.getElementById(entry).style.display="none";
			});
			document.getElementById(id).style.display="block";
			document.getElementById("_"+id).style.backgroundColor="#dddddd";
		}
	</script>
	<span class="button_top" id="_collapse_list_of_albums" onclick='displayOnly("collapse_list_of_albums");'>Albums</span>
	<span class="button_top" id="_collapse_album_management" onclick='displayOnly("collapse_album_management");'>Albums management</span>
	<?php echo $all_spans; ?>
	<span class="button_top" id="_collapse_visibility_add_user" onclick='displayOnly("collapse_visibility_add_user");'>Add user for visibility</span>
	<span class="button_top" id="_collapse_stats" onclick='displayOnly("collapse_stats");'>Connection log</span>
</div>
<?php

	echo '<div id="collapse_list_of_albums" style="display: '.(isset($_GET['collapse_list_of_albums'])?'block':'none').';">';
	foreach ($array_of_valbum_array as $user => $valbum_array)
	{
		if ($user == CONST_ADMIN_USER) \ShowAlbumsList\showListOfAlbums(CONST_ADMIN_USER, $valbum_array);
	}
	echo "</div>\n\n";

	$is_collapsed = isset($_GET['collapse_album_management']);
	echo '<div id="collapse_album_management" style="display: '.($is_collapsed?'block':'none').';">';
	showAdminAlbumManagement_();
	echo "</div>\n\n";
	
	foreach ($array_of_valbum_array as $user => $valbum_array)
	{
		if ($user != CONST_ADMIN_USER)
		{
			$get_id = collapseId($user);
			echo '<div id="'.$get_id.'" style="display: '.(isset($_GET[$get_id])?'block':'none').';">';
			\ShowAlbumsList\showListOfAlbums($user, $valbum_array);
			echo "</div>\n\n";
		}
	}
	
	echo '<div id="collapse_visibility_add_user" style="display: '.(isset($_GET['collapse_visibility_add_user'])?'block':'none').';">';
	showAdminVisibilitySpecificUser_();
	echo "</div>\n\n";
	
	echo '</div>';
	if ($_SERVER['REMOTE_USER'] == CONST_ADMIN_USER)
	{
		echo '<div id="collapse_stats" style="display: none;">';
		showStats_($valbum_array);
		echo '</div>';
	}
	
	// open good tab
	foreach ($array_get_ids as $get_id)
	{
		if (isset($_GET[$get_id]))
		{
			echo '<script type="text/javascript">displayOnly("'.$get_id.'");</script>';
		}
	}
}

function doPostOperations()
{
	$str_pst = '';
	$res = false;
	//echo "<pre>";print_r($_POST);echo "</pre>";

	if (isset($_POST['generate_thumbs']))
	{
		// load list of virtual albums for this user
		echo "\n".'<script type="text/javascript" src="ajax/ajax_thumbnails.js"></script>'."\n";
		echo "\n<script type='text/javascript'>window.onload = generateThumbnailAjaxGenerator;</script>\n\n";
	}
	if (isset($_POST['generate_htaccess']))
	{
		$str_pst = 'Generation of <i>.htaccess</i> files';
		// load list of virtual albums for this user
		$res = generateAllHtaccess_();
	}
	
	//------------
	// albums
	else if (isset($_POST['album_create']) && strip_tags($_POST['album_create'])!='')
	{
		$str_pst = 'Album creation';
		$res = mkdir(\MediaAccess\getAlbumDir($_POST['album_create']));
	}
	else if (isset($_FILES['album_addmedia__file']))
	{
		$str_pst = 'File upload';
		$album = $_POST['album_addmedia__album'];
		$media_id = $_FILES['album_addmedia__file']['name'];
		if (!isset($album) || !file_exists(\MediaAccess\getAlbumDir($album))) die();
		if (file_exists(\MediaAccess\getRealSmallThumbFromMedia($album, $media_id))) unlink(\MediaAccess\getRealSmallThumbFromMedia($album, $media_id));
		if (file_exists(\MediaAccess\getRealLargeThumbFromMedia($album, $media_id))) unlink(\MediaAccess\getRealLargeThumbFromMedia($album, $media_id));
		$res = move_uploaded_file($_FILES['album_addmedia__file']['tmp_name'], \MediaAccess\getRealMediaFile($album, $media_id));
	}
	else if (isset($_POST['album_removemedia_filename']))
	{
		$str_pst = 'File removal of <i>'.$_POST['album_removemedia_filename'].'</i> in album <i>'.$_POST['album_removemedia_album'].'</i>';
		$album = $_POST['album_removemedia_album'];
		$media_id = $_POST['album_removemedia_filename'];
		$res = unlink(\MediaAccess\getRealMediaFile($album, $media_id));
		if (file_exists(\MediaAccess\getRealSmallThumbFromMedia($album, $media_id))) unlink(\MediaAccess\getRealSmallThumbFromMedia($album, $media_id));
		if (file_exists(\MediaAccess\getRealLargeThumbFromMedia($album, $media_id))) unlink(\MediaAccess\getRealLargeThumbFromMedia($album, $media_id));
	}
	else if (isset($_POST['album_remove']) && strip_tags($_POST['album_remove'])!='')
	{
		$str_pst = 'Empty album removal';
		$res = rmdir(\MediaAccess\getAlbumDir($_POST['album_remove']));
	}
	//------------
	// virtual albums
	else if (isset($_POST['valbum_add__type']) && $_POST['valbum_add__type']=='ALBUM')
	{
		$str_pst = "Virtual album <i>".$_POST['valbum_add__title']."</i> creation for user <i>".$_POST['valbum_add__user']."</i>";
		$res = \VirtualAlbumsConf\createVirtualAlbum(
			$_POST['valbum_add__title'], 
			$_POST['valbum_add__album'], 
			$_POST['valbum_add__beginning'], 
			$_POST['valbum_add__end'], 
			$_POST['valbum_add__comments_permissions'], 
			$_POST['valbum_add__user'],
			$_POST['valbum_add__album_thumb_picture'],
			$_POST['valbum_add__exclude_include_list'],
			$_POST['valbum_add__is_exclude']);
	}
	else if (isset($_POST['valbum_add__type']) && $_POST['valbum_add__type']=='GROUP_TITLE')
	{
		$str_pst = "Group title <i>".$_POST['valbum_add__title']."</i> creation for user <i>".$_POST['valbum_add__user']."</i>";
		$res = \VirtualAlbumsConf\createGroupTitle($_POST['valbum_add__title'], $_POST['valbum_add__user']);
	}
	else if (isset($_POST['valbum_removal__title']))
	{
		$str_pst = "Virtual album or group title <i>".$_POST['valbum_removal__title']."</i> (of user <i>".$_POST['valbum_removal__user']."</i>) removal";
		$res = \VirtualAlbumsConf\removeVirtualAlbumOrTitle($_POST['valbum_removal__title'], $_POST['valbum_removal__user']);
	}
	else if (isset($_POST['valbum_reorder__title']))
	{
		$str_pst = "Virtual album or group title <i>".$_POST['valbum_reorder__title']."</i> (of user <i>".$_POST['valbum_reorder__user']."</i>) reorder";
		$res = \VirtualAlbumsConf\reorderVirtualAlbumOrTitle($_POST['valbum_reorder__title'], $_POST['valbum_reorder__user'], $_POST['valbum_reorder__action']);
	}
	else if (isset($_POST['valbum_newuser']))
	{
		$str_pst = "Add of <i>".$_POST['valbum_newuser']."</i> in specific rights";
		$res = \VirtualAlbumsConf\createNewUser($_POST['valbum_newuser']);
	}
	else if (isset($_POST['valbum_removeuser']))
	{
		$str_pst = "Removal of all specific rights of <i>".$_POST['valbum_removeuser']."</i>";
		$res = \VirtualAlbumsConf\removeUser($_POST['valbum_removeuser']);
	}
	
	return array($str_pst, $res);
}

//----------------------------------------------------------------------------------------------------------------------------------------------
// private functions
//----------------------------------------------------------------------------------------------------------------------------------------------

function showAdminVisibilitySpecificUser_()
{
	//----------------------------
	// Create a new user
	echo "\n<div class='admin_box'>\n";
	//echo "<h2>Visibility for a specific user</h2>\n"
		
	//."<p>By default all authenticated users see what the <i>".CONST_DEFAULT_USER."</i> user sees. This allows to write specific rules for a given user.</p>"
	//Note: you also need to add authentication for this user (e.g. in the <i>.htpasswd</i> file)
	echo "<p>".htmlMiniForm('?', "Add specific rights for the user <input type='text' name='valbum_newuser' value='' />", 'Add')."</p>";
		
	/*$removable_users_opts = getSelectUsers('valbum_removeuser', true);
	if (strlen($removable_users_opts) > 0)
	{
		echo "<p>".htmlMiniForm('?', "Remove specific rights for a user: $removable_users_opts", 'Remove')."</p>";
		//"<p>Note 2: you also need to remove authentication for this user (e.g. in the <i>.htpasswd</i> file).</p>"
	}*/
	echo "</div>\n";
}

function showAdminAlbumManagement_()
{
	//----------------------------
	// Album management
	
	$TARGET_PAGE = '?collapse_album_management';
	
	echo "\n<div class='admin_box'>\n";
	//echo "<h2>Album management</h2>\n"
	echo ""
		."<h4>Manage media files</h4>"
		
		."<p>It's advised to upload the albums manually (with an FTP client, for example) instead of using the buttons below, it's more practical to upload <i>en masse</i>.</p><ul>\n"
			
		."<li>".htmlMiniForm($TARGET_PAGE, "Create an empty album <input type='text' name='album_create' />", "Create")."</li>\n"
		
		."<li><form action='$TARGET_PAGE' method='POST' enctype='multipart/form-data'>\n"
		."Add file <input type='file' name='album_addmedia__file'>"
		."in the album ".getSelectAlbums('album_addmedia__album')
		."<input type='submit' value='Submit' />\n"
		."</form></li>\n"
		
		."<li>".htmlMiniForm($TARGET_PAGE, "Remove file <input type='text' name='album_removemedia_filename'>in the album ".getSelectAlbums('album_removemedia_album'), "Remove")."</li>\n";
		
	$empty_albums = getSelectEmptyAlbums('album_remove');
	if (strlen($empty_albums)>0) echo "<li>".htmlMiniForm($TARGET_PAGE, "Remove an empty album $empty_albums", "Remove")."</li>\n";
		
	echo "</ul>\n";
	
	echo "\n<h4>Generate thumbnails</h4>\n" // Thumbnail and reduced pictures generation
		."<form action='$TARGET_PAGE' method='POST'>You should press this button after modifying albums: "
		."<input type='submit' value='Generate missing thumbnails' />"
		."<input type='hidden' name='generate_thumbs' value='true' />"
		."</form>\n\n";
		
	echo "\n<h4>Generate <i>.htaccess</i> files</h4>\n" // .htaccess files
		."<form action='$TARGET_PAGE' method='POST'>You should press this button after creating new albums (for security reasons): "
		."<input type='submit' value='Generate missing .htaccess files' />"
		."<input type='hidden' name='generate_htaccess' value='true' />"
		."</form>\n\n";
	
	echo "</div>\n\n";
}

function showStats_()
{	
	echo '<script type="text/javascript" src="ajax/ajax_get_stats.js"></script>'."\n";
	echo '<div class= "admin_box">
		<p>
			<script type="text/javascript">
				function loadOnly(id)
				{
					document.getElementById("_all_log").style.backgroundColor="inherit";
					document.getElementById("_all_log_digest").style.backgroundColor="inherit";
					document.getElementById("_last_log").style.backgroundColor="inherit";
					document.getElementById("_"+id).style.backgroundColor="#dddddd";
					loadStats(id);
				}
			</script>
			<span class="button_top" id="_all_log" onclick=\'loadOnly("all_log");\'>All logs (might be huge)</span>
			<span class="button_top" id="_all_log_digest" onclick=\'loadOnly("all_log_digest");\'>All logs digest</span>
			<span class="button_top" id="_last_log" onclick=\'loadOnly("last_log");\'>Only the last log (between 0 and 10kB)</span>
		</p>
		';
		
	echo "<div id='stats'></div>\n";
}

//----------------------------------------------------------------------------------------------------------------------------------------------
// very private functions
//----------------------------------------------------------------------------------------------------------------------------------------------

function generateAllHtaccess_()
{
	$res = true;
	foreach (\MediaAccess\getAllAlbumsDirs() as $album_folder)
	{
		$album = basename(strip_tags($album_folder));
		if (!file_exists("$album_folder/.htaccess"))
		{
			$res = (file_put_contents("$album_folder/.htaccess", "Deny from all") != FALSE) && $res;
		}
	}
	return $res;
}

function getSelectAlbums($name)
{
	$str = "";
	foreach (\MediaAccess\getAllAlbumsDirs() as $album_folder) $str .= "<option>".basename($album_folder)."</option>";
	return "<select name='$name'>$str</select>";
}

function getSelectEmptyAlbums($name)
{
	$str = "";
	foreach (\MediaAccess\getAllAlbumsDirs() as $album_folder) if (count(glob("$album_folder/*"))==0) $str .= "<option>".basename($album_folder)."</option>";
	return $str==''?'':"<select name='$name'>$str</select>";
}

function getSelectUsers($name, $skip_default_user)
{
	$opts = array_map(function ($user) { return "<option>$user</option>"; }, \VirtualAlbumsConf\getUsers($skip_default_user));
	return count($opts)==0?'':"<select name='$name'>".implode('', $opts)."</select>";
}

function htmlMiniForm($target_page, $html, $submit_button_caption)
{
	return "<form action='$target_page' method='POST'>\n$html\n<input type='submit' value='$submit_button_caption' />\n</form>\n";
}

//----------------------------------------------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------------------------------------------------

?>
