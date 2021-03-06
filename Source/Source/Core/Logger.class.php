<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	logger.class.php
//	Author: tim
// -------------------------------------------------------------
//	This file contains the logger class. This class deals with 
//	logging error/warning/info messages.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//  This class deals with logging error/warning/info messages.
// -------------------------------------------------------------
class Logger
{
	private $m_engine;
	private $m_handlingError;

	// -------------------------------------------------------------
	//  Constructs this class.
	//
	//	@param engine Instance of engine that constructed this
	//				  class.
	// -------------------------------------------------------------
	public function __construct($engine)
	{
		$this->m_engine = $engine;
		$this->m_handlingError = false;
	}
	
	// -------------------------------------------------------------
	//  Shows the default error page used if we haven't gotten
	//	far enough to use a templated one.
	//	
	//	@param uri	   URI used when attempting to use template.
	//	@param status  Status header to return.
	//	@param title   Title of page to show.
	//	@param content Message to show on error page.
	// -------------------------------------------------------------
	public function DefaultError($uri, $status, $title, $content)
	{		
		// Have we got far enough that we can use a custom error handler?
		if ($this->m_handlingError == false &&
			$this->m_engine->RenderingPage == true)
		{
			$this->m_handlingError = true;
			
			$handler = PageHandler::FindHandlerForURI($uri);
			if ($handler != NULL)
			{
				$handler->RenderPage(array("message" => $content));
				exit(1);
			}
		}
		
		header("Status: " . $status);
		die("
			<!DOCTYPE html>
			<html>
				<head>
					<title>" . $title . "</title>
				</head>
				<body>
					<h2>" . $title . "</h2>
					" . $content . "
					<br/>
					<hr/>
					<span style='font-size: 75%;'>" . SOFTWARE_SIGNATURE . ", " . $_SERVER['SERVER_SOFTWARE'] . "</span>
				</body>
			</html>
		");
	}

	// -------------------------------------------------------------
	//  Takes an error message and emits and error page. If 
	//	available the page will use the 404 not-found error template.
	//
	//	@param content Error message description.
	// -------------------------------------------------------------
	public function NotFoundError($content)
	{
		$this->m_engine->Logger->Log("User recieved 404 error when viewing page: " . FULL_URI . "; " . $content);
		
		$this->DefaultError(array( "error", "404" ),
							 "404 Not Found", 
							 "Page Not Found",
							 $content);
	}
	
	// -------------------------------------------------------------
	//  Takes an error message and emits and error page. If 
	//	available the page will use the 403 not-found error template.
	//
	//	@param content Error message description.
	// -------------------------------------------------------------
	public function PermissionDeniedError($content)
	{
		$this->m_engine->Logger->Log("User denied permission to view page: " . FULL_URI . "; " . $content);
		
		$this->DefaultError(array( "error", "403" ),
							 "403 Forbidden", 
							 "Permission Denied",
							 $content);
	}

	// -------------------------------------------------------------
	//  Takes an internal error message and emits and error page. If 
	//	available the page will use an error template.
	//
	//	@param content Error message description.
	// -------------------------------------------------------------
	public function InternalError($content)
	{
		$this->m_engine->Logger->Log("User recieved 500 error when viewing page: " . FULL_URI . "; " . $content);
		
		$this->DefaultError(array( "error", "500" ),
							 "500 Internal Server Error", 
							 "Internal Error",
							 $content);
	}
	
	// -------------------------------------------------------------
	//  Writes a log into the database if we are connected.
	//
	//	@param content Error message description.
	// -------------------------------------------------------------
	public function Log($content, $source = "")
	{
		if ($source == "")
		{	
			if ($this->m_engine->Member != NULL)
			{
				$source = $this->m_engine->Member->Settings['username'];			
			}
			else
			{
				$source = "Not Logged In";
			}
		}
	
		$db = $this->m_engine->Database;
		if ($db != null && $db->IsConnected())
		{
			$db->Query("insert_log", array(
				":message" 		=> $content,
				":source" 		=> $source,
				":create_ip" 	=> $_SERVER['REMOTE_ADDR'],
				":create_time" 	=> time(),
			));
		}
	}
	
}