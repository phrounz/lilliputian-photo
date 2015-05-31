<?php
	error_reporting(E_ALL);
	require_once("inc/conf.inc.php");
	require_once("inc/virtual_albums_conf.inc.php");
	require_once("inc/media_access.inc.php");
	require_once("inc/show_virtual_album.inc.php");
	
	// GET parameters
	$content_type = $_GET['content_type'];
	$valbum_id = $_GET['q'];
	$media_id = $_GET['img'];

	// load list of virtual albums for this user
	$valbum_array = VirtualAlbumsConf\listVirtualAlbums();
	
	//get album from valbum_id
	$album = isset($valbum_id) && isset($valbum_array[$valbum_id]) ? $valbum_array[$valbum_id]["album"] : null;
	
	// display the image/video
	if (isset($album))
	{
		$filepath = null;
		if (isset($_GET['thumbnail']))
			$filepath = MediaAccess\getRealSmallThumbFromMedia($album, $media_id);
		else if (isset($_GET['reduced']))
			$filepath = MediaAccess\getRealLargeThumbFromMedia($album, $media_id);
		else
			$filepath = MediaAccess\getRealMediaFile($album, $media_id);
		
		// hack the content-type if this is a png video thumb
		$content_type_parts = explode('/', $content_type);
		if (pathinfo($filepath, PATHINFO_EXTENSION) == 'png' && array_shift($content_type_parts) == 'video') $content_type = 'image/png';
		
		// set up content-type
		header("Content-Type: $content_type");
		
		// show the file
		readfile($filepath);
	}
?>
