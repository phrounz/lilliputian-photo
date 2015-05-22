<?php
	namespace Comments;
	error_reporting(E_ALL);
	require_once("conf.inc.php");

//----------------------------------------------------------------------------------------------------------------------------------------------
// public functions
//----------------------------------------------------------------------------------------------------------------------------------------------

function readComments($album, $media_id)
{
	return readCommentsFile_(getCommentFileFromMedia_($album, $media_id));
}

function insertNewComment($album, $media_id, $new_comment)
{
	$new_comment = str_replace("\r", '<br />', str_replace("\n", '<br />', str_replace("|", '&#124;', $new_comment)));
	
	if (!file_exists(CONST_COMMENTS_DIR)) mkdir(CONST_COMMENTS_DIR);
	if (!file_exists(CONST_COMMENTS_DIR."/$album")) mkdir(CONST_COMMENTS_DIR."/$album");
	
	$comments_file = getCommentFileFromMedia_($album, $media_id);
	$connected_user = $_SERVER['REMOTE_USER'] == CONST_ADMIN_USER ? '' : $_SERVER['REMOTE_USER'];
	file_put_contents($comments_file, (file_exists($comments_file) ? '|':'')."$connected_user=$new_comment", FILE_APPEND);
}

function deleteComment($album, $media_id, $comment_to_delete)
{
	deleteComment_($album, $media_id, $comment_to_delete);
}

//----------------------------------------------------------------------------------------------------------------------------------------------
// private functions
//----------------------------------------------------------------------------------------------------------------------------------------------

//----------------------------------------------

function readCommentsFile_($comments_file)
{
	$comments_array = array();
	if (file_exists($comments_file))
	{
		$contents = file_get_contents($comments_file);
		if ($contents != FALSE)
		{		
			foreach (explode('|', $contents) as $el)
			{
				if (strpos($el, '=') === FALSE)
				{
					array_push($comments_array, array( 'user' => '', 'comment' => $el ));
				}
				else
				{
					$tmp_contents_array = explode('=', $el);
					$user = array_shift($tmp_contents_array);
					array_push($comments_array, array( 'user' => $user, 'comment' => implode('=', $tmp_contents_array) ));
				}
			}
		}
	}
	return $comments_array;
}

//----------------------------------------------

function deleteComment_($album, $media_id, $comment_to_delete)
{
	$comments_file = getCommentFileFromMedia_($album, $media_id);
	$comments_array = readCommentsFile_($comments_file);
	$comment_to_delete_desc = $comments_array[$comment_to_delete];
	if ($comment_to_delete_desc['user'] != $_SERVER['REMOTE_USER'] && $_SERVER['REMOTE_USER'] != CONST_ADMIN_USER)
	{
		die('You are not allowed to do that. You are "'.$_SERVER['REMOTE_USER'].'" and this is a comment of "'.$comment_to_delete_desc['user'].'".');
	}
	else
	{
		$i = 0;
		$comments_agr=array();
		foreach ($comments_array as $comment)
		{
			if ($i != $comment_to_delete)
				array_push($comments_agr, $comment['user']==''?$comment['comment']:$comment['user'].'='.$comment['comment']);
			$i+=1;
		}
		
		if (count($comments_agr) == 0)
		{
			unlink($comments_file);	
			if (count(glob(dirname($comments_file)."/*"))==0)
			{
				rmdir(dirname($comments_file));
				if (count(glob(CONST_COMMENTS_DIR."/*"))==0)
				{
					rmdir(CONST_COMMENTS_DIR);
				}
			}
		}
		else
			file_put_contents($comments_file, implode('|', $comments_agr));
	}
}

//----------------------------------------------

function getCommentFileFromMedia_($album, $media_id)
{
	return CONST_COMMENTS_DIR."/$album/".basename($media_id, ".".pathinfo($media_id, PATHINFO_EXTENSION)).".txt";
}

//----------------------------------------------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------------------------------------------------

?>
