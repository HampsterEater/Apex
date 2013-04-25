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
		$input = str_replace($input, chr(0), '');
		
		// *should* be safe to go.
		return $input;
	}

}