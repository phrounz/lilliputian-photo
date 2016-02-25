<?php
	error_reporting(E_ALL);
	require_once("../inc/conf.inc.php");
	require_once("../inc/virtual_albums_conf.inc.php");
	require_once("../inc/media_access.inc.php");
	require_once("../inc/show_virtual_album.inc.php");
	
	chdir("..");// change directory to the same than index.php before going further
	
	$valbum_id = $_GET['valbum_id'];
	$day = $_GET['day'];
	
	// load list of virtual albums for this user
	$valbum_array = \VirtualAlbumsConf\listVirtualAlbums();
	
	//get album from valbum_id
	$valbum = $valbum_array[$valbum_id];
	
	// display the image/video
	if (isset($valbum) && $valbum['type'] == 'ALBUM')
	{
		\ShowVirtualAlbum\show($valbum_id, $valbum, $day, false, true, null);
	}
?>
