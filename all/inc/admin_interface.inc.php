<?php
	namespace AdminInterface;
	error_reporting(E_ALL);
	require_once("conf.inc.php");
	require_once("virtual_albums_conf.inc.php");
	require_once("media_access.inc.php");
	require_once("inc/show_albums_list.inc.php");
	
//----------------------------------------------------------------------------------------------------------------------------------------------
// public function
//----------------------------------------------------------------------------------------------------------------------------------------------

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

//----------------------------------------------

function showAdminVisibilitySpecificUser()
{
	//----------------------------
	// Create a new user
	echo "\n<div class='admin_box'>\n<h2>Visibility for a specific user</h2>\n"
		
		//."<p>By default all authenticated users see what the <i>".CONST_DEFAULT_USER."</i> user sees. This allows to write specific rules for a given user.</p>"
		//Note: you also need to add authentication for this user (e.g. in the <i>.htpasswd</i> file)
		."<p>".htmlMiniForm("Add specific rights for the user <input type='text' name='valbum_newuser' value='' />", 'Add')."</p>";
		
	/*$removable_users_opts = getSelectUsers('valbum_removeuser', true);
	if (strlen($removable_users_opts) > 0)
	{
		echo "<p>".htmlMiniForm("Remove specific rights for a user: $removable_users_opts", 'Remove')."</p>";
		//"<p>Note 2: you also need to remove authentication for this user (e.g. in the <i>.htpasswd</i> file).</p>"
	}*/
	echo "</div>\n";
}

function showAdminAlbumManagement()
{
	//----------------------------
	// Album management
	
	echo "\n<div class='admin_box'>\n<h2>Album management</h2>\n"
	
		."<h4>Manage media files</h4>"
		
		."<p>It's advised to upload the albums manually (with an FTP client, for example) instead of using the buttons below, it's more practical to upload <i>en masse</i>.</p><ul>\n"
			
		."<li>".htmlMiniForm("Create an empty album <input type='text' name='album_create' />", "Create")."</li>\n"
		
		."<li><form action='".getTargetPage()."' method='POST' enctype='multipart/form-data'>\n"
		."Add file <input type='file' name='album_addmedia__file'>"
		."in the album ".getSelectAlbums('album_addmedia__album')
		."<input type='submit' value='Submit' />\n"
		."</form></li>\n"
		
		."<li>".htmlMiniForm("Remove file <input type='text' name='album_removemedia_filename'>in the album ".getSelectAlbums('album_removemedia_album'), "Remove")."</li>\n";
		
	$empty_albums = getSelectEmptyAlbums('album_remove');
	if (strlen($empty_albums)>0) echo "<li>".htmlMiniForm("Remove an empty album $empty_albums", "Remove")."</li>\n";
		
	echo "</ul>\n";
	
	echo "\n<h4>Generate thumbnails</h4>\n" // Thumbnail and reduced pictures generation
		."<form action='".getTargetPage()."' method='POST'>You should press this button after modifying albums: "
		."<input type='submit' value='Generate missing thumbnails' />"
		."<input type='hidden' name='generate_thumbs' value='true' />"
		."</form>\n\n";
		
	echo "\n<h4>Generate <i>.htaccess</i> files</h4>\n" // .htaccess files
		."<form action='".getTargetPage()."' method='POST'>You should press this button after creating new albums (for security reasons): "
		."<input type='submit' value='Generate missing .htaccess files' />"
		."<input type='hidden' name='generate_htaccess' value='true' />"
		."</form>\n\n";
	
	echo "</div>\n\n";
}

function showStats()
{	
	$want_log = (isset($_GET['all_log']) || isset($_GET['last_log']));
	echo "<div class='admin_box'>\n<h2>Connection log</h2><p>"
		."<span class='button_top'><a href='?all_log&amp;collapse_stats'>All logs (might be huge)</a></span>"
		."<span class='button_top'><a href='?all_log_digest&amp;collapse_stats'>All logs digest</a></span>"
		."<span class='button_top'><a href='?last_log&amp;collapse_stats'>Only the last log (between 0 and 10kB)</a></span>"
		.($want_log ? "<span class='button_top'><a href='?collapse_stats'>Close</a></span>" : "")
		."</p>";
		
	$lines = array();
	
	if (isset($_GET['all_log_digest']))
	{
		$nb_lines = 0;
		$countries_hash = array();
		$cities_hash = array();
		$users_hash = array();
		$last_date = 'None';
		foreach (glob(CONST_FILE_STATS."*") as $filepath)
		{
			foreach (file($filepath) as $line)
			{
				$tab = explode("\t", $line);
				$nb_lines++;
				$users_hash[$tab[3]] = 1;
				$cities_hash[$tab[4]] = 1;
				$countries_hash[$tab[5]] = 1;
				$last_date = $tab[0];
			}
		}
		echo "<br /><br /><br />Total number of requests: $nb_lines<br />\n"
			."Last connection date: $last_date<br />\n"
			."Users: ".implode(',', array_keys($users_hash))."<br />\n"
			."Countries: ".implode(',', array_keys($countries_hash))."<br />\n"
			."Cities: ".implode(',', array_keys($cities_hash))."\n";
	}
	elseif ($want_log)
	{
		echo "<div class='connection_log'>\n"
			."<table>\n"
			."<tr style='background-color: #aaaaaa;'><td>Date and time</td><td>Path</td><td>Ip address</td>"
			."<td>User</td><td>City</td><td>Country</td><td>Internet service provider</td><td>User-Agent (Browser), truncated</td></tr>";
		if (isset($_GET['all_log']))
		{	
			foreach (glob(CONST_FILE_STATS."*") as $filepath)
			{
				$lines = array_merge($lines, file($filepath));
			}
		}
		elseif (isset($_GET['last_log']))
		{
			$lines = array_merge($lines, file(CONST_FILE_STATS));
		}
		sort($lines);
		foreach ($lines as $line)
		{
			echo "<tr>";
			foreach (explode("\t", $line) as $tab) echo "<td>$tab</td>";
			echo "</tr>\n";
		}
		
		echo "</table></div>";
	}
	echo "</div>\n";
}

//----------------------------------------------------------------------------------------------------------------------------------------------
// private functions
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

function htmlMiniForm($html, $submit_button_caption)
{
	return "<form action='".getTargetPage()."' method='POST'>\n$html\n<input type='submit' value='$submit_button_caption' />\n</form>\n";
}

function getTargetPage() { return '?collapse_list_of_albums'; }

//----------------------------------------------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------------------------------------------------

?>
