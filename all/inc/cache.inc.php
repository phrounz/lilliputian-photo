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
	else
	{
		$cache_file = isset($valbum_id) ? 
			CONST_CACHE_ALBUM_DIR."/".$_SERVER['REMOTE_USER']."/".$valbum_id : 
			CONST_CACHE_INDEX_DIR."/".$_SERVER['REMOTE_USER'];
		
		if (count($_POST) > 0)
		{
			if (file_exists($cache_file)) unlink($cache_file);
			return null;
		}
		else
		{
			if (file_exists($cache_file))
			{
				if (checkCacheIsOk_($cache_file, $valbum_id))
				{
					readfile($cache_file);
					exit(0);
				}
			}
			return $cache_file;
		}
	}
}

//----------------------------------------------

function clearAllCache()
{
	foreach (glob(CONST_CACHE_INDEX_DIR."/*") as $cache_file) unlink($cache_file);
	if (count(glob(CONST_CACHE_INDEX_DIR."/*"))==0) rmdir(CONST_CACHE_INDEX_DIR);
	
	foreach (glob(CONST_CACHE_ALBUM_DIR."/*/*") as $cache_file) unlink($cache_file);
	foreach (glob(CONST_CACHE_ALBUM_DIR."/*") as $cache_dir)
	{
		if (count(glob($cache_dir."/*"))==0) rmdir($cache_dir);
	}
	if (count(glob(CONST_CACHE_ALBUM_DIR."/*"))==0) rmdir(CONST_CACHE_ALBUM_DIR);
}

//----------------------------------------------

function finishCache($generate_cache_file, $cancel_cache_generation)
{
	if (isset($generate_cache_file))
	{
		$page = ob_get_contents();
		ob_end_clean();
		if ($cancel_cache_generation)
		{
			if (file_exists($generate_cache_file)) unlink($generate_cache_file);
		}
		else
		{
			if (!file_exists(dirname(dirname($generate_cache_file)))) mkdir(dirname(dirname($generate_cache_file)));
			if (!file_exists(dirname($generate_cache_file))) mkdir(dirname($generate_cache_file));
			file_put_contents($generate_cache_file, $page);
		}
		echo $page;
	}
}

//----------------------------------------------------------------------------------------------------------------------------------------------
// private function
//----------------------------------------------------------------------------------------------------------------------------------------------

function checkCacheIsOk_($cache_file , $valbum_id)
{
	$mtime_cache_file = filemtime($cache_file);
	foreach (array_merge(glob("inc/*"),glob("*.php"),glob(CONST_ALBUM_CONF_DIR."/*")) as $file_or_dir) //\MediaAccess\getAllAlbumsDirs(),
	{
		if (filemtime($file_or_dir) > $mtime_cache_file) return false;
	}
	if (isset($valbum_id))
	{
		if (file_exists(CONST_COMMENTS_DIR) && file_exists(CONST_COMMENTS_DIR."/$valbum_id") && filemtime(CONST_COMMENTS_DIR."/$valbum_id") > $mtime_cache_file)
			return false;
	}
	
	return true;
}

//----------------------------------------------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------------------------------------------------

?>
