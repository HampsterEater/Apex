<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	managechangepasswordpagehandler.class.php
//	Author: tim
// -------------------------------------------------------------
//	This file contains the page handler for the "change password" 
//	page in the management area.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//	This class handles showing the "change password" page in the 
//	management area.
//
//	URI for this handler is:
// 		/index.php/manage/changepassword
// -------------------------------------------------------------
class ManageChangePasswordPageHandler extends PageHandler
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
			$uri_arguments[1] == "changepassword")
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
		$this->m_engine->Member->AssertAllowedTo("view_change_password_page");
		
		$arguments = array_merge($arguments,
			array
			(
				'error_type' => '',
				'success' => false,
			)		
		);	
		
		// Are we even logged in?
		if (!$this->m_engine->IsLoggedIn())
		{
			BrowserHelper::RedirectExit(BASE_SCRIPT_URI);
		}
	
		// Have we submitted the form?
		if (isset($this->m_engine->Settings->RequestValues['old_password']) &&
			isset($this->m_engine->Settings->RequestValues['confirm_password']) &&
			isset($this->m_engine->Settings->RequestValues['new_password']))
		{
			$member				= $this->m_engine->Member->Settings;
			$old_password 		= trim($this->m_engine->Settings->RequestValues['old_password']);
			$new_password 		= trim($this->m_engine->Settings->RequestValues['new_password']);
			$confirm_password 	= trim($this->m_engine->Settings->RequestValues['confirm_password']);
			
			if ($old_password == "" || $new_password == "" || $confirm_password == "")
			{
				$this->m_engine->Logger->Log("Recieved attempt to change password for account '{$member['username']}' but didn't provide information.");
				$arguments['error_type'] = "no_password";
			}
			else if ($new_password != $confirm_password)
			{
				$this->m_engine->Logger->Log("Recieved attempt to change password for account '{$member['username']}' but provided unconfirmed password.");
				$arguments['error_type'] = "invalid_confirm";
			}
			else
			{
				$hashed = hash("sha512", $old_password . $member['password_salt']);
				if ($hashed != $member['password'])
				{
					$this->m_engine->Logger->Log("Recieved attempt to change password for account '{$member['username']}' but provided invalid old password.");
					$arguments['error_type'] = "invalid_password";
				}
				
				// Password change time!
				else
				{
					$salt = hash("sha512", microtime(true));
					$hash = hash("sha512", $new_password . $salt);
					
					$this->m_engine->Logger->Log("User changed password for account '{$member['username']}'.");

					$this->m_engine->Database->Query("update_member_password", array(":id" 		 => $member['id'],
																					 ":password" => $hash,
																					 ":salt" 	 => $salt));

					BrowserHelper::RedirectExit(BASE_SCRIPT_URI . 'manage/');
					//$arguments['success'] = true;
				}
			}
		}
		
		// Render the template.
		$this->m_engine->RenderTemplate("Manage/ChangePassword.tmpl", $arguments);
	}
	
}