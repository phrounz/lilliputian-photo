<?php
	// general	
	define('CONST_ADMIN_USER', 'admin');
	define('CONST_DEFAULT_USER', 'default');
	define('CONST_MEDIA_DIR', './albums');
	define('CONST_ALBUM_CONF_DIR', './albums_conf');
	define('CONST_COMMENTS_DIR', './comments');
	define('CONST_THUMBNAILS_DIR', './thumbnails');
	define('CONST_USE_CACHE', true);
	define('CONST_CACHE_INDEX_DIR', './cache_index');
	define('CONST_CACHE_ALBUM_DIR', './cache_album');
	define('CONST_MAIN_TITLE', 'Photos &amp; videos');
	// additional security measure but disabled by default because does not work most of the time, depending of your apache configuration:
	define('CONST_HTPASSWD_PATH_TO_CHECK_PASSWORD', '');
	
	// album page (media thumbnails)
	define('CONST_WIDTH_THUMBNAIL', 200);
	define('CONST_HEIGHT_THUMBNAIL', 150);
	define('CONST_NB_CHARS_COMMENTS_INSIGHT', 100);
	
	// album list page
	define('CONST_WIDTH_ALBUM_INSIGHT', 100);
	define('CONST_HEIGHT_ALBUM_INSIGHT', 75);
	define('CONST_NB_INSIGHT_PICTURES', 6);
	define('CONST_NB_COLUMNS_LIST_ALBUMS', 2);
?>