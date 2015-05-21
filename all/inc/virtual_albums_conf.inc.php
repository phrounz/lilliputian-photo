<?php
	namespace VirtualAlbumsConf;
	error_reporting(E_ALL);
	require_once("conf.inc.php");
	
//----------------------------------------------------------------------------------------------------------------------------------------------
// public function
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
					'from_date' => "0",
					'to_date' => "INFINITE",
					'user' => CONST_ADMIN_USER));
		}
		foreach (glob("*.albums_conf") as $album_conf_file)
		{
			foreach (readVirtualAlbumConfFile($album_conf_file) as $valbum_line)
			{
				array_push($valbum_array, $valbum_line);
			}
		}
	}
	else
	{
		$album_conf_file = file_exists("$connected_user.albums_conf") ? "$connected_user.albums_conf" : CONST_DEFAULT_USER.".albums_conf";
		$valbum_array = readVirtualAlbumConfFile($album_conf_file);
	}

	return $valbum_array;
}

//----------------------------------------------

function createVirtualAlbum($title, $album, $from_date, $to_date, $user)
{
	file_put_contents("$user.albums_conf", "ALBUM|$title|$album|$from_date|$to_date\n", FILE_APPEND);
}

//----------------------------------------------

function createGroupTitle($title, $user)
{
	file_put_contents("$user.albums_conf", "GROUP_TITLE|$title\n", FILE_APPEND);
}

//----------------------------------------------

function createNewUser($user)
{
	file_put_contents("$user.albums_conf", "", FILE_APPEND);// (append avoids erasing data in case of a mistake)
}

//----------------------------------------------

function createDefaultUserIfNotExists()
{
	if (!file_exists(CONST_DEFAULT_USER.".albums_conf")) file_put_contents(CONST_DEFAULT_USER.".albums_conf", "", FILE_APPEND);
}

//----------------------------------------------

function getUsers()
{
	$users = array();
	foreach (glob("*.albums_conf") as $album_conf_file) array_push($users, basename($album_conf_file, ".albums_conf"));
	return $users;
}

//----------------------------------------------

function removeUser($user)
{
	unlink("$user.albums_conf");
}

//----------------------------------------------

function removeVirtualAlbumOrTitle($title, $user)
{
	$handle = fopen("$user.albums_conf", "r");
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
	file_put_contents("$user.albums_conf", $output);
}

//----------------------------------------------------------------------------------------------------------------------------------------------
// private function
//----------------------------------------------------------------------------------------------------------------------------------------------

$global_data1_for_filter = null;

function readVirtualAlbumConfFile($album_conf_file)
{
	global $global_data1_for_filter;
	
	$valbum_array = array();
	$user = basename($album_conf_file, ".albums_conf");
	
	$handle = fopen($album_conf_file, "r");
	
	if (isset($handle))
	{
		while (($data = fgetcsv($handle, 1000, "|")) !== FALSE)
		{
			if ($data[0] == 'ALBUM')
			{
				array_push($valbum_array, 
					array( 
						"type" => $data[0], 
						"title" => $data[1], 
						"album" => $data[2], 
						"from_date" => $data[3], 
						"to_date" => $data[4], 
						"user" => $user
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
			else if ($data[0] == 'REMOVE') // remove an album previously mentioned with 'ALBUM'
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
	return $valbum_array;
}

//----------------------------------------------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------------------------------------------------

?>
