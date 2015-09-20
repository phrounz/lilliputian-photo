<?php
	namespace VirtualAlbumsConf;
	error_reporting(E_ALL);
	require_once("conf.inc.php");
	
//----------------------------------------------------------------------------------------------------------------------------------------------
// public functions
//----------------------------------------------------------------------------------------------------------------------------------------------

// for the connected user (note: administrator is special and gets raw albums + all virtual albums of all users)
function listVirtualAlbums()
{
	$valbum_array = array();
	
	$connected_user = $_SERVER['REMOTE_USER'];
	
	if ($connected_user == CONST_ADMIN_USER)
	{
		foreach (glob(CONST_MEDIA_DIR."/*") as $album_folder)
		{
			$alb = basename($album_folder);
			array_push($valbum_array,
				array(
					'type' => 'ALBUM',
					'title' => $alb,
					'album' => $alb,
					'from_date' => null,
					'to_date' => null,
					'exclude_include_list' => '',
					'is_exclude' => true,
					'comments_permissions' => 'RWDA',
					'user' => CONST_ADMIN_USER));
		}
		if (file_exists(CONST_ALBUM_CONF_DIR))
		{
			foreach (glob(CONST_ALBUM_CONF_DIR."/*") as $album_conf_file)
			{
				foreach (readVirtualAlbumConfFile($album_conf_file) as $valbum_line)
				{
					array_push($valbum_array, $valbum_line);
				}
			}
		}
	}
	else
	{
		$album_conf_file = CONST_ALBUM_CONF_DIR."/".(file_exists(CONST_ALBUM_CONF_DIR."/$connected_user") ? "$connected_user" : CONST_DEFAULT_USER);
		$valbum_array = readVirtualAlbumConfFile($album_conf_file);
	}

	return $valbum_array;
}

//----------------------------------------------

function createVirtualAlbum($title, $album, $from_date, $to_date, $comments_permissions, $user, $album_thumb_picture, $valbum_add__exclude_include_list, $valbum_add__is_exclude)
{
	preg_replace('/[^\.a-zA-Z0-9_-]/s', '', $album_thumb_picture);
	preg_replace('/[^\.a-zA-Z0-9_-]/s', '', $valbum_add__exclude_include_list);
	preg_replace('/[^\.a-zA-Z0-9_-]/s', '', $valbum_add__is_exclude);
	$is_exclude = (((int)$valbum_add__is_exclude) ? 1 : 0);
	
	return file_put_contents(CONST_ALBUM_CONF_DIR."/$user", 
		"ALBUM|$title|$album|$from_date|$to_date|$comments_permissions|$album_thumb_picture|$valbum_add__exclude_include_list|$is_exclude\n"
		, FILE_APPEND) !== FALSE;
}

//----------------------------------------------

function createGroupTitle($title, $user)
{
	return file_put_contents(CONST_ALBUM_CONF_DIR."/$user", "GROUP_TITLE|$title\n", FILE_APPEND) !== FALSE;
}

//----------------------------------------------

function createNewUser($user)
{
	return file_put_contents(CONST_ALBUM_CONF_DIR."/$user", "", FILE_APPEND) !== FALSE;// (append avoids erasing data in case of a mistake)
}

//----------------------------------------------

function createDefaultUserIfNotExists()
{
	if (!file_exists(CONST_ALBUM_CONF_DIR))
	{
		mkdir(CONST_ALBUM_CONF_DIR);
		file_put_contents(CONST_ALBUM_CONF_DIR."/.htaccess", "Deny from all");
	}
	if (!file_exists(CONST_ALBUM_CONF_DIR."/".CONST_DEFAULT_USER))
	{
		file_put_contents(CONST_ALBUM_CONF_DIR."/".CONST_DEFAULT_USER, "", FILE_APPEND);
	}
}

//----------------------------------------------

function getUsers($skip_default)
{
	$users = array();
	foreach (glob(CONST_ALBUM_CONF_DIR."/*") as $album_conf_file)
		if (!($skip_default && basename($album_conf_file)==CONST_DEFAULT_USER))
			array_push($users, basename($album_conf_file));
	return $users;
}

//----------------------------------------------

function isUserConfEmpty($user)
{
	return file_get_contents(CONST_ALBUM_CONF_DIR."/$user") == "";
}

//----------------------------------------------

function removeUser($user)
{
	return unlink(CONST_ALBUM_CONF_DIR."/$user");
}

//----------------------------------------------

