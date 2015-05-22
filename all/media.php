<?php
	error_reporting(E_ALL);
	require_once("inc/conf.inc.php");
	require_once("inc/virtual_albums_conf.inc.php");
	require_once("inc/media_access.inc.php");
	
	// GET parameters
	$content_type = $_GET['content_type'];
	$valbum_id = $_GET['q'];
	$media_id = $_GET['img'];

	// set up content-type
	header("Content-Type: $content_type");
	
	// load list of virtual albums for this user
	$valbum_array = VirtualAlbumsConf\listVirtualAlbums();
	
	//----------------------------
	//get album from valbum_id
	$album = isset($valbum_id) && isset($valbum_array[$valbum_id]) ? $valbum_array[$valbum_id]["album"] : null;
	
	//----------------------------
	// display the image/video
	if (isset($album))
	{
		readfile(isset($_GET['thumbnail']) ? MediaAccess\getRealThumbFileFromMedia($album, $media_id) : MediaAccess\getRealMediaFile($album, $media_id));
	}
?>