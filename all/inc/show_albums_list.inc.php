<?php
	namespace ShowAlbumsList;
	error_reporting(E_ALL);
	require_once("inc/virtual_albums_conf.inc.php");
	require_once("inc/show_virtual_album.inc.php");
	require_once("inc/media_access.inc.php");

//----------------------------------------------------------------------------------------------------------------------------------------------
// public functions
//----------------------------------------------------------------------------------------------------------------------------------------------

function showListOfAlbums($valbum_array)
{
	$curr_user = $_SERVER['REMOTE_USER'];
	if ($_SERVER['REMOTE_USER'] == CONST_ADMIN_USER)
	{
		echo "\n<div class='admin_box'>\n"
			."<h2>Albums</h2>\n"
			."<p>An album is a subfolder of the <i>albums/</i> directory. As the administrator, you can see them all.</p>\n";
	}
		
	echo "<table><tr>\n";
	$j=0;
	
	foreach ($valbum_array as $valbum_id => $valbum)
	{
		if ($_SERVER['REMOTE_USER'] == CONST_ADMIN_USER && $valbum['user'] != $curr_user)
		{
			echo "</tr></table>\n".getCreateVirtualAlbumOrGroupButtons_($curr_user)."</div>\n";
			$curr_user=$valbum['user'];
			echo "\n\n<div class='admin_box'><h2>Visibility for user: <i>".$curr_user."</i></h2>\n\n<table><tr>";
			$j=0;
		}
			
		if ($valbum['type'] == 'GROUP_TITLE')
		{
			echo "</tr>\n<tr><td class='group'><h2>".$valbum["title"]."</h2>".getDeleteAndReorderButtons_($curr_user, $valbum["title"])."</td></tr>\n<tr>";
			$j=0;
		}
		else if ($valbum['type'] == 'ALBUM')
		{
			$album_title = $valbum['title'];
			
			echo "\n<td><div class='alb_insight'><a href='".\MediaAccess\getAlbumUrl($valbum_id)."'>\n"
				."  <span>";// - ".count($media_files_this_album)." elements
			
			if (isset($valbum["album_thumb_picture"]) && $valbum["album_thumb_picture"]!='')
			{
				$pic = $valbum["album_thumb_picture"];
				if (strstr($pic, '/'))
				{
					$array_pics = explode('/', $valbum["album_thumb_picture"]);
					$pic = $array_pics[array_rand($array_pics, 1)];
				}
				echo "    <img class='album_thumb_picture' src='".(\MediaAccess\getLargeThumbUrl($valbum_id, $pic))."' alt=''>\n";
			}
			else
			{
				echo "    <span style='text-align: center;'>";
				\ShowVirtualAlbum\show($valbum_id, $valbum, null, true, false, 2);
				echo "</span>\n";
			}
			
			echo "    <h4><span class='".($curr_user == CONST_ADMIN_USER?"admin":"normal")."'>"."$album_title</span></h4>";
			// style='position:absolute;'
				
			echo "  </span>\n"
				."</a>";
			echo getDeleteAndReorderButtons_($curr_user, $album_title);
			echo "</div></td>\n";
			
			$j++;
			if (($j%CONST_NB_COLUMNS_LIST_ALBUMS)==0) {echo "</tr><tr>";$j = 0;}
		}
		else
		{
			die("l.".__LINE__." ".$valbum["type"]);
		}
	}
	echo "</tr></table>";
	if ($_SERVER['REMOTE_USER'] == CONST_ADMIN_USER)
	{
		echo getCreateVirtualAlbumOrGroupButtons_($curr_user);
		echo "</div>\n";
		foreach (\VirtualAlbumsConf\getUsers(false) as $user)
		{
			if (\VirtualAlbumsConf\isUserConfEmpty($user))
			{
				echo "<div class='admin_box'><h2>Stuff visible by: <i>".$user."</i></h2><p>Nothing is visible by this user.</p>"
					.getCreateVirtualAlbumOrGroupButtons_($user)."</div>";
			}
		}
	}
}

