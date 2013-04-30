<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	boardindexpagehandler.class.php
//	Author: tim
// -------------------------------------------------------------
//	This file contains the code for showing the board-index.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//	This class handles showing board index page.
//
//	URI for this handler is:
// 		/board-name[/page-index]
// -------------------------------------------------------------
class BoardIndexPageHandler extends PageHandler
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
		if (count($uri_arguments) == 1 ||
			count($uri_arguments) == 2)
		{
			$board_name = $uri_arguments[0];
		
			// Check first argument is a board name.
			foreach ($this->m_engine->Settings->PageSettings['boards'] as $board)
			{
				if ($board['url'] == $board_name)
				{
					// Is page a correct integer.
					if (count($uri_arguments) == 2)
					{
						$page_index = $uri_arguments[1];
						if (is_numeric($page_index))
						{
							return true;
						}
					}
					else
					{
						return true;
					}
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
		$uri_arguments			= $this->m_engine->Settings->URIArguments;
		$board_uri  			= $uri_arguments[0];
		$page_index 			= count($uri_arguments) > 1 ? intval($uri_arguments[1]) - 1 : 0;
		$arguments['board'] 	= null;
		
		// Load board settings.
		$arguments['board'] = $this->m_engine->GetBoardByUri($board_uri);
		if ($arguments['board'] == null)
		{
			$this->m_engine->Logger->InternalError("Could not retrieve board information (for uri /{$board_uri}/) from database.");
		}
						
		// Check permissions.
		$this->m_engine->Member->AssertAllowedTo("view_board_index_page", $arguments['board']['id']);
		
		// Work out password cookie.
		$cookie_password_key = 'board_' . ($arguments['board']['id']) . '_password';

		// Is an access password required?
		if ($arguments['board']['password'] != '' &&
			$this->m_engine->Member->IsAllowedTo("bypass_passwords", $arguments['board']['id']) == false)
		{
			if (isset($_SESSION[$cookie_password_key]) == false ||
				$_SESSION[$cookie_password_key] != $arguments['board']['password'])
			{
				BrowserHelper::RedirectExit(BASE_SCRIPT_URI . $board_uri . "/login");
				return;
			}
		}
		
		// Generate recaptcha code.
		if ($arguments['board']['use_recaptcha'] == true)
		{
			$arguments['recaptcha_html'] = recaptcha_get_html($this->m_engine->Settings->RecaptchaPublicKey);
		}
	
		// Work out what tags we auto-tag posts with.
		$arguments['board']['auto_tags'] = array();
		if ($arguments['board']['auto_tag_ids'] != "")
		{
			$result = $this->m_engine->Database->Query("select_tags_by_id_array", array(), $arguments['board']['auto_tag_ids']);
			$arguments['board']['auto_tags'] = $result->Rows;
		}
		$arguments['board']['auto_tags'] = implode(",", array_map(
														function($obj) { return $obj['name']; }, 
														$arguments['board']['auto_tags']));
														
		// Work out what tags we show on this board posts with.
		$arguments['board']['post_filter_tags'] = array();
		if ($arguments['board']['post_filter_tag_ids'] != "")
		{
			$result = $this->m_engine->Database->Query("select_tags_by_id_array", array(), $arguments['board']['post_filter_tag_ids']);
			$arguments['board']['post_filter_tags'] = $result->Rows;
		}
		$arguments['board']['post_filter_tags_string'] = implode(", ", array_map(
																			function($obj) { return $obj['name']; }, 
																			$arguments['board']['post_filter_tags']));
		
		// Work out what file types this board allows.
		$arguments['board']['allowed_upload_file_types'] = array();
		if ($arguments['board']['allowed_upload_file_type_ids'] != "")
		{
			$result = $this->m_engine->Database->Query("select_file_types_by_id_array", array(), $arguments['board']['allowed_upload_file_type_ids']);
			$arguments['board']['allowed_upload_file_types'] = $result->Rows;
		}
		$arguments['board']['allowed_upload_file_types_string'] = implode(", ", array_map(
																			function($obj) { return $obj['extension']; }, 
																			$arguments['board']['allowed_upload_file_types']));
			
		// Render the template.
		$this->m_engine->RenderTemplate("BoardIndex.tmpl", $arguments);
	}
	
}