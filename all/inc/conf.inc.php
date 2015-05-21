<?php
	// general	
	define('CONST_ADMIN_USER', 'admin');
	define('CONST_DEFAULT_USER', 'default');
	define('CONST_MEDIA_DIR', "./albums");
	define('CONST_THUMBNAILS_DIR', "./thumbnails");
	define('CONST_MAIN_TITLE', 'Photos &amp; videos');
	
	// album page (media thumbnails)
	define('CONST_WIDTH_THUMBNAIL', 200);
	define('CONST_HEIGHT_THUMBNAIL', 150);
	define('CONST_NB_CHARS_COMMENTS_INSIGHT', 100);
	
	// album list page
	define('CONST_WIDTH_ALBUM_INSIGHT', 100);
	define('CONST_HEIGHT_ALBUM_INSIGHT', 75);
	define('CONST_NB_INSIGHT_PICTURES', 8);
	define('CONST_NB_COLUMNS_LIST_ALBUMS', 2);
	
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