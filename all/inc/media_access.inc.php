<?php
	namespace MediaAccess;
	error_reporting(E_ALL);
	require_once("conf.inc.php");
	require_once("media_infos.inc.php");
	
	// index.php
	function getListOfAlbumsUrl() { return '?'; }
	function getAlbumUrl($valbum_id) { return "?q=$valbum_id"; }
	
	// media.php (virtual secure access to media file)
	function getMediaUrl($valbum_id, $media_id) { return "media.php?q=$valbum_id&amp;img=$media_id&amp;content_type=".\MediaInfos\getMimeType($media_id); }
	function getMediaPageUrl($valbum_id, $media_id) { return "?q=$valbum_id&amp;img=$media_id"; }
	function getSmallThumbUrl($valbum_id, $media_id) { return getMediaUrl($valbum_id, $media_id, false).'&amp;thumbnail'; }
	function getLargeThumbUrl($valbum_id, $media_id) { return getMediaUrl($valbum_id, $media_id).'&amp;reduced'; }

	// direct access to media file directories
	function getAllAlbumsDirs() { return glob(CONST_MEDIA_DIR."/*"); }
	function getAlbumDir($album) { return CONST_MEDIA_DIR."/".strip_tags($album); }
	function getSmallThumbAlbumDir($album) { return CONST_THUMBNAILS_SMALL_DIR."/".strip_tags($album); }
	function getLargeThumbAlbumDir($album) { return CONST_THUMBNAILS_LARGE_DIR."/".strip_tags($album); }
	
	// direct access to media file
	function getRealMediaFile($album, $media_id) { return CONST_MEDIA_DIR."/".strip_tags("$album/$media_id"); }
	function getRealSmallThumbFromMedia($album, $media_id) { return getFixedPath_(CONST_THUMBNAILS_SMALL_DIR, $album, $media_id); }
	function getRealLargeThumbFromMedia($album, $media_id) { return getFixedPath_(CONST_THUMBNAILS_LARGE_DIR, $album, $media_id); }
	
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