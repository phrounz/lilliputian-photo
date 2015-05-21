<?php
	namespace MediaInfos;
	error_reporting(E_ALL);
	
//----------------------------------------------------------------------------------------------------------------------------------------------
// public functions
//----------------------------------------------------------------------------------------------------------------------------------------------

function getDateTaken($media_file)
{
	$date_taken = null;
	$ext = pathinfo($media_file, PATHINFO_EXTENSION);
	$media_file_without_ext = dirname($media_file)."/".basename($media_file, ".".$ext);
	$thm_file = "$media_file_without_ext.THM";
	$jpg_helper_file = "$media_file_without_ext.JPG";
	
	if (preg_match('/^\w+_(\d\d\d\d)(\d\d)(\d\d)_(\d\d)(\d\d)(\d\d)\W/', basename($media_file), $matches))
		$date_taken = $matches[1].':'.$matches[2].':'.$matches[3].' '.$matches[4].':'.$matches[5].':'.$matches[6];
	else if (preg_match('/^(\d\d\d\d)-(\d\d)-(\d\d)[^\d].*/', basename($media_file), $matches))
		$date_taken = $matches[1].':'.$matches[2].':'.$matches[3].' 00:00:00';
	else if (($ext != 'jpg' && $ext != 'JPG') && file_exists($thm_file))
		$date_taken = getExifDateTaken_($thm_file);
	else if ($ext == 'MOV' && file_exists($jpg_helper_file))
		$date_taken = getExifDateTaken_($jpg_helper_file);
	else if ($ext == 'jpg' || $ext == 'JPG')
		$date_taken = getExifDateTaken_($media_file);
	else
		$date_taken = date("Y:m:d H:i:s", filemtime($media_file));
	
	return $date_taken;
}

//----------------------------------------------

function isReallyAMediaFile($media_file)
{
	$ext = pathinfo($media_file, PATHINFO_EXTENSION);
	return $ext != 'THM' && $ext != 'txt' && basename($media_file) != 'Thumbs.db' && !($ext == 'JPG' && file_exists(dirname($media_file)."/".basename($media_file, ".".$ext).".MOV"));
}

//----------------------------------------------

function isMediaFileAVideo($media_file)
{
	$ext = pathinfo($media_file, PATHINFO_EXTENSION);
	return !($ext == 'jpg' || $ext == 'JPG');
}

//----------------------------------------------

function getMimeType($media_filename) // note: it only analyses the filename, not the contents
{
	$ext = pathinfo($media_filename, PATHINFO_EXTENSION);
	if ($ext == "jpg" || $ext == "JPG") $ext = "jpeg";
	if ($ext == 'mov' || $ext == 'MOV') $ext = "quicktime";
	return (isMediaFileAVideo($media_filename)?"video/$ext":"image/$ext");
}

//----------------------------------------------------------------------------------------------------------------------------------------------
// private function
//----------------------------------------------------------------------------------------------------------------------------------------------

function getExifDateTaken_($jpg_file)
{
	$exif = exif_read_data($jpg_file, 0, true);
	if (isset($_GET['all_exif']))
	{
		foreach ($exif as $key => $section) {foreach ($section as $name => $val) echo "$key.$name: $val<br />\n";}
	}
	if (isset($exif['EXIF']) && isset($exif['EXIF']['DateTimeOriginal'])) return $exif['EXIF']['DateTimeOriginal'];
	return null;
}

//----------------------------------------------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------------------------------------------------
?>
