 <?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	engine.class.php
//	Author: tim
// -------------------------------------------------------------
//	This file contains the main engine class. This is the core 
//	class responsible for recieving and dispatching requests
//	to the appropriate page-rendering code.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//  This is the core class responsible for recieving and 
//	dispatching requests to the appropriate page-rendering code.
// -------------------------------------------------------------
class Engine
{
	// Page rendering tracking.
	public $RenderStartTime;
	public $RenderingPage;

	// Sub-Systems.
	public $Logger;
	public $Settings;
	public $Cache;
	public $Database;
	
	// Language settings.
	public $Language;
	
	// Template settings.
	public $TwigLoader;
	public $TwigEnvironment;
		
	// -------------------------------------------------------------
	//  Constructs this engine class.
	//
	//	@param settings Settings instance determining how this engine
	//					instance should behave.
	// -------------------------------------------------------------
	public function __construct($settings)
	{
		// Store the start time for rendering.
		$this->RenderStartTime = microtime(true);
		$this->RenderingPage = false;
		
		// Store settings.
		$this->Logger   = new Logger($this);
		$this->Settings = $settings;
		
		// Work out base script URI?
		if ($this->Settings->PathRewriteMethod == "htaccess")
		{
			if (!file_exists(BASE_PATH . ".htaccess"))
			{
				$this->Logger->InternalError("Path rewriting set to htaccess, but no htaccess file could be found!");			
			}			
			else
			{
				define("BASE_SCRIPT_URI", BASE_URI_DIR . '/');
			}
		}
		else
		{		
			define("BASE_SCRIPT_URI", BASE_URI_DIR . '/index.php/');
		}
	
		// Include all extensions.
		foreach ($this->Settings->Extensions as $extension)
		{
			if (!file_exists(BASE_PATH . $extension))
			{
				$this->Logger->InternalError("Failed to find extension file: " . BASE_PATH . $extension);
			}
			else
			{
				require($extension);
			}
		}
	
		// Store viewers browser information.
		$this->Settings->UserAgentValues = BrowserHelper::ParseUserAgent($_SERVER['HTTP_USER_AGENT'], true);
		
		// Use mobile site if user has a mobile user-agent.
		$this->Settings->UseMobileSite = $this->Settings->UserAgentValues['is_mobile'];
		
		// Set timezone.
		date_default_timezone_set($this->Settings->TimeZone);
	
		// Load request variables.
		$this->InitRequestVariables();

		// Instantiate a new cache provider.
		$this->Cache = CacheProvider::CreateProvider($this, $this->Settings->CacheProvider);
	
		// Instantiate a new database provider.
		$this->Database = DatabaseProvider::CreateProvider($this, $this->Settings->DatabaseProvider);
				
		// Instantiate all hooks.
		HookProvider::CreateHooks($this);
		
		// Instantiate all page handlers.
		PageHandler::CreateHandlers($this);
		
		// Setup language system.
		$this->InitLanguage();
		
		// Setup template system.
		$this->InitTemplates();
	}
	
	// -------------------------------------------------------------
	//	Loads and sanitizes all request variables.
	// -------------------------------------------------------------
	public function InitRequestVariables()
	{
		// Store URI arguments.
		$this->Settings->URIArguments = array();		
		if (isset($_SERVER["PATH_INFO"]))
		{
			$this->Settings->URIArguments = explode("/", ltrim($_SERVER["PATH_INFO"], '/'));
		}
		
		// Clean up request variables and add them to settings.
		$vars = array_merge($_GET, $_POST);
		foreach ($vars as $key => $value)
		{
			$cleaned_value = StringHelper::CleanRequestVariable($value);			
			$this->Settings->RequestValues[$key] = $cleaned_value;
		}		
		
		// Add file uploads to settings.
		$this->Settings->RequestValues["files"] = array();
		foreach ($_FILES as $key => $value)
		{
			$cleaned_value = $value;
			$cleaned_value["name"]     = StringHelper::CleanRequestVariable($value["name"]);
			$cleaned_value["type"] 	   = StringHelper::CleanRequestVariable($value["type"]);
			$cleaned_value["size"] 	   = (int)$value["size"];
			$cleaned_value["tmp_name"] = StringHelper::CleanRequestVariable($value["tmp_name"]);
			$cleaned_value["error"]	   = (int)$value["error"];

			$this->Settings->RequestValues["files"][$key] = $cleaned_value;
		}		
		
		// Add cookies to settings.
		$this->Settings->RequestValues["cookies"] = array();
		foreach ($_COOKIE as $key => $value)
		{
			$cleaned_value = StringHelper::CleanRequestVariable($value);
			
			$this->Settings->RequestValues["cookies"][$key] = $cleaned_value;
		}		
	}
	
