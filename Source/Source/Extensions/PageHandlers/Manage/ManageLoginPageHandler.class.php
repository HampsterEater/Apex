<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	manageloginpagehandler.class.php
//	Author: tim
// -------------------------------------------------------------
//	This file contains the page handler for the "login" page in 
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
// 		/index.php/manage/login
// -------------------------------------------------------------
class ManageLoginPageHandler extends PageHandler
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
			$uri_arguments[0] == "manage" &&
			$uri_arguments[1] == "login")
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
		// Are we even logged in?
		if ($this->m_engine->IsLoggedIn())
		{
			BrowserHelper::RedirectExit(BASE_SCRIPT_URI . 'manage');
		}
	
		$arguments = array_merge($arguments,
			array
			(
				'error_type' => '',
			)		
		);	
	
		// Have we submitted the form?
		if (isset($this->m_engine->Settings->RequestValues['username']) &&
			isset($this->m_engine->Settings->RequestValues['password']))
		{
			$username = trim($this->m_engine->Settings->RequestValues['username']);
			$password = trim($this->m_engine->Settings->RequestValues['password']);

			if ($username == "")
			{
				$arguments['error_type'] = 'no_username';
			}
			else if ($password == "")
			{
				$arguments['error_type'] = 'no_password';
			}
			else 
			{
				$result = $this->m_engine->Database->Query("select_member_by_username", array(":username" => $username));
				
				if (count($result->Rows) != 1)
				{
					$arguments['error_type'] = 'invalid_login';
				}	
				else
				{
					$member = $result->Rows[0];
					if ($member['can_log_in'] != true)
					{
						$arguments['error_type'] = 'account_disabled';
					}
					else
					{
						$hashed = hash("sha512", $password . $member['password_salt']);
						if ($hashed == $member['password'])
						{
							$this->m_engine->LoginAsMember($member['id']);
							BrowserHelper::RedirectExit(BASE_SCRIPT_URI . "manage/");
						}
						else
						{
							$arguments['error_type'] = 'invalid_login';
						}
					}
				}
			}
		}
	
		// Render the template.
		$this->m_engine->RenderTemplate("Manage/Login.tmpl", $arguments);
	}
	
}