//----------------------------------------------------------------------------------------------------------------------------------------------
// private functions
//----------------------------------------------------------------------------------------------------------------------------------------------

function getDeleteAndReorderButtons_($curr_user, $album_title)
{
	$str = '';//"<a name='$album_title"."_____$curr_user'></a>";
	if ($_SERVER['REMOTE_USER'] == CONST_ADMIN_USER && $curr_user != CONST_ADMIN_USER)
	{
		$str .= "<small><form action='?' method='POST'>\n"
			."Reorder: <input type='hidden' name='valbum_reorder__user' value='$curr_user' />"
			."<select name='valbum_reorder__action'>\n"
			."  <option>MoveTop</option>\n"
			."  <option>MoveBeginningGroup</option>\n"
			."  <option>MoveUp</option>\n"
			."  <option>MoveDown</option>\n"
			."  <option>MoveBottom</option>\n"
			."</select>\n"
			."<input type='hidden' name='valbum_reorder__title' value='$album_title' />"
			."<input type='submit' value='Reorder' /></select>"
			."</form></small>\n"
			
			."<small><form action='?' method='POST'>\n"
			."Delete: <input type='hidden' name='valbum_removal__user' value='$curr_user' />"
			."<input type='hidden' name='valbum_removal__title' value='$album_title' />"
			."<input type='submit' value='Delete!' /></select>"
			."</form></small>\n";
	}
	return $str;
}

function getCreateVirtualAlbumOrGroupButtons_($curr_user)
{
	if ($curr_user == CONST_ADMIN_USER) return '';
	return "\n<div class='admin_grey_box'>"
		."<form action='?' method='POST'><input type='hidden' name='valbum_add__type' value='ALBUM' />\n"
		."Create a new <b>virtual album</b> named <input type='text' name='valbum_add__title' value='' />"
		."<input type='hidden' name='valbum_add__user' value='$curr_user' />"
		."<br />allowing visibility on the album ".getSelectAlbums('valbum_add__album')
		."<br />starting from <input type='text' name='valbum_add__beginning' value='0' /><small> (0 means the beginning of the album, otherwise use format YYYY:MM:dd hh:mm:ss)</small>"
		."<br />until <input type='text' name='valbum_add__end' value='ZZZZZZZZZ' /><small> (ZZZZZZZZZ means the end of the album, otherwise use format YYYY:MM:dd hh:mm:ss)</small>"
		."<br />and about commenting: <input type='radio' name='valbum_add__comments_permissions' value='NONE'>no access</input> - "
		."<input type='radio' name='valbum_add__comments_permissions' value='R'>read access</input> - "
		."<input type='radio' name='valbum_add__comments_permissions' value='RW'>read/write</input> - "
		."<input type='radio' name='valbum_add__comments_permissions' value='RWD' checked>read/write + delete own comments</input> - "
		."<input type='radio' name='valbum_add__comments_permissions' value='RWDA'>read/write + delete all comments</input>"
		."<br />With main thumbnail picture: <input type='text' name='valbum_add__album_thumb_picture' value='' /> "
		."<small>(example: <i>IMG1234.jpg</i>, look in the album for name) (let blank for automatic pictures combination) "
		."(you can put several pictures, separated by \"/\", for random selection)</small>"
		."<br /><input type='submit' value='Add virtual album' />\n"
		."</form></div>\n"

		."<div class='admin_grey_box'><form action='?' method='POST'><input type='hidden' name='valbum_add__type' value='GROUP_TITLE' />\n"
		."Create a new <b>group title</b> named <input type='text' name='valbum_add__title' value='' /> "
		."<input type='hidden' name='valbum_add__user' value='$curr_user' />"
		."<input type='submit' value='Add group title' />\n"
		."</form><br /></div>\n";
}

function getSelectAlbums($name)
{
	$str = "";
	foreach (\MediaAccess\getAllAlbumsDirs() as $album_folder) $str .= "<option>".basename($album_folder)."</option>";
	return "<select name='$name'>$str</select>";
}

//----------------------------------------------

?>