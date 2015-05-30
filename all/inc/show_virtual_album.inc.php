<?php
	namespace ShowVirtualAlbum;
	error_reporting(E_ALL);
	require_once("conf.inc.php");
	require_once("media_access.inc.php");
	require_once("media_infos.inc.php");
	require_once("comments.inc.php");

//----------------------------------------------------------------------------------------------------------------------------------------------
// public functions
//----------------------------------------------------------------------------------------------------------------------------------------------

function getListOfDays($album, $from_date, $to_date)
{
	$days_album = array();
	foreach (glob(\MediaAccess\getAlbumDir($album)."/*") as $media_file)
	{
		$ext = pathinfo($media_file, PATHINFO_EXTENSION);
		if (\MediaInfos\isReallyAMediaFile($media_file))
		{
			$date_file = \MediaInfos\getDateTaken($media_file);
			if ((!isset($from_date) || strcmp($date_file, $from_date) >= 0) && (!isset($to_date) || strcmp($date_file, $to_date) <= 0))
			{
				$day_file = substr($date_file, 0, 10);
				if (!isset($days_album[$day_file]))
				{
					$days_album[$day_file] = 0;
				}
				$days_album[$day_file] += 1;
			}
		}
	}
	ksort($days_album);
	return $days_album;
}

//----------------------------------------------

function getListOfDatePerMedias($album, $from_date, $to_date, $is_insight, &$is_cut)
{
	$date_media_files = array();
	$i = 0;
	$is_cut = false;
	$media_files = glob(\MediaAccess\getAlbumDir($album)."/*");
	foreach ($media_files as $media_file)
	{
		$ext = pathinfo($media_file, PATHINFO_EXTENSION);
		if (\MediaInfos\isReallyAMediaFile($media_file))
		{
			$date_file = \MediaInfos\getDateTaken($media_file);
			if ((!isset($from_date) || strcmp($date_file, $from_date) >= 0) && (!isset($to_date) || strcmp($date_file, $to_date) <= 0))
			{
				$date_media_files[$media_file] = $date_file;
				$i += 1;
			}
		}
		if ($is_insight && $i>=CONST_NB_INSIGHT_PICTURES)
		{
			if ($i<count($media_files)) $is_cut = true;
			break;
		}
	}
	
	asort($date_media_files);
	
	return $date_media_files;
}

//----------------------------------------------

function showVirtualAlbum($valbum_id, $album, $from_date, $to_date, $comments_permissions, $is_insight)
{
	global $cancel_cache_generation;
	$is_cut = false;
	$date_media_files = getListOfDatePerMedias($album, $from_date, $to_date, $is_insight, $is_cut);
	$day_mark = null;
	
	$i = 0;
	foreach($date_media_files as $media_file => $date_file)
	{
		$ext = pathinfo($media_file, PATHINFO_EXTENSION);
		$day = substr($date_file, 0, 10);
		$media_id = basename($media_file);
		showMediaThumb_($valbum_id, $album, $media_id, !$is_insight, !$is_insight && strpos($comments_permissions, 'R')!==FALSE);
		
		if (!file_exists(\MediaAccess\getRealThumbFileFromMedia($album, $media_id)) || !file_exists(\MediaAccess\getRealReducedFileFromMedia($album, $media_id)))
		{
			echo '<script type="text/javascript">media_ids_to_process.push("'.$media_id.'");</script>';
			$cancel_cache_generation = true;
		}

		$i++;
	}
	if ($is_cut)
	{
		echo "<img src='three_dots.png' alt='...' class='three_dots' />";
	}
	
	return $i;
}

//----------------------------------------------------------------------------------------------------------------------------------------------
// private functions
//----------------------------------------------------------------------------------------------------------------------------------------------

//----------------------------------------------

function showMediaThumb_($valbum_id, $album, $media_id, $add_link, $add_comment_insight)
{
	$comments_insight = "";
	$is_video = \MediaInfos\isMediaFileAVideo($media_id);
	//if ($is_video) $comments_insight = "[VIDEO] ";
	if ($add_comment_insight)
	{
		$comments_array = \Comments\readComments($album, $media_id);
		if (count($comments_array)>0)
		{
			$first_comment = array_shift($comments_array);
			$first_comment_comment = strlen($first_comment['comment']) > CONST_NB_CHARS_COMMENTS_INSIGHT ?
				substr($first_comment['comment'], 0, CONST_NB_CHARS_COMMENTS_INSIGHT).'[...]' : 
				$first_comment['comment'];
			$com_str = '';
			if ($first_comment['user'] == '') $com_str .= "description";
			$nb_comments = count($comments_array)+1;
			if ($first_comment['user'] == '' && $nb_comments-1 > 0) $com_str .= " &amp; ".strReplies_($nb_comments-1);
			else if ($first_comment['user'] != '' && $nb_comments > 0) $com_str .= "".strReplies_($nb_comments);
			$comments_insight .= "[$com_str] $first_comment_comment";
		}
	}
	if ($comments_insight != '') $comments_insight = "<span class='comments_insight'>$comments_insight</span>";
	
	echo ($add_link ? "<a class='media_thumb_link' href='".getMediaPageUrl($valbum_id, $media_id)."'>" : "")."$comments_insight";
	//if ($is_video)
	//	echo "<video class='insight_video' controls src='".getMediaUrl($valbum_id, $media_id)."' width='".CONST_WIDTH_THUMBNAIL."px;' preload='metadata' controls>";
	//else
	echo "<img class='".($is_video?'vid':'pic')."' src='".getMediaUrlThumb($valbum_id, $media_id)."' alt='(img)' />";
	echo "".($add_link?"</a>":"")."\n";
}

//----------------------------------------------

function strReplies_($nb) { return $nb>1 ? "$nb replies" : "$nb reply"; }

//----------------------------------------------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------------------------------------------------

?>
