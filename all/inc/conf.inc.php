<?php
	// general	
	define('CONST_ADMIN_USER', 'admin');
	define('CONST_DEFAULT_USER', 'default');
	define('CONST_MEDIA_DIR', './albums');
	define('CONST_ALBUM_CONF_DIR', './albums_conf');
	define('CONST_COMMENTS_DIR', './comments');
	define('CONST_THUMBNAILS_SMALL_DIR', './thumbnails/small');
	define('CONST_THUMBNAILS_LARGE_DIR', './thumbnails/large');
	define('CONST_MAIN_TITLE', 'Photos &amp; videos');
	// additional security measure but disabled by default because does not work most of the time, depending of your apache configuration:
	define('CONST_HTPASSWD_PATH_TO_CHECK_PASSWORD', '');	
	define('CONST_FILE_STATS', 'stats/stats.txt');
	
	// media page
	define('CONST_WIDTH_REDUCED_MEDIA', 800);
	define('CONST_HEIGHT_REDUCED_MEDIA', 600);
	
	// album page (media thumbnails)
	define('CONST_WIDTH_THUMBNAIL', 200);
	define('CONST_HEIGHT_THUMBNAIL', 150);
	define('CONST_NB_CHARS_COMMENTS_INSIGHT', 100);
	
	// album list page
	define('CONST_WIDTH_ALBUM_INSIGHT', 150);//100
	define('CONST_HEIGHT_ALBUM_INSIGHT', 112);//75
	define('CONST_NB_INSIGHT_PICTURES', 4);
	define('CONST_NB_COLUMNS_LIST_ALBUMS', 4);
	define('CONST_WIDTH_ALBUM_THUMB_PICTURE', 300);//200
	define('CONST_HEIGHT_ALBUM_THUMB_PICTURE', 225);//150
	define('CONST_WIDTH_ALBUM_INSIGHT_BOX', CONST_WIDTH_ALBUM_THUMB_PICTURE+20);//300
	define('CONST_HEIGHT_ALBUM_INSIGHT_BOX', CONST_HEIGHT_ALBUM_THUMB_PICTURE+130);//225
	
	function collapseId($user)
	{
		return 'collapse_user_'.rtrim(strtr(base64_encode($user), '+/', '-_'), '=');
	}
?>