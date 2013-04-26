 <?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	homepagehandler.class.php
//	Author: tim
// -------------------------------------------------------------
//	This file contains the page handler for the "home" index
//	page. Showing news/faq/etc
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//	This class handles showing the "home" index page. 
//	News/FAQ/etc.
//
//	URI for this handler is:
// 		/index.php
// -------------------------------------------------------------
class HomePageHandler extends PageHandler
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
		if (count($uri_arguments) == 0)
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
		$arguments['news_categories'] = array();
		$arguments['news_items'] = array();

		// Load all news categories.
		$result = $this->m_engine->Database->Query("select_news_categories");
		foreach ($result->Rows as $row)
		{
			array_push($arguments['news_categories'], $row);
		}
		
		// Load other pages.
		$result = $this->m_engine->Database->Query("select_news_items");
		foreach ($result->Rows as $row)
		{
			array_push($arguments['news_items'], $row);
		}
	
		// Render the template.
		$this->m_engine->RenderTemplate("Home.tmpl", $arguments);
	}
	
}