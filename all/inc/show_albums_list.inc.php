<?php
	namespace ShowAlbumsList;
	error_reporting(E_ALL);
	require_once("inc/virtual_albums_conf.inc.php");
	require_once("inc/show_virtual_album.inc.php");
	require_once("inc/media_access.inc.php");

//----------------------------------------------

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
			$curr_user=$valbum['user'];
			echo "</tr></table></div>\n<div class='admin_box'><table><tr><td><h2>Stuff visible by: <i>".$curr_user."</i></h2></td></tr>\n<tr>";
			$j=0;
		}
			
		if ($valbum['type'] == 'GROUP_TITLE')
		{
			echo "</tr>\n<tr><td class='group'><h2>".$valbum["title"]."</h2></td></tr>\n<tr>";//</table>\n\n<table>
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
				."</a></div></td>\n";
			
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
		echo "</div>\n";
		foreach (\VirtualAlbumsConf\getUsers(false) as $user)
		{
			if (\VirtualAlbumsConf\isUserConfEmpty($user))
				echo "<div class='admin_box'><h2>Stuff visible by: <i>".$user."</i></h2><p>Nothing is visible by this user.</p></div>";
		}
	}
}

//----------------------------------------------

//----------------------------------------------

?>