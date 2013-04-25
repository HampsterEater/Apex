<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	settings.class.php
//	Author: tim
// -------------------------------------------------------------
//	Contains the settings class which encapsulates all settings
//	that can vary the behaviour of this software. The main 
//	purpose of this is to be parsed to Engine.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
// Contains all the settings used to get this imageboard up 
// and running.
// -------------------------------------------------------------
class Settings 
{

	// -------------------------------------------------------------
	//	These settings are filled in at runtime by the engine.
	//	You don't want to change any of these yourself.
	// -------------------------------------------------------------
	
	// Request settings.
	public $RequestValues			= array();
	
	// User agent settings.
	public $UserAgentValues			= array();
		
	// Contains all database settings.
	public $DatabaseSettings		= array();
		
	// Contains all plain URI arguments.
	public $URIArguments			= array();

	// -------------------------------------------------------------
	//	These settings are defaults and can/will be overridden by
	//	user preferences.
	// -------------------------------------------------------------

	// Theme settings.
	public $Theme					= "Default";
	public $ThemeDirectory			= "Themes/";
	public $ThemePath				= "Themes/Default/";

	// Language settings.
	public $LanguageName			= "en-us";
	
	// Mobile site settings.		
	public $UseMobileSite			= false;
	
	// Timezone settings.
	public $TimeZone				= "Europe/London";
	
	// -------------------------------------------------------------
	//	These settings are static so change to whatever you want.
	// -------------------------------------------------------------
	
	// Template settings.
	public $TemplateCacheDirectory	= "Cache/Templates/";
	public $TemplateAutoReload		= true;
	
	// Database settings.
	public $DatabaseHost 			= "127.0.0.1";	// ProTip: localhost is slow as shit during connection when IPv6 is enabled.
	public $DatabaseName 			= "apex";
	public $DatabasePassword 		= "herpderp";
	public $DatabaseUsername 		= "apex";
	public $DatabaseProvider 		= "MySQLDatabaseProvider";
	
	// This value can either be htaccess or nothing.
	// If its htaccess and a .htaccess file exists all
	// url's will be based like so:
	//		http://domain.com/b/thread/234234
	// If its not, then domains will be based like this;
	//		http://domain.com/index.php/b/thread/234234
	public $PathRewriteMethod		= "htaccess";

	// Contains a list of non-core classes to include when
	// running the software. 
	public $Extensions				= array(	
											// Cache providers.
											"Source/Extensions/CacheProviders/DiskCacheProvider.class.php",
											"Source/Extensions/CacheProviders/APCCacheProvider.class.php",
											
											// Database providers.
											"Source/Extensions/DatabaseProviders/MySQLDatabaseProvider.class.php",
											
											// Hooks.
											"Source/Extensions/HookProviders/PageCacheHookProvider.class.php",
											
											// Page handlers.
											"Source/Extensions/PageHandlers/Errors/Error404PageHandler.class.php",
											"Source/Extensions/PageHandlers/Errors/Error500PageHandler.class.php",
										);	
										
	// Cache settings, some of these settings may or may not
	// be used, it depends which cache provider is activated.
	public $CacheProvider 		= "DiskCacheProvider";
	public $CacheTimeToLive		= 3600000; 	  // 1 hour
	public $CacheDirectory		= "Cache/";   // 1 hour
	public $CacheMaxSize		= 536870912;  // 512MB

}