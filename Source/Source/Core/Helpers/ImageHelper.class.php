<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	imagehelper.class.php
//	Author: tim
// -------------------------------------------------------------
//	Contains several commonly used helper functions for dealing
//	with image files.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//	Contains several commonly used helper functions for dealing
//	with image files.
// -------------------------------------------------------------
class ImageHelper
{
	
	// -------------------------------------------------------------
	//	Attempts to strip EXIF data from file if it exists.
	//
	//	@param path Path of image file to strip.
	// -------------------------------------------------------------
	public static function StripEXIF($path)
	{
		$size = getimagesize($path);
		if ($size[2] == IMAGETYPE_JPEG)
		{
			$image = imagecreatefromjpeg($path);
			if ($image != NULL)
			{
				imagejpeg($image, $path, 100);
				imagedestroy($image);
			}
		}
	}
	
	// -------------------------------------------------------------
	//	Returns true if image file contains EXIF data.
	//
	//	@param path Path of image file to check.
	//
	//	@returns True if file contains EXIF.
	// -------------------------------------------------------------
	public static function ContainsEXIF($path)
	{
		if (extension_loaded('exif'))
		{
			if (exif_read_data($path) != FALSE)
			{
				return true;
			}
		}
		return false;
	}
	
	// -------------------------------------------------------------
	//	Resizes an image size so it fits into the given width/height
	//	whilst maintaining aspect ratio.
	// -------------------------------------------------------------
	public static function ResizeKeepingAspectRatio($width, $height, $maxWidth, $maxHeight)
	{
		$newWidth 	= 0;
		$newHeight 	= 0;

		if ($width <= $maxWidth && $height <= $maxHeight)
		{
			return array($width, $height);
		}

		if ($width > $height)
		{
			$newWidth = $maxWidth;
			$newHeight = (int)($maxWidth * $height / $width);
			
			if ($newHeight > $maxHeight)
			{
				$newHeight = $maxHeight;
				$newWidth = (int)($maxHeight * $width / $height);
			}
		}
		elseif ($height > $width)
		{
			$newHeight = $maxHeight;
			$newWidth = (int)($maxHeight * $width / $height);
			
			if ($newWidth > $maxWidth)
			{
				$newWidth = $maxWidth;
				$newHeight = (int)($maxWidth * $height / $width);
			}
		}
		else
		{
			$newWidth = $maxWidth;
			$newHeight = $maxHeight;
		}
		
		return array($newWidth, $newHeight);
	}
		
	// -------------------------------------------------------------
	//	Creates a thumbnail of the given image that fits into the
	//	given maximum dimensions and saves it to a file.
	//
	//	@returns True on success.
	// -------------------------------------------------------------
	public static function CreateImageThumbnail($fileName, $thumbFileName, $maxWidth, $maxHeight)
	{
		$newWidth 	= 0;
		$newHeight 	= 0;
		
		$size 		= getimagesize($fileName);
		$width 		= $size[0];
		$height 	= $size[1];
		
		if ($width > $height)
		{
			$newWidth 	= $maxWidth;
			$newHeight 	= (int)($maxWidth * $height / $width);
			
			if ($newHeight > $maxHeight)
			{
				$newHeight 	= $maxHeight;
				$newWidth 	= (int)($maxHeight * $width / $height);
			}
		}
		elseif ($height > $width)
		{
			$newHeight 	= $maxHeight;
			$newWidth 	= (int)($maxHeight * $width / $height);
			
			if ($newWidth > $maxWidth)
			{
				$newWidth 	= $maxWidth;
				$newHeight 	= (int)($maxWidth * $height / $width);
			}
		}
		else
		{
			$newWidth 	= $maxWidth;
			$newHeight 	= $maxHeight;
		}
		
		// Load in original image.
		$sourceImage = null;
		if ($size[2] == IMAGETYPE_GIF)
		{
			$sourceImage = imagecreatefromgif($fileName);
		}
		else if ($size[2] == IMAGETYPE_JPEG)
		{
			$sourceImage = imagecreatefromjpeg($fileName);
		}
		else if ($size[2] == IMAGETYPE_PNG)
		{
			$sourceImage = imagecreatefrompng($fileName);
		}
		else
		{
			return false;
		}
		
		if ($sourceImage == null)
		{
			return false;
		}
			
		imagesavealpha($sourceImage, true);
		
		// Create a new image and place it into it.
		$destImage = imagecreatetruecolor($newWidth, $newHeight);
		imagesavealpha($destImage, true);
		
		$trans_colour = imagecolorallocatealpha($destImage, 255, 0, 255, 127);
		imagefill($destImage, 0, 0, $trans_colour);
		
		imagecopyresampled($destImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
		
		if ($size[2] == IMAGETYPE_GIF)
		{
			if (!imagegif($destImage, $thumbFileName))
				return false;
		}
		else if ($size[2] == IMAGETYPE_JPEG)
		{
			if (!imagejpeg($destImage, $thumbFileName))
				return false;
		}
		else if ($size[2] == IMAGETYPE_PNG)
		{
			if (!imagepng($destImage, $thumbFileName))
				return false;
		}
		else
		{
			return false;
		}
		
		imagedestroy($sourceImage);
		imagedestroy($destImage);
			
		return true;
	}
		
}