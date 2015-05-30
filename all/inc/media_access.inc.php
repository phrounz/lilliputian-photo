<?php
	namespace MediaAccess;
	error_reporting(E_ALL);
	require_once("conf.inc.php");
	
	function getAllAlbumsDirs() { return glob(CONST_MEDIA_DIR."/*"); }
	
	function getAlbumDir($album) { return CONST_MEDIA_DIR."/".strip_tags($album); }
	function getAlbumThumbnailDir($album) { return CONST_THUMBNAILS_DIR."/".strip_tags($album); }
	function getAlbumReducedDir($album) { return CONST_REDUCED_DIR."/".strip_tags($album); }
	
	function getRealMediaFile($album, $media_id) { return CONST_MEDIA_DIR."/".strip_tags("$album/$media_id"); }
	function getRealThumbFileFromMedia($album, $media_id) { return getFixedPath_(CONST_THUMBNAILS_DIR, $album, $media_id); }
	function getRealReducedFileFromMedia($album, $media_id) { return getFixedPath_(CONST_REDUCED_DIR, $album, $media_id); }
	
	function getFixedPath_($root_dir, $album, $media_id)
	{
		$ext = pathinfo($media_id, PATHINFO_EXTENSION);
		$media_file_without_ext = "$root_dir/$album/".basename($media_id, ".".$ext);
	
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