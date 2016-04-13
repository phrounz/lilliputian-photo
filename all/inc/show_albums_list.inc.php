<?php
	namespace ShowAlbumsList;
	error_reporting(E_ALL);
	require_once("inc/virtual_albums_conf.inc.php");
	require_once("inc/show_virtual_album.inc.php");
	require_once("inc/media_access.inc.php");

//----------------------------------------------------------------------------------------------------------------------------------------------
// public functions
//----------------------------------------------------------------------------------------------------------------------------------------------

function reorganizeIntoListOfListOfAlbums($valbum_array)
{
	//-------------------------
	// reorganize into $array_of_valbum_array
	
	$array_of_valbum_array = array();
	
	$user = null;
	
	$valbum_array_part = array();
	foreach ($valbum_array as $valbum_id => $valbum)
	{
		if (!isset($user)) $user = $valbum['user'];
		if ($_SERVER['REMOTE_USER'] == CONST_ADMIN_USER && $valbum['user'] != $user)
		{
			$array_of_valbum_array[$user] = $valbum_array_part;
			$valbum_array_part = array();
			$user = $valbum['user'];
		}
		$valbum_array_part[$valbum_id] = $valbum;
	}
	$array_of_valbum_array[$user] = $valbum_array_part;
	
	foreach (\VirtualAlbumsConf\getUsers(false) as $user)
	{
		if (\VirtualAlbumsConf\isUserConfEmpty($user))
		{
			$array_of_valbum_array[$user] = array();
		}
	}
	return $array_of_valbum_array;
}

//-------------------------------------------------

function showListOfAlbums($user, $valbum_array)
{
	$get_id = collapseId($user);
	if (count($valbum_array) == 0)
	{
		echo "<div class='admin_box'><p>Nothing is yet visible for ".($user==CONST_DEFAULT_USER?"these users":"this user").".</p>";//<h2>Visibility for user: <i>".$user."</i></h2>
		if ($user != CONST_DEFAULT_USER)
		{
			echo "<form action='?$get_id' method='POST'> "
				."<input type='submit' value='Delete all specific rights on this user' />"
				."<input type='hidden' name='valbum_removeuser' value='$user' />"
				."</form>\n\n";
		}
		echo getCreateVirtualAlbumOrGroupButtons_($user)."</div>";
	}
	else
	{
		$j=0;
		
		echo "\n\n<!-- new album list -->\n"
			.'<div class="admin_box">';
		
		echo "\n\n";
		if ($user == CONST_ADMIN_USER)
		{
			echo "<p>An album is a subfolder of the <i>albums/</i> directory. As the administrator, you can see them all.</p>\n";
		}
		elseif ($user != CONST_DEFAULT_USER)
		{
			echo "<form action='?$get_id' method='POST'> "
				."<input type='submit' value='Delete all specific rights on this user' />"
				."<input type='hidden' name='valbum_removeuser' value='$user' />"
				."</form>\n\n";
		}
		
		echo "<table><tr>";
		
		foreach ($valbum_array as $valbum_id => $valbum)
		{
			if ($valbum['type'] == 'GROUP_TITLE')
			{
				echo "</tr>\n<tr><td class='group'><h2>".$valbum["title"]."</h2>".getDeleteAndReorderButtons_($user, $valbum["title"])."</td></tr>\n<tr>";
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
					$date_media_files = \ShowVirtualAlbum\getListOfDatePerMediasFromValbum($valbum, true);
					\ShowVirtualAlbum\showVirtualAlbumDayOrWhole($valbum_id, $valbum, $date_media_files, true, true, false, 2, null);
					echo "</span>\n";
				}
				
				echo "    <h4><span class='".($user == CONST_ADMIN_USER?"admin":"normal")."'>"."$album_title</span></h4>";
				// style='position:absolute;'
					
				echo "  </span>\n"
					."</a>";
				echo getDeleteAndReorderButtons_($user, $album_title);
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
		if ($_SERVER['REMOTE_USER'] == CONST_ADMIN_USER && $valbum['user'] != CONST_ADMIN_USER)
		{
			echo getCreateVirtualAlbumOrGroupButtons_($user);
		}
		echo "</div>\n\n";
	}
}

//----------------------------------------------------------------------------------------------------------------------------------------------
// private functions
//----------------------------------------------------------------------------------------------------------------------------------------------

function getDeleteAndReorderButtons_($user, $album_title)
{
	$str = '';
	if ($_SERVER['REMOTE_USER'] == CONST_ADMIN_USER && $user != CONST_ADMIN_USER)
	{
		$get_id = collapseId($user);
		$str .= "<small><form action='?$get_id' method='POST'>\n"
			."Reorder: <input type='hidden' name='valbum_reorder__user' value='$user' />"
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
			
			."<small><form action='?$get_id' method='POST'>\n"
			."Delete: <input type='hidden' name='valbum_removal__user' value='$user' />"
			."<input type='hidden' name='valbum_removal__title' value='$album_title' />"
			."<input type='submit' value='Delete!' /></select>"
			."</form></small>\n";
	}
	return $str;
}

function getCreateVirtualAlbumOrGroupButtons_($user)
{
	if ($user == CONST_ADMIN_USER) return '';
	$get_id = collapseId($user);
	return "\n<div class='admin_grey_box'>"
		."<form action='?$get_id' method='POST'><input type='hidden' name='valbum_add__type' value='ALBUM' />\n"
		."Create a new <b>virtual album</b> named <input type='text' name='valbum_add__title' value='' />"
		."<input type='hidden' name='valbum_add__user' value='$user' />"
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
		
		."<br />Exclude/include list: <input type='text' name='valbum_add__exclude_include_list' value='' /> "
		."<small>(example: <i>IMG1234.jpg</i>, look in the album for name)"
		."(you can put several pictures, separated by \"/\")</small>"
		
		."<input type='radio' name='valbum_add__is_exclude' value='0' />include list"
		."<input type='radio' name='valbum_add__is_exclude' value='1' checked />exclude list"
		." <small>(let blank with exclude checked to keep all media files)</small>"
		
		."<br /><input type='submit' value='Add virtual album' />\n"
		."</form></div>\n"

		."<div class='admin_grey_box'><form action='?$get_id' method='POST'><input type='hidden' name='valbum_add__type' value='GROUP_TITLE' />\n"
		."Create a new <b>group title</b> named <input type='text' name='valbum_add__title' value='' /> "
		."<input type='hidden' name='valbum_add__user' value='$user' />"
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