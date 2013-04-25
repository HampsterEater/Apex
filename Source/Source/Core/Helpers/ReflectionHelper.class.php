<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	reflectionhelper.class.php
//	Author: tim
// -------------------------------------------------------------
//	Contains several commonly used helper functions for dealing
//	with reflection.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//	Contains several commonly used helper functions for dealing
//	with sreflection.
// -------------------------------------------------------------
class ReflectionHelper
{
	
	// -------------------------------------------------------------
	//	Gets a list of all subclasses of a given class.
	//
	//	@param input Name or instance of class that sub-classes
	//				 are to derive from.
	//
	//	@returns Array of names of subclass.
	// -------------------------------------------------------------
	public static function GetSubClasses($input)
	{		
		$result = array();

		$classes = get_declared_classes();
		foreach ($classes as $class)
		{
			if (is_subclass_of($class, $input))
			{
				array_push($result, $class);
			}
		}
		
		return $result;
	}

}