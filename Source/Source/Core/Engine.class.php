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
	
	// Board information.
	public $BoardCategories;
		
	// Login state.
	public $SessionID;
	public $Member;
		
	// -------------------------------------------------------------
	//  Constructs this engine class.
	//
	//	@param settings Settings instance determining how this engine
	//					instance should behave.
	// -------------------------------------------------------------
	public function __construct($settings)
	{
		// Setup default values.
		$this->BoardCategories = array();
		
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
				define("BASE_SCRIPT_URI", BASE_URI_DIR );
			}
		}
		else
		{		
			define("BASE_SCRIPT_URI", BASE_URI_DIR . 'index.php/');
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
		
		// Load request variables.
		$this->InitRequestVariables();

		// Load preferences.
		$this->LoadPreferences();

		// Set timezone.
		date_default_timezone_set($this->Settings->TimeZone);
	
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
			$this->Settings->URIArguments = explode("/", rtrim(ltrim($_SERVER["PATH_INFO"], '/'), '/'));
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
			if ($key == $this->Settings->CookieName)
			{
				$cleaned_value = @json_decode(StringHelper::CleanRequestVariable($value));		
				foreach ($cleaned_value as $vk => $vv)
				{
					$this->Settings->RequestValues["cookies"][$vk] = $vv;
				}				 
			}
		}				
	}
	
	// -------------------------------------------------------------
	//	Loads the users preferences.
	// -------------------------------------------------------------
	public function LoadPreferences()
	{
		// User preferences not set? Set defaults then.
		if (!isset($this->Settings->RequestValues["cookies"]['theme']) ||
			!isset($this->Settings->RequestValues["cookies"]['timezone']) ||
			!isset($this->Settings->RequestValues["cookies"]['use_mobile']) ||
			!isset($this->Settings->RequestValues["cookies"]['language']))
		{
			$this->Settings->RequestValues["cookies"]['theme'] 		= $this->Settings->Theme;
			$this->Settings->RequestValues["cookies"]['timezone'] 	= $this->Settings->TimeZone;
			$this->Settings->RequestValues["cookies"]['use_mobile'] = $this->Settings->UseMobileSite;
			$this->Settings->RequestValues["cookies"]['language'] 	= $this->Settings->LanguageName;
			$this->StoreCookieSettings();
		}

		// Load user specified theme?
		if (isset($this->Settings->RequestValues["cookies"]['theme']))
		{
			$this->Settings->Theme 	   = StringHelper::RemovePathElements($this->Settings->RequestValues["cookies"]['theme']);
			$this->Settings->ThemePath = $this->Settings->ThemeDirectory . $this->Settings->Theme . '/';
		}
		
		// Load timezone.
		if (isset($this->Settings->RequestValues["cookies"]['theme']))
		{
			$this->Settings->TimeZone = $this->Settings->RequestValues["cookies"]['timezone'];
		}
		
		// Load language.
		if (isset($this->Settings->RequestValues["cookies"]['language']))
		{
			$this->Settings->LanguageName = $this->Settings->RequestValues["cookies"]['language'];
		}
		
		// Load mobile.
		if (isset($this->Settings->RequestValues["cookies"]['use_mobile']))
		{
			$this->Settings->UseMobileSite = $this->Settings->RequestValues["cookies"]['use_mobile'];
		}
	}
	
	// -------------------------------------------------------------
	//	Stores any changed cookie settings.
	// -------------------------------------------------------------
	public function StoreCookieSettings()
	{
		$name = $this->Settings->CookieName;
		$val  = json_encode($this->Settings->RequestValues["cookies"]);
		
		setcookie($name, $val, time() + 60 * 60 * 24 * 365, '/');
		
		$this->LoadPreferences();
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
			'cache' 			=> $this->Settings->TemplateCacheDirectory,
			'auto_reload' 		=> $this->Settings->TemplateAutoReload,
			'strict_variables' 	=> true
		));		
		
		// Add some general global settings.
		$this->TwigEnvironment->addGlobal("BASE_PATH", 			BASE_PATH);
		$this->TwigEnvironment->addGlobal("BASE_URI", 			BASE_URI);
		$this->TwigEnvironment->addGlobal("BASE_URI_DIR", 		BASE_URI_DIR);
		$this->TwigEnvironment->addGlobal("BASE_SCRIPT_URI", 	BASE_SCRIPT_URI);
		$this->TwigEnvironment->addGlobal("THEME_DIR_URI", 		BASE_URI_DIR . $this->Settings->ThemePath);
		$this->TwigEnvironment->addGlobal("Settings", 			$this->Settings);
		$this->TwigEnvironment->addGlobal("Engine", 			$this);
		
		// Add LANG function which allows the template to get a language
		// specific string with optiomal formatting pattern.
		$function = new Twig_SimpleFunction('LANG', 
			function ($key) 
			{
				$args 	 = func_get_args();
				$pattern = $this->Language->Get($key);
				
				if (count($args) == 1)
				{
					return $pattern;
				}
				else
				{
					array_shift($args);
					return vsprintf($pattern, $args);
				}
			}
		);
		$this->TwigEnvironment->addFunction($function);

		// Add microtime function, used mainly for getting the 
		// templates generation timestamp.
		$function = new Twig_SimpleFunction('microtime', 
			function () 
			{
				return microtime(true);
			}
		);
		$this->TwigEnvironment->addFunction($function);
	}
	
	// -------------------------------------------------------------
	//	Loads any general settings stored in the database.
	// -------------------------------------------------------------
	public function LoadDatabaseSettings()
	{
		// Load global settings.
		$result = $this->Database->Query("select_global_settings");
		foreach ($result->Rows as $row)
		{
			$this->Settings->DatabaseSettings[$row['name']] = $row['value'];
		}
	}
	
	// -------------------------------------------------------------
	//	Loads generation information about what boards
	//	are on this site.
	// -------------------------------------------------------------
	public function LoadBoardInformation()
	{
		// Load board categories.
		$result = $this->Database->Query("select_board_categories");		
		$this->Settings->PageSettings['board_categories'] = array();
		
		foreach ($result->Rows as $row)
		{
			array_push($this->Settings->PageSettings['board_categories'], $row);
		}
		
		// Load boards.
		$result = $this->Database->Query("select_boards");		
		$this->Settings->PageSettings['boards'] = array();
		
		foreach ($result->Rows as $row)
		{
			array_push($this->Settings->PageSettings['boards'], $row);
		}
	}	
	
	// -------------------------------------------------------------
	//	Loads the users login state from cookies/database.
	// -------------------------------------------------------------
	public function LoadLoginState()
	{
		session_start();
		$this->SessionID = session_id();
		
		$member_id = 0;

		// If we have a given member ID, load that state.
		if (isset($_SESSION['member_id']))
		{
			$this->Member = new Member($this, $_SESSION['member_id']);
			if ($this->Member->LoadFromDatabase() == false ||
				$this->Member->Settings['can_log_in'] == false)
			{
				$this->Member = null;
			}
		}
		
		// Otherwise login as a guest.
		if ($this->Member == null)
		{
			$this->Member = new Member($this, $this->Settings->DatabaseSettings['visitor_member_id']);
			if ($this->Member->LoadFromDatabase() == false)
			{
				$this->Logger->InternalError("Database entry for visitor_member_id points to non-existant member (database possibly corrupt?).");
			}
		}
		
		// Perform security check.
		$this->SessionSecurityCheck();
		
		// Add session variable to templating system.		
		$this->TwigEnvironment->addGlobal("SESSION_ID", $this->SessionID);
		
		session_write_close();
	}
	
	// -------------------------------------------------------------
	//	Basically this checks if we are accepting any POST/GET/FIlES
	//	variables, if we are, then it makes sure we have recieved
	//	the correct session id with said variables.
	//
	//	This makes sure people can't create a fake form on another
	//	site that submits, say, management requests to our site and
	// 	tricking a member into using the form.
	// -------------------------------------------------------------
	public function SessionSecurityCheck()
	{
		if (count($_GET) > 0 || count($_POST) > 0 || count($_FILES) > 0)
		{
			if (isset($this->Settings->RequestValues['SESSION']) == false ||
				$this->Settings->RequestValues['SESSION'] != $this->SessionID)
			{
				$this->Logger->InternalError("Session ID security check failed.<br/>
				The action you attempted to perform has been rejected.<br/>
				If this action was intentional, please try again.");
			}			
		}		
	}	
	
	// -------------------------------------------------------------
	//	Checks if a user can perform the given permission on the 
	//	given board.
	// -------------------------------------------------------------
	public function IsAllowedTo($action, $board_id = -1)
	{
		return $this->Member->IsAllowedTo($action, $board_id);
	}	
	
	// -------------------------------------------------------------
	//	Returns true if the user is logged in.
	// -------------------------------------------------------------
	public function IsLoggedIn()
	{
		return ($this->Member->IsVisitor == false);
	}	
	
	// -------------------------------------------------------------
	//	Returns true if the user is logged in.
	// -------------------------------------------------------------
	public function LoginAsMember($id)
	{
		session_start();
		$_SESSION['member_id'] = $id;
		session_write_close();
		
		$this->LoadLoginState();
	}	
	
	// -------------------------------------------------------------
	//	Logs the user out.
	// -------------------------------------------------------------
	public function Logout()
	{
		session_start();
		unset($_SESSION['member_id']);
		session_write_close();
		
		$this->LoadLoginState();
	}		
	
	// -------------------------------------------------------------
	//	Returns information for the board located at the given uri.
	// -------------------------------------------------------------
	public function GetBoardByUri($uri)
	{
		foreach ($this->Settings->PageSettings['boards'] as $board)
		{
			if ($board['url'] == $uri)
			{
				return $board;
			}
		}
		return null;
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
				
		// Load all information we need from database.
		$this->LoadDatabaseSettings();
		$this->LoadBoardInformation();
		$this->LoadLoginState();
		
		// Invoke the intitial event.
		HookProvider::InvokeEvent("OnPostDatabaseConnection");

		// Invoke pre-render.
		HookProvider::InvokeEvent("OnPreRender");		
			
		// Override page handler.
		$this->RenderingPage = true;
		
		// Find the handler we want to use.
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
		
		// Merge template settings with page settings.
		$this->Settings->PageSettings = array_merge($this->Settings->PageSettings, $settings);
		
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
		
		echo $template->render($this->Settings->PageSettings);
	}
	
}