	// -------------------------------------------------------------
	//	Initializes the language sub-system.
	// -------------------------------------------------------------
	public function InitLanguage()
	{
		// Load language settings.
		$this->Language = new Language($this);
		
		// Load the english-us language file as this is our "base".
		$this->Language->LoadFile(BASE_PATH . $this->Settings->ThemePath . 'Languages/en-us.php');
		
		// Now load the translated strings for our current language.
		if ($this->Settings->LanguageName != "en-us")
		{
			$this->Language->LoadFile(BASE_PATH . $this->Settings->ThemePath . 'Languages/' . $this->Settings->LanguageName . '.php');
		}
	}
	
	// -------------------------------------------------------------
	//	Initializes the template sub-system.
	// -------------------------------------------------------------
	public function InitTemplates()
	{
		// Setup template engine.
		Twig_Autoloader::register();
		$this->TwigLoader 	   = new Twig_Loader_Filesystem($this->Settings->ThemePath . '/Templates/');
		$this->TwigEnvironment = new Twig_Environment($this->TwigLoader, array(
			'cache' 		=> $this->Settings->TemplateCacheDirectory,
			'auto_reload' 	=> $this->Settings->TemplateAutoReload
		));		
		
		// Add some general global settings.
		$this->TwigEnvironment->addGlobal("BASE_PATH", 		BASE_PATH);
		$this->TwigEnvironment->addGlobal("BASE_URI", 		BASE_URI);
		$this->TwigEnvironment->addGlobal("BASE_URI_DIR", 	BASE_URI_DIR);
		$this->TwigEnvironment->addGlobal("Settings", 		$this->Settings);
		
		// Add language-string retrieval function to templates.
		$function = new Twig_SimpleFunction('LANG', function ($key) {
			return $this->Language->Get($key);
		});
		$this->TwigEnvironment->addFunction($function);
	}
	
	// -------------------------------------------------------------
	//	Loads any general settings stored in the database.
	// -------------------------------------------------------------
	public function LoadDatabaseSettings()
	{
		$result = $this->Database->Query("select_global_settings");
		foreach ($result->Rows as $row)
		{
			$this->Settings->DatabaseSettings[$row['name']] = $row['value'];
		}
		
	}
	
	// -------------------------------------------------------------
	//  Core function, takes the users requests and does what is
	//	neccessary to fulfill it.
	//
	//	@param override_handler If set this will override the handler that 
	//				  			would normally be used to show this page.
	// -------------------------------------------------------------
	public function RenderPage($override_handler = null)
	{
		// Invoke the intitial event.
		HookProvider::InvokeEvent("OnBegin");

		// Invoke the intitial event.
		HookProvider::InvokeEvent("OnPreDatabaseConnection");

		// Connect to database.
		$this->Database->Connect();
				
		// Load settings from database.
		$this->LoadDatabaseSettings();
		
		// Invoke the intitial event.
		HookProvider::InvokeEvent("OnPostDatabaseConnection");

		// Invoke pre-render.
		HookProvider::InvokeEvent("OnPreRender");		
			
		// Override page handler.
		$this->RenderingPage = true;
		
		$handler = $override_handler;
		if ($handler == null)
		{
			$handler = PageHandler::FindHandlerForURI($this->Settings->URIArguments);
		}
		
		// Render the page!
		if ($handler != NULL)
		{
			$handler->RenderPage();		
		}
		else
		{
			$this->Logger->NotFoundError("The page requested could not be found.");
			return;
		}
		
		$this->RenderingPage = false;
		
		// Invoke the intitial event.
		HookProvider::InvokeEvent("OnPreDatabaseDisconnection");
		
		// Disconnect from database.
		$this->Database->Disconnect();
		
		// Invoke the intitial event.
		HookProvider::InvokeEvent("OnPostDatabaseDisconnection");
		
		// Invoke post-render.
		HookProvider::InvokeEvent("OnPostRender");			
		
		// Invoke the final event.
		HookProvider::InvokeEvent("OnFinish");	
	}
	
	// -------------------------------------------------------------
	//  Renders a template located at the given path using the
	//	given settings.
	//
	//	@param template_path Path to template to render.
	//	@param settings      Settings to pass to the template 
	//						 being rendered.
	// -------------------------------------------------------------
	public function RenderTemplate($template_path, $settings)
	{
		$template = NULL;
		$error_msg = "";
		try
		{
			// If we are rendering the mobile version of this site, use
			// the mobile template, not the normal one.
			if ($this->Settings->UseMobileSite == true)
			{
				$template_path = FileHelper::StripExtension($template_path) . ".mobile.tmpl";
			}
			
			$template = $this->TwigEnvironment->loadTemplate($template_path);
		}
		catch (Exception $e)
		{
			$this->Logger->InternalError("Could not find (or could not load) template '{$template_path}'.<br/><br/>Internal Error:<br/>" . str_replace("\n", "<br/>", (string)$e));
		}
		
		echo $template->render($settings);
	}
	
}