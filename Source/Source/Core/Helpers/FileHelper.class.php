<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	filehelper.class.php
//	Author: tim
// -------------------------------------------------------------
//	Contains several commonly used helper functions for dealing
//	with files and directories.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//	Contains several commonly used helper functions for dealing
//	with files and directories.
// -------------------------------------------------------------
class FileHelper
{
	
	// -------------------------------------------------------------
	//	Works out the size in bytes of a directory.
	//
	//	@param dir 		 Directory to calculate size of.
	//	@param recursive If true function will recursively check 
	//					 sub-directory sizes.
	//
	//	@returns Size in bytes of directory.
	// -------------------------------------------------------------
	public static function DirectorySize($dir, $recursive = false)
	{
		$size = 0;
		$files = scandir($dir);		

		foreach ($files as $file)
		{
			$path = $dir . $file;
			if (is_dir($path))
			{
				if ($recursive == true &&
					$path != "." && 
					$path != "..")				
				{
					$size += DirectorySize($path, $recursive);
				}
			}
			else if (file_exists($path))
			{
				$size += filesize($path);
			}
		}
				
		return $size;
	}
	
	// -------------------------------------------------------------
	//	Strips trailing slashes from a path.
	//
	//	@param path	Path to remove slashes from.
	//
	//	@returns Path with slashes removed.
	// -------------------------------------------------------------
	public static function StripTrailingSlash($path)
	{
		return rtrim($path, "/\\");
	}
	
	// -------------------------------------------------------------
	//	Strips the extension from a path.
	//
	//	@param path	Path to remove extension from.
	//
	//	@returns Path with extension removed.
	// -------------------------------------------------------------	
	public static function StripExtension($path)
	{	
		$period_pos = strrpos($path, '.');
		
		if ($period_pos === FALSE)
		{
			return $path;
		}
		else
		{
			return substr($path, 0, $period_pos);
		}
	}
	
	// -------------------------------------------------------------
	//	Gets the extension from a path.
	//
	//	@param path	Path to get extension from.
	//
	//	@returns Extension of path removed.
	// -------------------------------------------------------------	
	public static function ExtractExtension($path)
	{	
		$period_pos = strrpos($path, '.');
		
		if ($period_pos === FALSE)
		{
			return "";
		}
		else
		{
			return substr($path, $period_pos + 1);
		}
	}
	
	// -------------------------------------------------------------
	//	Inserts a watermark onto the end of the path before the
	//	extension.
	//
	//	@param path	Path to add watermark to.
	//	@param mark	Wathermark to add.
	//
	//	@returns Path with watermark added.
	// -------------------------------------------------------------	
	public static function AddFileNameWatermark($path, $mark)
	{	
		$period_pos = strrpos($path, '.');
		
		if ($period_pos === FALSE)
		{
			return $path . $mark;
		}
		else
		{
			return  substr($path, 0, $period_pos) . $mark . substr($path, $period_pos);
		}
	}

	// -------------------------------------------------------------
	//	Sanitizes a filename so it only contains 0-9, a-z, . _ -.
	//
	//	@param text filename to sanitize.
	//
	//	@returns Sanitized version of text.
	// -------------------------------------------------------------
	public static function SanitizeFileName($text)
	{
		return preg_replace('/[^0-9a-z\.\_\-]/i', '', $text);
	}
	
}