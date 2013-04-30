<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	index.php
//	Author: tim
// -------------------------------------------------------------
//	Entry point to the application, all actions
//	in this software are interfaced through
//	this file.
// -------------------------------------------------------------

// Enable full strict error reporting!
error_reporting(E_ALL | E_STRICT);

// This define is used by other scripts to make sure they have been
// included from this script and not accessed directly.
define("ENTRY_POINT", true);

// Some general installation constants. 
// Just the software signature, shows up on default error pages.
define("SOFTWARE_SIGNATURE", "Apex ImageBoard Software");	
																			
// Base Path: /var/www/domain.com/public_html/apex/
define("BASE_PATH", 		 dirname(__FILE__) . DIRECTORY_SEPARATOR);
																	
// Base URI: http://domain.com/apex/index.php
define("BASE_URI",  		 "http" . (!empty($_SERVER['HTTPS']) ? "s" : "") . "://" . 														// http://										
								$_SERVER['SERVER_NAME'] . 																					//		  hostname						
								(!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 80 ? ':' . $_SERVER['SERVER_PORT'] : '') . 	//			 	  :port
									$_SERVER['SCRIPT_NAME']);						
// Full URI: http://domain.com/apex/index.php/herp/derp
define("FULL_URI",  		 "http" . (!empty($_SERVER['HTTPS']) ? "s" : "") . "://" . 														// http://										
								$_SERVER['SERVER_NAME'] . 																					//		  hostname						
								(!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 80 ? ':' . $_SERVER['SERVER_PORT'] : '') . 	//			 	  :port
									$_SERVER['REQUEST_URI']);	
// Base URI Directory: http://domain.com/apex/
define("BASE_URI_DIR",  	 dirname(BASE_URI) . '/');																		

// Base Script Path (No Rewriting): http://domain.com/apex/index.php/
// Base Script Path (Rewriting):    http://domain.com/apex/ 
// This is defined by the engine, its just here for reference.
// define("BASE_SCRIPT_URI", ???);

// Check our installation is valid.
if (file_exists(BASE_PATH . "check.php") &&
	$_SERVER['SERVER_NAME'] != "127.0.0.1") // Do not do checks if we are working on local-installs, helps with debugging :3.
{
	require_once(BASE_PATH . "check.php");
}

// Include library source code.
require(BASE_PATH . "Source/Core/Libraries/Twig/Autoloader.php");
require(BASE_PATH . "Source/Core/Libraries/Recaptcha/recaptchalib.php");

// Include helper files.
require(BASE_PATH . "Source/Core/Helpers/ImageHelper.class.php");
require(BASE_PATH . "Source/Core/Helpers/BrowserHelper.class.php");
require(BASE_PATH . "Source/Core/Helpers/StringHelper.class.php");
require(BASE_PATH . "Source/Core/Helpers/FileHelper.class.php");
require(BASE_PATH . "Source/Core/Helpers/ReflectionHelper.class.php");

// Include core files.
require(BASE_PATH . "Source/Core/Settings.class.php");
require(BASE_PATH . "Source/Core/Language.class.php");
require(BASE_PATH . "Source/Core/Engine.class.php");
require(BASE_PATH . "Source/Core/Logger.class.php");
require(BASE_PATH . "Source/Core/Member.class.php");
require(BASE_PATH . "Source/Core/DatabaseProvider.class.php");
require(BASE_PATH . "Source/Core/CacheProvider.class.php");
require(BASE_PATH . "Source/Core/HookProvider.class.php");
require(BASE_PATH . "Source/Core/PageHandler.class.php");

// Boot up the rendering engine.
// Specifically not using globals here to make sure nobody gets tempted to access
// the engine instance in that way.
(new Engine(new Settings()))->RenderPage();
