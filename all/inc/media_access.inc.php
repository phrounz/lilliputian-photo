<?php
	namespace MediaAccess;
	error_reporting(E_ALL);
	require_once("conf.inc.php");
	
	function getAlbumDir($album) { return CONST_MEDIA_DIR."/".strip_tags($album); }
	
	function getAllAlbumsDirs() { return glob(CONST_MEDIA_DIR."/*"); }
	
	function getRealMediaFile($album, $media_id) { return CONST_MEDIA_DIR."/".strip_tags("$album/$media_id"); }
	
	function getAlbumThumbnailDir($album) { return CONST_THUMBNAILS_DIR."/".strip_tags($album); }
	
	function getRealThumbFileFromMedia($album, $media_id)
	{
		$ext = pathinfo($media_id, PATHINFO_EXTENSION);
		$media_file_without_ext = CONST_THUMBNAILS_DIR."/$album/".basename($media_id, ".".$ext);	
	
		$thm_file = "$media_file_without_ext.THM";
		$jpg_helper_file = "$media_file_without_ext.JPG";
		
		if (($ext != 'jpg' && $ext != 'JPG') && file_exists($thm_file))
			$thumb_file = $thm_file;
		else if ($ext == 'MOV' && file_exists($jpg_helper_file))
			$thumb_file = $jpg_helper_file;
		else if ($ext == 'jpg' || $ext == 'JPG')
			$thumb_file = "$media_file_without_ext.jpg";
		else
			$thumb_file = "video.png";
		return $thumb_file;
	}
?>