function removeVirtualAlbumOrTitle($title, $user)
{
	$handle = fopen(CONST_ALBUM_CONF_DIR."/$user", "r");
	$output = '';
	if (isset($handle))
	{
		while (($data = fgetcsv($handle, 1000, "|")) !== FALSE)
		{
			if (!($data[1] == $title))
			{
				$output .= implode('|', $data)."\n";
			}
		}
	}
	fclose($handle);
	return file_put_contents(CONST_ALBUM_CONF_DIR."/$user", $output) !== FALSE;
}

//----------------------------------------------

function reorderVirtualAlbumOrTitle($title, $user, $action)
{
	$handle = fopen(CONST_ALBUM_CONF_DIR."/$user", "r");
	$output = '';
	$lines = array();
	if (isset($handle))
	{
		while (($data = fgetcsv($handle, 1000, "|")) !== FALSE)
		{
			array_push($lines, $data);
		}
	}
	fclose($handle);
	
	$i=0;
	$current_group_index = 0;
	foreach ($lines as $line)
	{
		if ($line[0] == 'GROUP_TITLE')
		{
			$current_group_index = $i;
		}
		if ($line[1] == $title)
		{
			if ($action == 'MoveTop' && $i>0)
			{
				$out = array_splice($lines, $i, 1);
				array_splice($lines, 0, 0, $out);
				break;
			}
			elseif ($action == 'MoveBeginningGroup' && $i>0)
			{
				$out = array_splice($lines, $i, 1);
				array_splice($lines, $current_group_index+1, 0, $out);
				break;
			}
			elseif ($action == 'MoveUp' && $i>0)
			{
				$out = array_splice($lines, $i, 1);
				array_splice($lines, $i-1, 0, $out);
				break;
			}
			elseif ($action == 'MoveDown' && $i<count($lines))
			{
				$out = array_splice($lines, $i, 1);
				array_splice($lines, $i+1, 0, $out);
				break;
			}
			elseif ($action == 'MoveBottom' && $i<count($lines))
			{
				$out = array_splice($lines, $i, 1);
				array_splice($lines, count($lines), 0, $out);
				break;
			}
			else
			{
				print_r("ERROR: $action unknown");
			}
		}
		$i++;
	}
	foreach ($lines as $line)
	{
		$output .= implode('|', $line)."\n";
	}
	
	return file_put_contents(CONST_ALBUM_CONF_DIR."/$user", $output) !== FALSE;
}

//----------------------------------------------------------------------------------------------------------------------------------------------
// private function
//----------------------------------------------------------------------------------------------------------------------------------------------

$global_data1_for_filter = null;

function readVirtualAlbumConfFile($album_conf_file)
{
	global $global_data1_for_filter;
	
	$valbum_array = array();
	
	if (file_exists($album_conf_file))
	{
		$user = basename($album_conf_file);
		
		$handle = fopen($album_conf_file, "r");
		
		if (isset($handle))
		{
			while (($data = fgetcsv($handle, 1000, "|")) !== FALSE)
			{
				if ($data[0] == 'ALBUM')
				{
					array_push($valbum_array, 
						array( 
							'type' => $data[0], 
							'title' => $data[1], 
							'album' => $data[2], 
							'from_date' => $data[3], 
							'to_date' => $data[4], 
							'comments_permissions' => $data[5],
							'user' => $user,
							'album_thumb_picture' => $data[6],
							'exclude_include_list' => $data[7],
							'is_exclude' => ($data[8] ? true : false)
							));
				}
				else if ($data[0] == 'GROUP_TITLE') // add a title for a group of virtual albums
				{
					array_push($valbum_array, array( "type" => $data[0], "title" => $data[1], "user" => $user ));
				}
				else if ($data[0] == 'INCLUDE_FILE') // recursively include another album conf file
				{
					foreach (readVirtualAlbumConfFile($data[1]) as $valbum) array_push($valbum_array, $valbum);
				}
				else if ($data[0] == 'REMOVE') // remove a virtual album or a group title previously mentioned with 'ALBUM'
				{
					$global_data1_for_filter = $data[1];
					$valbum_array = array_filter($valbum_array, function ($a){ global $global_data1_for_filter;return $a['title'] != $global_data1_for_filter; });
				}
				else
				{
					die("Unknown ".$data[0]." in ".$album_conf_file);
				}
			}
		}
		fclose($handle);
	}
	
	return $valbum_array;
}

//----------------------------------------------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------------------------------------------------

?>
