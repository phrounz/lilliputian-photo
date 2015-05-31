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
		$valbum_array = \VirtualAlbumsConf\listVirtualAlbums();
		generateAllThumbsAndReducedPictures_($valbum_array);
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
			$_POST['valbum_add__user']);
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

function showEdition($valbum_array)
{
	//----------------------------
	// Album management
	echo "\n<div class='admin_box'>\n<h2>Album management</h2>\n"
		
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
		
	echo "</ul></div>\n";
	
	//----------------------------
	// Thumbnail and reduced pictures generation
	
	echo "\n<div class='admin_box'>\n<h2>Generate thumbnails</h2>\n"
		."<p>You should call this after modifying albums."
		."<form action='".getTargetPage()."' method='POST'>"
		."<input type='submit' value='Generate missing thumbnails' />"
		."<input type='hidden' name='generate_thumbs' value='true' /></form></div>";
	
	//----------------------------
	// Virtual albums and group titles
	echo "\n<div class='admin_box'>\n<h2>Virtual albums and group titles</h2>\n"
		."<p>This allows to create a visibility on an album or a part of an album, for some authenticated user(s).</p><ul>\n"
		."<li><form action='".getTargetPage()."' method='POST'><input type='hidden' name='valbum_add__type' value='ALBUM' />\n"
		."Create a new <b>virtual album</b> named <input type='text' name='valbum_add__title' value='' />"
		."<br />for user <select name='valbum_add__user'>";
	foreach (\VirtualAlbumsConf\getUsers(false) as $registered_user) echo "<option>$registered_user</option>";
	echo "</select><small> (Note: <i>".CONST_DEFAULT_USER."</i> applies to all users without specific rights; if you want to make specific rights for a given user, see below)</small>."
		."<br />allowing visibility on the album ".getSelectAlbums('valbum_add__album')
		."<br />starting from <input type='text' name='valbum_add__beginning' value='0' /><small> (0 means the beginning of the album, otherwise use format YYYY:MM:dd hh:mm:ss)</small>"
		."<br />until <input type='text' name='valbum_add__end' value='ZZZZZZZZZ' /><small> (ZZZZZZZZZ means the end of the album, otherwise use format YYYY:MM:dd hh:mm:ss)</small>"
		."<br />and about commenting: <input type='radio' name='valbum_add__comments_permissions' value='NONE'>no access</input> - "
		."<input type='radio' name='valbum_add__comments_permissions' value='R'>read access</input> - "
		."<input type='radio' name='valbum_add__comments_permissions' value='RW'>read/write</input> - "
		."<input type='radio' name='valbum_add__comments_permissions' value='RWD' checked>read/write + delete own comments</input> - "
		."<input type='radio' name='valbum_add__comments_permissions' value='RWDA'>read/write + delete all comments</input>"
		."<br /><input type='submit' value='Add virtual album' />\n"
		."</form></li><br />\n"

		."<li><form action='".getTargetPage()."' method='POST'><input type='hidden' name='valbum_add__type' value='GROUP_TITLE' />\n"
		."Create a new <b>group title</b> named <input type='text' name='valbum_add__title' value='' /> "
		."visible by user ".getSelectUsers('valbum_add__user', false)
		."<input type='submit' value='Add group title' />\n"
		."</form></li><br />\n";

	$curr_user = CONST_ADMIN_USER;
	$i=0;
	foreach ($valbum_array as $valbum)
	{
		if ($valbum['user'] != $curr_user)
		{
			if ($curr_user!=CONST_ADMIN_USER) echo "<input type='submit' value='Remove' /></select></form></li><br />";
			$curr_user=$valbum['user'];			
			echo ""
				."<li><form action='".getTargetPage()."' method='POST'>"
				."Remove <i>$curr_user</i>&#39;s virtual album or group title <input type='hidden' name='valbum_removal__user' value='$curr_user' />"
				."<select name='valbum_removal__title'>";
			$i+=1;
		}
		if ($curr_user!=CONST_ADMIN_USER) echo "<option>".$valbum['title']."</option>";
	}
	if ($i > 0) echo "<input type='submit' value='Remove' /></select></form></li><br />";
	
	echo "</ul></div>\n";
	
	//----------------------------
	// Create a new user
	echo "\n<div class='admin_box'>\n<h2>Specific rights for a user</h2>\n"
		
		."<p>By default all authenticated users see what the <i>".CONST_DEFAULT_USER."</i> user sees. This allows to write specific rules for a given user.</p><ul>"
		//Note: you also need to add authentication for this user (e.g. in the <i>.htpasswd</i> file)
		."<li>".htmlMiniForm("Add specific rights for the user <input type='text' name='valbum_newuser' value='' />", 'Add')."</li>";
		
	$removable_users_opts = getSelectUsers('valbum_removeuser', true);
	if (strlen($removable_users_opts) > 0)
	{
		echo "<li>".htmlMiniForm("Remove specific rights for a user: $removable_users_opts", 'Remove')."</li>";
		//"<p>Note 2: you also need to remove authentication for this user (e.g. in the <i>.htpasswd</i> file).</p>"
	}
	echo "</ul></div>\n";
}

//----------------------------------------------------------------------------------------------------------------------------------------------
// private functions
//----------------------------------------------------------------------------------------------------------------------------------------------

function generateAllThumbsAndReducedPictures_($valbum_array)
{
	echo "\n"
		.'<script type="text/javascript" src="ajax.js"></script>'."\n"
		.'<script type="text/javascript">'."\n";
		
	foreach ($valbum_array as $valbum_id => $valbum)
	{
		if (isset($valbum) && $valbum['type'] == 'ALBUM' && $valbum['user'] == CONST_ADMIN_USER)
		{
			$album = $valbum['album'];
			$is_cut = false;
			foreach (\ShowVirtualAlbum\getListOfDatePerMedias($album, $valbum["from_date"], $valbum["to_date"], false, $is_cut) as $media_file => $date)
			{
				$media_id = basename($media_file);
						
				if (!file_exists(\MediaAccess\getRealSmallThumbFromMedia($album, $media_id)) || !file_exists(\MediaAccess\getRealLargeThumbFromMedia($album, $media_id)))
				{
					echo 'media_ids.push("'.$media_id.'");valbum_ids.push("'.$valbum_id.'");'."\n";
				}
			}
		}
	}
	echo "\n"
		.'if (media_ids.length > 0){generateThumbnailAjax(media_ids[0], valbum_ids[0]);}else {alert("Ok, nothing to do!");}'."\n"
		.'</script>'
		."\n\n";
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

function getTargetPage() { return '?'; }

//----------------------------------------------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------------------------------------------------

?>
