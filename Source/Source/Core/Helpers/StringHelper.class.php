<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	stringhelper.class.php
//	Author: tim
// -------------------------------------------------------------
//	Contains several commonly used helper functions for dealing
//	with strings.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//	Contains several commonly used helper functions for dealing
//	with strings.
// -------------------------------------------------------------
class StringHelper
{
	
	// -------------------------------------------------------------
	//	Cleans up a variable that has been recieved from as part
	//	of a request. Typically this means removing null bytes, or
	//	any other security risks.
	//
	//	@param input Value recieved as input.
	//
	//	@returns Cleaned up version of $input.
	// -------------------------------------------------------------
	public static function CleanRequestVariable($input)
	{
		// Strip all null bytes, as they are most likely poison attacks.
		// http://hakipedia.com/index.php/Poison_Null_Byte		
		$input = str_replace(chr(0), '', $input);
		
		// *should* be safe to go.
		return $input;
	}
	
	// -------------------------------------------------------------
	//	Removes all path elements from a string. / \ .. . and %
	//
	//	@param path Value to have elements removed from.
	//
	//	@returns Cleaned up version of $path.
	// -------------------------------------------------------------
	public static function RemovePathElements($path)
	{
		$path = str_replace("\\", "", $path);
		$path = str_replace("/", "", $path);
		$path = str_replace("..", "", $path);
		$path = str_replace(".", "", $path);
		$path = str_replace("%", "", $path);
		return $path;
	}
	
	// -------------------------------------------------------------
	//	Formats a user-entered string. This function deals with 
	//	santizing a string for output to a browser and also parses
	//	bbcode.
	//
	//	@param input Value to be formatted.
	//
	//	@returns Formatted version of $input.
	// -------------------------------------------------------------
	public static function FormatUserString($input)
	{
		// Clean up html entities, preventing XSS.
		$input = htmlspecialchars($input);
	
		// Format BBCode.
		$input = StringHelper::FormatBBCode($input);
	
		// Insert <br/>'s instead of new lines.
		$input = nl2br($input);
		
		// First of all, clean up all html entities (prevent 
		// *should* be safe to go.
		return $input;
	}
	
	// -------------------------------------------------------------
	//	Replaces all BBCode instances in a string with the 
	//	appropriate html code.
	//
	//	@param input Value to be formatted.
	//
	//	@returns Formatted version of $input.
	// -------------------------------------------------------------
	public static function FormatBBCode($input)
	{
		// Check for any important bbcode.
		$count 	   = -1;
		$tag_index = 0;
		while ($count != 0)
		{
			$bb_replace = array
				(               
					'/\[spoiler\](.*?)\[\/spoiler\]/is',
					'/\[b\](.*?)\[\/b\]/is',
					'/\[i\](.*?)\[\/i\]/is',
					'/\[s\](.*?)\[\/s\]/is',
					'/\[u\](.*?)\[\/u\]/is',
					'/\[code\](.*?)\[\/code\]/is',
					'/\[color=([A-Za-z0-9]*?)\](.*?)\[\/color\]/is',
					'/\[color=#([A-Fa-f0-9]*?)\](.*?)\[\/color\]/is',
					'/&amp;#([A-Za-z0-9]*?);/is',
				);
			$bb_replacements = array
				(
					'<span class="SpoilerBox">$1</span>',
					'<b>$1</b>',
					'<i>$1</i>',
					'<s>$1</s>',
					'<u>$1</u>',
					'<tt>$1</tt>',
					'<span style="color: $1;">$2</span>',	
					'<span style="color: #$1;">$2</span>',			
					'&#$1;',
				);
				
			$input = preg_replace($bb_replace, $bb_replacements, $input, 1, $count);
			$tag_index++;
		}
		
		return $input;
	}
	
	// -------------------------------------------------------------
	//	Turns all URL's in the text into hyperlinks.
	//
	//	@param input Value to be formatted.
	//
	//	@returns Formatted version of $input.
	// -------------------------------------------------------------
	public static function HyperlinkURLs($input)
	{
		return preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', 
							'<a href="$1">$1</a>', 
							$input);	
	}
	
}