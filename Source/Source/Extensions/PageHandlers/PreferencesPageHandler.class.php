<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	preferencespagehandler.class.php
//	Author: tim
// -------------------------------------------------------------
//	This file contains the code for showing the 
//	users preferences page.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//	This class handles showing the preferences page.
//
//	URI for this handler is:
// 		/preferences
// -------------------------------------------------------------
class PreferencesPageHandler extends PageHandler
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
		if (count($uri_arguments) == 1 ||
			$uri_arguments[0] == "preferences")
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
		// Get list of timezones.
		$arguments['timezones'] = timezone_identifiers_list();
		
		// Get list of all themes.
		$result = $this->m_engine->Database->Query("select_themes");
		$arguments['themes'] = $result->Rows;
		
		// Get list of all languages.
		$result = $this->m_engine->Database->Query("select_languages");
		$arguments['languages'] = $result->Rows;
		
		// Apply settings.
		if (isset($this->m_engine->Settings->RequestValues['theme']) &&
			isset($this->m_engine->Settings->RequestValues['language']) &&
			isset($this->m_engine->Settings->RequestValues['timezone']))
		{
			$this->m_engine->Settings->RequestValues['cookies']['theme'] 		= $this->m_engine->Settings->RequestValues['theme'];
			$this->m_engine->Settings->RequestValues['cookies']['language'] 	= $this->m_engine->Settings->RequestValues['language'];
			$this->m_engine->Settings->RequestValues['cookies']['timezone'] 	= $this->m_engine->Settings->RequestValues['timezone'];
			$this->m_engine->Settings->RequestValues['cookies']['use_mobile'] 	= isset($this->m_engine->Settings->RequestValues['use_mobile']);
			$this->m_engine->StoreCookieSettings();
		}
		
		// Render the template.
		$this->m_engine->RenderTemplate("Preferences.tmpl", $arguments);
	}
	
}