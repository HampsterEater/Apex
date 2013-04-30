<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	managehomepagehandler.class.php
//	Author: tim
// -------------------------------------------------------------
//	This file contains the page handler for the "home" page in 
//	the management area.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//	This class handles showing the "home" page in the 
//	management area.
//
//	URI for this handler is:
// 		/index.php/manage
// -------------------------------------------------------------
class ManageHomePageHandler extends PageHandler
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
		if (count($uri_arguments) == 1 &&
			$uri_arguments[0] == "manage")
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
		// Check permissions.
		$this->m_engine->Member->AssertAllowedTo("view_management_page");
		
		// If we are not logged in, redirect to login page.
		if ($this->m_engine->IsLoggedIn() == false)
		{
			BrowserHelper::RedirectExit(BASE_SCRIPT_URI . "manage/login");
			return;
		}
	
		// Render the template.
		$this->m_engine->RenderTemplate("Manage/Home.tmpl", $arguments);
	}
	
}