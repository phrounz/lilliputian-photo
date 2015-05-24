<?php
	namespace Cache;
	error_reporting(E_ALL);
	require_once("conf.inc.php");
	require_once("media_access.inc.php");
	require_once("virtual_albums_conf.inc.php");

//----------------------------------------------------------------------------------------------------------------------------------------------
// public functions
//----------------------------------------------------------------------------------------------------------------------------------------------

function checkAndUseCache($valbum_id, $media_id)
{
	if (isset($media_id))
	{
		// no cache yet for media page
		return null;
	}
	else if (isset($valbum_id))
	{
		$cache_file = CONST_CACHE_ALBUM_DIR."/".$_SERVER['REMOTE_USER']."/".$valbum_id;
		if (checkCacheIsOk_($cache_file, true))
		{
			readfile($cache_file);
			exit(0);
		}
		return count($_POST) == 0 ? $cache_file : null;
	}
	else
	{
		$cache_file = CONST_CACHE_INDEX_DIR."/".$_SERVER['REMOTE_USER'];
		if (checkCacheIsOk_($cache_file, false))
		{
			readfile($cache_file);
			exit(0);
		}
		return count($_POST) == 0 ? $cache_file : null;
	}
}

//----------------------------------------------------------------------------------------------------------------------------------------------
// private function
//----------------------------------------------------------------------------------------------------------------------------------------------

function checkCacheIsOk_($cache_file, $check_also_comments_and_thumbs)
{
	if (file_exists($cache_file))
	{
		if (count($_POST) > 0)
		{
			unlink($cache_file);
			return false;
		}
		$mtime_cache_file = filemtime($cache_file);
		foreach (array_merge(\MediaAccess\getAllAlbumsDirs(),glob("inc/*"),glob("*.php"),glob(CONST_ALBUM_CONF_DIR."/*")) as $file_or_dir)
		{
			if (filemtime($file_or_dir) > $mtime_cache_file) return false;
		}
		if ($check_also_comments_and_thumbs)
		{
			foreach (array_merge(glob(CONST_COMMENTS_DIR."/*"),glob(CONST_THUMBNAILS_DIR."/*")) as $comments_dir)
			{
				if (filemtime($file_or_dir) > $mtime_cache_file) return false;
			}
		}
		if (\VirtualAlbumsConf\getMTimeUserConf($_SERVER['REMOTE_USER']) >$mtime_cache_file) return false;
	}
	else
	{
		return false;
	}
	return true;
}

//----------------------------------------------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------------------------------------------------

?>
