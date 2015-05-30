<?php
	require_once("inc/conf.inc.php");
	require_once("inc/virtual_albums_conf.inc.php");
	require_once("inc/media_access.inc.php");
	
	// GET arguments
	if (!isset($_GET['valbum_id']) || !isset($_GET['media_id'])) die("missing arg");
	$valbum_id = $_GET['valbum_id'];
	$media_id = $_GET['media_id'];
	
	$valbum_array = VirtualAlbumsConf\listVirtualAlbums();
	$album = $valbum_array[$valbum_id]["album"];
	
	if (!file_exists(dirname(CONST_THUMBNAILS_SMALL_DIR))) mkdir(dirname(CONST_THUMBNAILS_SMALL_DIR));
	if (!file_exists(CONST_THUMBNAILS_SMALL_DIR)) mkdir(CONST_THUMBNAILS_SMALL_DIR);
	if (!file_exists(CONST_THUMBNAILS_LARGE_DIR)) mkdir(CONST_THUMBNAILS_LARGE_DIR);
	
	generateThumbnail(MediaAccess\getRealMediaFile($album, $media_id), MediaAccess\getSmallThumbAlbumDir($album), false);
	generateThumbnail(MediaAccess\getRealMediaFile($album, $media_id), MediaAccess\getLargeThumbAlbumDir($album), true);

	//---------------------------------------------------------------------------
	
	function generateThumbnail($media_file, $output_folder, $is_reduced_instead_of_thumb)
	{
		if (!file_exists($output_folder)) mkdir($output_folder);
		
		ini_set('memory_limit', '-1');// Allocate all necessary memory for the image.
		
		$ext = pathinfo($media_file, PATHINFO_EXTENSION);
		if ($ext == 'jpg' || $ext == 'JPG')
		{
			$img = new Zubrag_image;

			// maximum thumb side size
			$img->max_x        = $is_reduced_instead_of_thumb ? CONST_WIDTH_REDUCED_MEDIA : CONST_WIDTH_THUMBNAIL;
			$img->max_y        = $is_reduced_instead_of_thumb ? CONST_HEIGHT_REDUCED_MEDIA : CONST_HEIGHT_THUMBNAIL;
			// cut image before resizing. Set to 0 to skip this.
			$img->cut_x        = 0;
			$img->cut_y        = 0;
			
			$img->allow_extend_x = true;
			$img->allow_extend_y = false;
			// Quality for JPEG and PNG.
			// 0 (worst quality, smaller file) to 100 (best quality, bigger file)
			// Note: PNG quality is only supported starting PHP 5.1.2
			$img->quality      = 100;
			// save to file (true) or output to browser (false)
			$img->save_to_file = true;
			// resulting image type (1 = GIF, 2 = JPG, 3 = PNG)
			// enter code of the image type if you want override it
			// or set it to -1 to determine automatically
			$img->image_type   = 2;

			$output_file = $output_folder."/".basename($media_file, ".".$ext).".jpg";
			if (!file_exists($output_file))
			{
				echo "Generating $output_file from $media_file ... ";
				$img->GenerateThumbFile($media_file, "$output_file.lock");
				echo "ok.\n<br />";
				rename("$output_file.lock", $output_file);
			}
		}
		else
		{		
		}
	}
	
###############################################################
# Thumbnail Image Class for Thumbnail Generator
###############################################################
# For updates visit http://www.zubrag.com/scripts/
############################################################### 
// REQUIREMENTS:
// PHP 4.0.6 and GD 2.0.1 or later
// May not work with GIFs if GD2 library installed on your server 
// does not support GIF functions in full

class Zubrag_image {

  var $save_to_file = true;
  var $image_type = -1;
  var $quality = 100;
  var $max_x = 100;
  var $max_y = 100;
  var $cut_x = 0;
  var $cut_y = 0;
  var $allow_extend_x = false;
  var $allow_extend_y = false;
 
  function SaveImage($im, $filename) {
 
    $res = null;
 
    // ImageGIF is not included into some GD2 releases, so it might not work
    // output png if gifs are not supported
    if(($this->image_type == 1)  && !function_exists('imagegif')) $this->image_type = 3;

    switch ($this->image_type) {
      case 1:
        if ($this->save_to_file) {
          $res = ImageGIF($im,$filename);
        }
        else {
          header("Content-type: image/gif");
          $res = ImageGIF($im);
        }
        break;
      case 2:
        if ($this->save_to_file) {
          $res = ImageJPEG($im,$filename,$this->quality);
        }
        else {
          header("Content-type: image/jpeg");
          $res = ImageJPEG($im, NULL, $this->quality);
        }
        break;
      case 3:
        if (PHP_VERSION >= '5.1.2') {
          // Convert to PNG quality.
          // PNG quality: 0 (best quality, bigger file) to 9 (worst quality, smaller file)
          $quality = 9 - min( round($this->quality / 10), 9 );
          if ($this->save_to_file) {
            $res = ImagePNG($im, $filename, $quality);
          }
          else {
            header("Content-type: image/png");
            $res = ImagePNG($im, NULL, $quality);
          }
        }
        else {
          if ($this->save_to_file) {
            $res = ImagePNG($im, $filename);
          }
          else {
            header("Content-type: image/png");
            $res = ImagePNG($im);
          }
        }
        break;
    }
 
    return $res;
 
  }
 
