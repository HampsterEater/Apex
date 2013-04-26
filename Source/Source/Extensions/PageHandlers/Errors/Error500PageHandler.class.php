 <?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	error500pagehandler.class.php
//	Author: tim
// -------------------------------------------------------------
//	This file contains the page handler for 500 errors.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//	This class handles 500 internal server errors.
//
//	URI for this handler is:
// 		/index.php/error/500
// -------------------------------------------------------------
class Error500PageHandler extends PageHandler
{

	// Engine that constructed this class.
	private $m_engine;

	// -------------------------------------------------------------
	//	If returns true then this provider is capable of being
	//	instantiated and used.
	// -------------------------------------------------------------
	public function IsSupported()
	{
		return true;
	}	
	
	// -------------------------------------------------------------
	//	If returns true then this provider will be responsible 
	//	for handling pages with the given URI.
	// -------------------------------------------------------------
	public function CanHandleURI($uri_arguments)
	{
		if (count($uri_arguments) == 2 &&
			$uri_arguments[0] 	  == "error" &&
			$uri_arguments[1]	  == "500")
		{
			return true;
		}
		
		return false;
	}
	
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
	//	Invoked when this page handler is responsible for rendering
	//	the current page.
	// -------------------------------------------------------------
	public function RenderPage($arguments = array())
	{
		$this->m_engine->RenderTemplate("Errors/500.tmpl", $arguments);
	}
	
}