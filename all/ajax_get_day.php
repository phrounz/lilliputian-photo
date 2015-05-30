<?php
	error_reporting(E_ALL);
	require_once("inc/conf.inc.php");
	require_once("inc/virtual_albums_conf.inc.php");
	require_once("inc/media_access.inc.php");
	require_once("inc/show_virtual_album.inc.php");
	
	$valbum_id = $_GET['valbum_id'];
	$day = $_GET['day'];
	
	// load list of virtual albums for this user
	$valbum_array = VirtualAlbumsConf\listVirtualAlbums();
	
	//get album from valbum_id
	$album = isset($valbum_id) && isset($valbum_array[$valbum_id]) ? $valbum_array[$valbum_id]["album"] : null;
	
	$valbum = $valbum_array[$valbum_id];
	
	// display the image/video
	if (isset($album))
	{
		ShowVirtualAlbum\showVirtualAlbum(
			$valbum_id, 
			$album, 
			strcmp($day, $valbum['from_date']) < 0 ? $valbum['from_date'] : $day, 
			strcmp($day."ZZZZZZZZZZ", $valbum['to_date']) < 0 ? $day."ZZZZZZZZZZ" : $valbum['to_date'], 
			$valbum['comments_permissions'], 
			false);
	}
?>
