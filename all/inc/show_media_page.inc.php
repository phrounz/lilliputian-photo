<?php
	namespace ShowMediaPage;
	error_reporting(E_ALL);
	require_once("conf.inc.php");
	require_once("media_infos.inc.php");
	require_once("media_access.inc.php");
	require_once("comments.inc.php");
	
//----------------------------------------------

function showMediaPage($valbum_id, $valbum, $media_id)
{
	$media_html = \MediaInfos\isMediaFileAVideo($media_id) ?
		"<video id='the_media' src='".\MediaAccess\getMediaUrl($valbum_id, $media_id)."' controls width='100%' />" :
		"<a href='".\MediaAccess\getMediaUrl($valbum_id, $media_id)."'><img id='the_media' src='".\MediaAccess\getMediaUrlReduced($valbum_id, $media_id)."' alt='' style='height: 750px;'/></a>";
	
	$all_commenting = '';
	$php_this_media = \MediaAccess\getMediaPageUrl($valbum_id, $media_id);
	
	$valbum_user_mod = $_SERVER['REMOTE_USER'] == CONST_ADMIN_USER ? '' : $_SERVER['REMOTE_USER'];

	if (strpos($valbum['comments_permissions'], 'R')!==FALSE)
	{
		$i = 0;
		foreach (\Comments\readComments($valbum['album'], $media_id) as $comment)
		{
			$span_delete = '';
			if (($valbum['comments_permissions'] == 'RWD' && $valbum_user_mod == $comment['user']) || $valbum['comments_permissions']=='RWDA')
			{
				$span_delete = "<form style='float: right;' action='$php_this_media' method='POST'>"
					."<input type='hidden' name='comment_to_delete' value='$i' />"
					."<input type='submit' value='Delete' />"
					."</form>";
			}
			$all_commenting .= "\n<div class='comment_box'>".($comment['user']==''?'':'<b>'.$comment['user'].': </b>').$comment['comment']."$span_delete</div><br />\n";
			$i += 1;
		}
		
		if (strpos($valbum['comments_permissions'], 'RW')!==FALSE)
		{
			$all_commenting .= ""
				."<div class='comment_box'><form action='$php_this_media' method='POST'>"
				.'<textarea rows="5" name="new_comment"></textarea>'
				.'<br /><input type="submit" value="Submit comment" />'
				.'</form></div>';
		}
	}
	
	$all_commenting .= "<br /><br />Orientation: "
		."<a href='$php_this_media&amp;anti_rot'>-90&deg;</a> / "
		."<a href='$php_this_media'>normal</a> / "
		."<a href='$php_this_media&amp;rot'>90&deg;</a> / "
		."<a href='$php_this_media&amp;inverse_rot'>180&deg;</a>";
	
	$all_commenting .= "\n<div><a href='?q=$valbum_id&amp;img=$media_id&amp;next'>Next picture or video</a></div>\n";
	
	echo "\n<table width='100%'><tr><td>".$media_html."</td><td class='media_file_page_right'>".$all_commenting."</td></tr></table>\n";
}

//----------------------------------------------

?>
