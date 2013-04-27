<?php
 
// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	language.php
//	Author: tim
// -------------------------------------------------------------
//	This file contains the base class that is responsible for loading
//	and displaying individual language strings in templates.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//	This file is the base class that is responsible for loading
//	and displaying individual language strings in templates.
// -------------------------------------------------------------
class Language 
{
	// Engine that instantiated this class.
	public $m_engine;
	
	// An associative array of all strings supplied by the language.
	public $Strings = array();
	
	// -------------------------------------------------------------
	//  Constructs this class.
	//
	//	@param engine Instance of engine that constructed this
	//				  class.
	// -------------------------------------------------------------
	public function __construct($engine)
	{
		$this->m_engine = $engine;
	}
	
	// -------------------------------------------------------------
	//	When calling this returns a string 
	// -------------------------------------------------------------
	public function LoadFile($lang_file)
	{
		if (!file_exists($lang_file))
		{
			$this->m_engine->Logger->InternalError("Could not load language file '{$lang_file}'.");
		}
		
		// This is a bit of trickery to make the language files simpler. It allows us
		// to declare a string in the language file just like this;
		//		LANG["X"] = "Y";
		//
		$LANG = array();
		include($lang_file);
		$this->Strings = $LANG;
	}

	// -------------------------------------------------------------
	//	When calling this returns a string 
	// -------------------------------------------------------------
	public function Get($key)
	{
		if (isset($this->Strings[$key]))
		{
			return $this->Strings[$key];
		}
		else
		{
			return "[Language String Not Found: " . $key . "]";
		}
	}
}