<?php
	error_reporting(E_ALL);
	require_once("inc/conf.inc.php");
	require_once("inc/virtual_albums_conf.inc.php");
	require_once("inc/show_virtual_album.inc.php");
	require_once("inc/media_access.inc.php");

	if ($_SERVER['REMOTE_USER'] == CONST_ADMIN_USER)
	{
		$valbum_array = \VirtualAlbumsConf\listVirtualAlbums();
		generateAllThumbsAndReducedPictures_($valbum_array);
	}
	
	function generateAllThumbsAndReducedPictures_($valbum_array)
	{
		foreach ($valbum_array as $valbum_id => $valbum)
		{
			if (isset($valbum) && $valbum['type'] == 'ALBUM' && $valbum['user'] == CONST_ADMIN_USER)
			{
				$album = $valbum['album'];
				$is_cut = false;
				foreach (\ShowVirtualAlbum\getListOfDatePerMedias($album, $valbum["from_date"], $valbum["to_date"], false, $is_cut) as $media_file => $date)
				{
					$media_id = basename($media_file);
					
					if (!file_exists(\MediaAccess\getRealSmallThumbFromMedia($album, $media_id)) || !file_exists(\MediaAccess\getRealLargeThumbFromMedia($album, $media_id)))
					{
						echo 'media_ids.push("'.$media_id.'");valbum_ids.push("'.$valbum_id.'");'."\n";
					}
				}
			}
		}
	}

?>
