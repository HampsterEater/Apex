<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	boardloginpagehandler.class.php
//	Author: tim
// -------------------------------------------------------------
//	This file contains the code for showing the board login
//	page for password protected boards.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//	This class handles showing board login page for password
//	protected boards.
//
//	URI for this handler is:
// 		/board-name/login
// -------------------------------------------------------------
class BoardLoginPageHandler extends PageHandler
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
			$uri_arguments[1] == "login")
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
		$this->m_engine->Member->AssertAllowedTo("view_board_login_page");
		
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
		if ($arguments['board']['password'] == "" ||
			(isset($_SESSION[$cookie_password_key]) && $arguments['board']['password'] != $_SESSION[$cookie_password_key]) ||
			$this->m_engine->Member->IsAllowedTo("bypass_passwords", $arguments['board']['id']))
		{		
			BrowserHelper::RedirectExit(BASE_SCRIPT_URI . $board_uri);
			return;
		}		

		// Delete old expired failed login attempts.
		$threshold 	 = $this->m_engine->Settings->DatabaseSettings['failed_login_threshold'];
		$expire_time = $this->m_engine->Settings->DatabaseSettings['failed_login_expire_time'];
		$this->m_engine->Database->Query("delete_expired_failed_login_attempts", array(":create_ip" => $_SERVER['REMOTE_ADDR'], ":min_time" => time() - $expire_time));	

		// Have we had to many failed login attempts recently?		
		$result = $this->m_engine->Database->Query("select_failed_login_attempts", array(":create_ip" => $_SERVER['REMOTE_ADDR']));	
		if (count($result->Rows) >= $threshold)
		{
			$arguments['error_type'] = 'timeout';
		}
	
		// Have we submitted the form?
		if ($arguments['error_type'] == '' &&
			isset($this->m_engine->Settings->RequestValues['password']))
		{
			$password = trim($this->m_engine->Settings->RequestValues['password']);

			if ($password == "")
			{
				$arguments['error_type'] = 'no_password';
				$this->m_engine->Logger->Log("Recieved attempt to login to board '{$board_uri}' but provided no password.");
			}
			else 
			{
				if ($password == $arguments['board']['password'])
				{
					session_start();
					$_SESSION[$cookie_password_key] = $password;
					session_write_close();
		
					BrowserHelper::RedirectExit(BASE_SCRIPT_URI . $board_uri);
				}
				else
				{
					$this->m_engine->Logger->Log("Recieved attempt to login to board '{$board_uri}' with invalid password.");
					$arguments['error_type'] = 'invalid_login';
				}
			}
		}
		
		// If we had an error (thats not a failed login timeout) then log a failed attempt.
		if ($arguments['error_type'] != '' &&
			$arguments['error_type'] != 'timeout')
		{
			$this->m_engine->Database->Query("insert_failed_login_attempt", array(":create_ip" => $_SERVER['REMOTE_ADDR'], ":create_time" => time()));	
		}
		
		// Render the template.
		$this->m_engine->RenderTemplate("BoardLogin.tmpl", $arguments);
	}
	
}