  function ImageCreateFromType($type,$filename) {
   $im = null;
   switch ($type) {
     case 1:
       $im = ImageCreateFromGif($filename);
       break;
     case 2:
       $im = ImageCreateFromJpeg($filename);
       break;
     case 3:
       $im = ImageCreateFromPNG($filename);
       break;
    }
    return $im;
  }
 
  // generate thumb from image and save it
  function GenerateThumbFile($from_name, $to_name) {
 
    // if src is URL then download file first
    $temp = false;
    if (substr($from_name,0,7) == 'http://') {
      $tmpfname = tempnam("tmp/", "TmP-");
      $temp = @fopen($tmpfname, "w");
      if ($temp) {
        @fwrite($temp, @file_get_contents($from_name)) or die("Cannot download image");
        @fclose($temp);
        $from_name = $tmpfname;
      }
      else {
        die("Cannot create temp file");
      }
    }

    // check if file exists
    if (!file_exists($from_name)) die("Source image does not exist!");
    
    // get source image size (width/height/type)
    // orig_img_type 1 = GIF, 2 = JPG, 3 = PNG
    list($orig_x, $orig_y, $orig_img_type, $img_sizes) = @GetImageSize($from_name);

    // cut image if specified by user
    if ($this->cut_x > 0) $orig_x = min($this->cut_x, $orig_x);
    if ($this->cut_y > 0) $orig_y = min($this->cut_y, $orig_y);
 
    // should we override thumb image type?
    $this->image_type = ($this->image_type != -1 ? $this->image_type : $orig_img_type);
 
    // check for allowed image types
    if ($orig_img_type < 1 or $orig_img_type > 3) die("Image type not supported");
 
    if ($orig_x > $this->max_x or $orig_y > $this->max_y) {
 
      // resize
      $per_x = $orig_x / $this->max_x;
      $per_y = $orig_y / $this->max_y;
      if ($per_y > $per_x) {
        if (!$this->allow_extend_y) {
          $this->max_x = $orig_x / $per_y;
        } else {
          $this->max_y = $orig_y / $per_x;
        }
      }
      else {
	    if (!$this->allow_extend_x) {
          $this->max_y = $orig_y / $per_x;
		} else {
          $this->max_x = $orig_x / $per_y;
        }
      }
 
    }
    else {
      // keep original sizes, i.e. just copy
      if ($this->save_to_file) {
        @copy($from_name, $to_name);
      }
      else {
        switch ($this->image_type) {
          case 1:
              header("Content-type: image/gif");
              readfile($from_name);
            break;
          case 2:
              header("Content-type: image/jpeg");
              readfile($from_name);
            break;
          case 3:
              header("Content-type: image/png");
              readfile($from_name);
            break;
        }
      }
      return;
    }
 
    if ($this->image_type == 1) {
      // should use this function for gifs (gifs are palette images)
      $ni = imagecreate($this->max_x, $this->max_y);
    }
    else {
      // Create a new true color image
      $ni = ImageCreateTrueColor($this->max_x,$this->max_y);
    }
 
    // Fill image with white background (255,255,255)
    $white = imagecolorallocate($ni, 255, 255, 255);
    imagefilledrectangle( $ni, 0, 0, $this->max_x, $this->max_y, $white);
    // Create a new image from source file
    $im = $this->ImageCreateFromType($orig_img_type,$from_name);
    // Copy the palette from one image to another
    imagepalettecopy($ni,$im);
    // Copy and resize part of an image with resampling
    imagecopyresampled(
      $ni, $im,             // destination, source
      0, 0, 0, 0,           // dstX, dstY, srcX, srcY
      $this->max_x, $this->max_y,       // dstW, dstH
      $orig_x, $orig_y);    // srcW, srcH
 
    // save thumb file
    $this->SaveImage($ni, $to_name);
	echo "(saving) ";

    if($temp) {
      unlink($tmpfname); // this removes the file
    }

  }

}

?>
