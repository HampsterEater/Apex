<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	pagecachehookprovider.class.php
//	Author: tim
// -------------------------------------------------------------
//	This file contains a class that caches pages and serves 
//	the cached pages to users when they are available. 
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//	This class caches pages and serves the cached pages to users
//	when they are available. Cached pages are all served before
//	the database is conencted to, this reduces the strain on
//	the server.
// -------------------------------------------------------------
class PageCacheHookProvider extends HookProvider
{

	// Engine instance that instantiated us.
	private $m_engine;
	
	// -------------------------------------------------------------
	//	If returns true then this provider is capable of being
	//	instantiated and used.
	// -------------------------------------------------------------
	public function IsSupported()
	{
		return ($this->m_engine->Cache != null);
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
	//	Returns true if this request can be cached.
	// -------------------------------------------------------------
	private function CanCacheRequest()
	{
		// Do not cache if any dynamic variables have 
		// been passed to us.
		if (count($_GET) > 0 || count($_POST) > 0 || count($_FILES) > 0)
		{
			return false;
		}
		
		// Do not cache any of the management pages.
		if (count($settings->URIArguments) > 0 &&
			$settings->URIArguments[0] == "manage")
		{
			return false;
		}
		
		return true;
	}
	
	// -------------------------------------------------------------
	//	Returns the key used to store/retrieve this request from
	//	the cache.
	// -------------------------------------------------------------
	private function GetRequestCacheKey()
	{
		$settings = $this->m_engine->Settings;
	
		// Use URI arguments as base for key.
		$key = implode("/", $settings->URIArguments);
	
		// Use preference as part of the key as well.
		$key .= "?mobile=" 		. ($settings->UseMobileSite ? "1" : "0");
		$key .= "&theme=" 		. ($settings->Theme);
		$key .= "&language=" 	. ($settings->LanguageName);
		$key .= "&timezone=" 	. ($settings->TimeZone);		
		
		return $key;
	}
	
	// -------------------------------------------------------------
	//	Invoked before any work is done to generate the page.
	// -------------------------------------------------------------
	public function OnBegin()
	{
		// Can we cache this request?
		if (!$this->CanCacheRequest())
		{
			return;
		}
		
		// Server from cache if available?
		$key = $this->GetRequestCacheKey();
		$page = $this->m_engine->Cache->Get($key);
		if ($page != NULL)
		{
			echo $page;
			exit(0);
		}
		
		// Not available, being output buffering.
		ob_start();
	}
	
	// -------------------------------------------------------------
	//	Invoked when all work is done to generate a page.
	// -------------------------------------------------------------
	public function OnFinish()
	{
		// Can we cache this request?
		if (!$this->CanCacheRequest())
		{
			return;
		}
		
		// Try and cache this rendered page.
		$page = ob_get_contents();
		$key  = $this->GetRequestCacheKey();
		$this->m_engine->Cache->Set($key, $page);
	
		// Finish output buffering and flush to user.
		ob_end_clean();
	}
		
}