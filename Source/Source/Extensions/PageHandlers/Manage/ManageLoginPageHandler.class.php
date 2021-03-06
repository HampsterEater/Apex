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
		// Check permissions.
		$this->m_engine->Member->AssertAllowedTo("view_login_page");
		
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
			isset($this->m_engine->Settings->RequestValues['username']) &&
			isset($this->m_engine->Settings->RequestValues['password']))
		{
			$username = trim($this->m_engine->Settings->RequestValues['username']);
			$password = trim($this->m_engine->Settings->RequestValues['password']);

			if ($username == "")
			{
				$arguments['error_type'] = 'no_username';
				$this->m_engine->Logger->Log("Recieved attempt to login to account '{$username}' but provided no username.");
			}
			else if ($password == "")
			{
				$arguments['error_type'] = 'no_password';
				$this->m_engine->Logger->Log("Recieved attempt to login to account '{$username}' but provided no password.");
			}
			else 
			{
				$result = $this->m_engine->Database->Query("select_member_by_username", array(":username" => $username));
				
				if (count($result->Rows) != 1)
				{
					$arguments['error_type'] = 'invalid_login';
					$this->m_engine->Logger->Log("Recieved attempt to login to account '{$username}' but username dosen't exist.");
				}	
				else
				{
					$member = $result->Rows[0];
					if ($member['can_log_in'] != true)
					{
						$arguments['error_type'] = 'account_disabled';
						$this->m_engine->Logger->Log("Recieved attempt to login to account '{$username}' but account is disabled.");
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
							$this->m_engine->Logger->Log("Recieved attempt to login to account '{$username}' with invalid password.");
							$arguments['error_type'] = 'invalid_login';
						}
					}
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
		$this->m_engine->RenderTemplate("Manage/Login.tmpl", $arguments);
	}
	
}