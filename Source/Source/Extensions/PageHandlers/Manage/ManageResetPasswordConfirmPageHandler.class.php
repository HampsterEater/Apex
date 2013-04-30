<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	manageresetpassworconfirmpagehandler.class.php
//	Author: tim
// -------------------------------------------------------------
//	This file contains the page handler for the "reset password" 
//	confirmation page in the management area.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//	This class handles showing the "reset password confirm" page 
//	in the management area.
//
//	URI for this handler is:
// 		/index.php/manage/resetpassword/confirm/[hash]
// -------------------------------------------------------------
class ManageResetPasswordConfirmPageHandler extends PageHandler
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
		if (count($uri_arguments) == 4 &&
			$uri_arguments[0] == "manage" &&
			$uri_arguments[1] == "resetpassword"&&
			$uri_arguments[2] == "confirm")
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
		$this->m_engine->Member->AssertAllowedTo("view_reset_password_confirm_page");
		
		// Are we even logged in?
		if ($this->m_engine->IsLoggedIn())
		{
			BrowserHelper::RedirectExit(BASE_SCRIPT_URI . 'manage');
		}
		
		$arguments = array_merge($arguments,
			array
			(
				'error_type' => '',
				'success' => false
			)		
		);	
		
		// Delete old password reset links.
		$this->m_engine->Database->Query("delete_expired_password_reset_links", array(":oldest_time" => time() - $this->m_engine->Settings->DatabaseSettings['password_reset_link_expire_time']));
		
		// Check link is valid.
		$link_hash = $this->m_engine->Settings->URIArguments[3];
		$result    = $this->m_engine->Database->Query("select_password_reset_link_by_hash", array(":link_hash" => $link_hash));
		if (count($result->Rows) <= 0)
		{
			$this->m_engine->Logger->Log("User attempted to reset password for invalid or expired link '{$link_hash}'.");
			$this->m_engine->Logger->NotFoundError("Password reset link specified was not found.");
		}		
		$reset_link = $result->Rows[0];
		
		// Grab the member.
		$member = new Member($this->m_engine, $reset_link['member_id']);
		if (!$member->LoadFromDatabase())
		{
			$this->m_engine->Logger->Log("User attempted to reset password using a link that points to a non-existant member '{$link_hash}'.");
			$this->m_engine->Logger->NotFoundError("Password reset link specified was for a non-existant member.");
		}
		
		// Have we submitted the form?
		if (isset($this->m_engine->Settings->RequestValues['new_password']) &&
			isset($this->m_engine->Settings->RequestValues['confirm_password']))
		{		
			$new_password 		= trim($this->m_engine->Settings->RequestValues['new_password']);
			$confirm_password 	= trim($this->m_engine->Settings->RequestValues['confirm_password']);
		
			if ($new_password == "" || $confirm_password == "")
			{			
				$this->m_engine->Logger->Log("User attempted to reset password for account '{$member['username']}', but did not provide all information required.");
				$arguments['error_type'] = "no_password";
			}
			else if ($new_password != $confirm_password)
			{
				$this->m_engine->Logger->Log("User attempted to reset password for account '{$member['username']}', but password confirmation was invalid.");
				$arguments['error_type'] = "invalid_confirm";
			}
			else
			{
				$salt = hash("sha512", microtime(true));
				$hash = hash("sha512", $new_password . $salt);
					
				$this->m_engine->Logger->Log("User attempted to reset password for account '{$member['username']}'.");
				
				// Change password.
				$this->m_engine->Database->Query("update_member_password", array(":id" 		 => $member->ID,
																				 ":password" => $hash,
																				 ":salt" 	 => $salt));
																				 
				// Delete password reset link.
				$this->m_engine->Database->Query("delete_password_reset_link_by_id", array(":id" => $reset_link['id']));

				BrowserHelper::RedirectExit(BASE_SCRIPT_URI . 'manage/');
			}
		}
	
		// Render the template.
		$this->m_engine->RenderTemplate("Manage/ResetPasswordConfirm.tmpl", $arguments);
	}
	
}