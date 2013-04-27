<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	manageresetpasswordpagehandler.class.php
//	Author: tim
// -------------------------------------------------------------
//	This file contains the page handler for the "reset password" 
//	page in the management area.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//	This class handles showing the "reset password" page in the 
//	management area.
//
//	URI for this handler is:
// 		/index.php/manage/resetpassword
// -------------------------------------------------------------
class ManageResetPasswordPageHandler extends PageHandler
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
			$uri_arguments[1] == "resetpassword")
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
				'success' => false
			)		
		);	
		
		// Have we submitted the form?
		if (isset($this->m_engine->Settings->RequestValues['email']))
		{
			$member		= $this->m_engine->Member->Settings;
			$email 		= trim($this->m_engine->Settings->RequestValues['email']);
			
			if ($email == "")
			{
				$arguments['error_type'] = "no_email";
			}
			else
			{
				// Send reset link.
				$result = $this->m_engine->Database->Query("select_member_by_email", array(":email" => $email));
				if (count($result->Rows) > 0)
				{
					$site_name			= $this->m_engine->Settings->DatabaseSettings['site_name'];
					$site_domain		= $_SERVER['HTTP_HOST'];
				
					$member 			= $result->Rows[0];				

					// Generate a reset link.
					$link_hash			= substr(hash("sha512", microtime(true)), 0, 16);
					$reset_link			= BASE_SCRIPT_URI . 'manage/resetpassword/confirm/' . $link_hash;

					$this->m_engine->Database->Query("insert_password_reset_link", array(
																							":member_id" 	=> $member['id'],
																							":link_hash" 	=> $link_hash,
																							":create_time" 	=> time(),
																							":create_ip" 	=> $_SERVER['REMOTE_ADDR'],
																						));

					// Grab localized email settings.
					$email_body 		= $this->m_engine->Language->Get('RESET_PASSWORD_EMAIL');
					$email_subject 		= $this->m_engine->Language->Get('RESET_PASSWORD_SUBJECT');
					$email_sender 		= $this->m_engine->Language->Get('RESET_PASSWORD_SENDER_EMAIL');
					$email_recipient	= $member['email'];

					// Replace placeholder values.
					$email_body 		= vsprintf($email_body, 	array($member['username'], $site_name, $reset_link, $site_name));
					$email_subject	 	= vsprintf($email_subject,	array($site_name));
					$email_sender	 	= vsprintf($email_sender, 	array($site_domain));

					mail($email_recipient, $email_subject, $email_body, "From: " . $email_sender . "\r\n");
				}
				
				// Show success regardless of if we found an email or not.
				// We don't want people able to work out what emails are registered.				
				$arguments['success'] = true;
			}
		}
	
		// Render the template.
		$this->m_engine->RenderTemplate("Manage/ResetPassword.tmpl", $arguments);
	}
	
}