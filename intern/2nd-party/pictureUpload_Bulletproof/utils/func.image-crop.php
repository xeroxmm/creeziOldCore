<?php
/**
 * Image Croping Function. 
 *
 * @author     Daniel, Simon <samayo@gmail.com>
 * @link       https://github.com/samayo/bulletproof
 * @copyright  Copyright (c) 2015 Simon Daniel
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */
namespace Bulletproof;

 function crop($image, $mimeType, $imgWidth, $imgHeight, $newWidth, $newHeight, $path){

    switch ($mimeType) {
        case "jpg":
        case "jpeg":
            $imageCreate = imagecreatefromjpeg($image);
            break;

        case "png":
            $imageCreate = imagecreatefrompng($image);
            break;

        case "gif":
            $imageCreate = imagecreatefromgif($image);
            break;

        default:
            throw new \Exception(" Only gif, jpg, jpeg and png files can be cropped ");
            break;
    }

    // The image offsets/coordination to crop the image.
    $widthTrim = ceil(($imgWidth - $newWidth) / 2);
    $heightTrim = ceil(($imgHeight - $newHeight) / 2);

    // Can't crop to a bigger size, ex: 
    // an image with 100X100 can not be cropped to 200X200. Image can only be cropped to smaller size.
    if ($widthTrim < 0 && $heightTrim < 0) {
        return ;
    }

    $temp = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled(
                $temp,
                $imageCreate,
                0,
                0,
                $widthTrim,
                $heightTrim,
                $newWidth,
                $newHeight,
                $newWidth,
                $newHeight
            );


    if (!$temp) {
        throw new \Exception("Failed to crop image. Please pass the right parameters");
    } else {
        imagejpeg($temp, $path, 90);
    }

}
function resize_crop_image($max_width, $max_height, $source_file, $dst_dir, $quality = 80){
    $imgsize = getimagesize($source_file);
    $width = $imgsize[0];
    $height = $imgsize[1];
    $mime = $imgsize['mime'];
 
    switch($mime){
        case 'image/gif':
            $image_create = "imagecreatefromgif";
            $image = "imagegif";
            break;
 
        case 'image/png':
            $image_create = "imagecreatefrompng";
            $image = "imagepng";
            $quality = 7;
            break;
 
        case 'image/jpeg':
            $image_create = "imagecreatefromjpeg";
            $image = "imagejpeg";
            $quality = 80;
            break;
 
 		case 'image/webp':
            if(!mimeWEBPenabled)
				return FALSE;
            $image_create = "imagecreatefromwebp";
            $image = "imagewebp";
            $quality = 80;
            break;
 
        default:
            return false;
            break;
    }
    
	if(mimeWEBPenabled){
		$image = "imagewebp";
		$quality = 80;
	}
	if($max_height > 0 && $max_width > 0){ 
	    $width_new = $height * $max_width / $max_height;
	    $height_new = $width * $max_height / $max_width;
	} else if($max_height > 0 && $max_width == 0){
		$height_new = $max_height;
		$width_new = (int)(($width / $height) * $max_height);
	} else if($max_height == 0 && $max_width > 0){
		$height_new = $max_width / ($width / $height);
		$width_new = $max_width;
	} else
		return FALSE;
	if($max_height > 0 && $max_width > 0){
		$dst_img = imagecreatetruecolor($max_width, $max_height);
		$src_img = $image_create($source_file);
	} else {
		$dst_img = imagecreatetruecolor($width_new, $height_new);
		$src_img = $image_create($source_file);
	}
    //if the new width is greater than the actual width of the image, then the height is too large and the rest cut off, or vice versa
    if($max_height > 0 && $max_width > 0){
	    if($width_new > $width){
	        //cut point by height
	        $h_point = (($height - $height_new) / 2);
	        //copy image
	        imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
	    }else{
	        //cut point by width
	        $w_point = (($width - $width_new) / 2);
	        imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
	    }
    } else {
    	imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $width_new, $height_new, $width, $height);
    }
    $image($dst_img, $dst_dir, $quality);
 
    if($dst_img)imagedestroy($dst_img);
    if($src_img)imagedestroy($src_img);
}
