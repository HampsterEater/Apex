<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	boardlogoutpagehandler.class.php
//	Author: tim
// -------------------------------------------------------------
//	This file contains the code for showing the board logout
//	page for password protected boards.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//	This class handles showing board logout page for password
//	protected boards.
//
//	URI for this handler is:
// 		/board-name/logout
// -------------------------------------------------------------
class BoardLogOutPageHandler extends PageHandler
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
			$uri_arguments[1] == "logout")
		{
			$board_name = $uri_arguments[0];
		
			// Check first argument is a board name.
			foreach ($this->m_engine->Settings->PageSettings['boards'] as $board)
			{
				if ($board['url'] == $board_name)
				{
					return true;
				}				
			}
			
			return false;
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
		$arguments = array_merge($arguments,
			array
			(
				'error_type' => '',
			)		
		);	
		
		// Check permissions.
		$this->m_engine->Member->AssertAllowedTo("view_board_logout_page");
		
		$uri_arguments			= $this->m_engine->Settings->URIArguments;
		$board_uri  			= $uri_arguments[0];
		$arguments['board'] 	= null;
		
		// Load board settings.
		$arguments['board'] = $this->m_engine->GetBoardByUri($board_uri);
		if ($arguments['board'] == null)
		{
			$this->m_engine->Logger->InternalError("Could not retrieve board information (for uri /{$board_uri}/) from database.");
		}
		
		// Work out password cookie.
		$cookie_password_key = 'board_' . ($arguments['board']['id']) . '_password';

		// Does this board actually require a password?
		if (isset($_SESSION[$cookie_password_key]) == false)
		{		
			BrowserHelper::RedirectExit(BASE_SCRIPT_URI . $board_uri);
			return;
		}		

		// Get rid of password!
		session_start();
		$_SESSION[$cookie_password_key] = $password;
		session_write_close();

		BrowserHelper::RedirectExit(BASE_SCRIPT_URI . $board_uri);	
	}
	
}