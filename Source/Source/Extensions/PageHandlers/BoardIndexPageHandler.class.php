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
		$uri_arguments		= $this->m_engine->Settings->URIArguments;
		$board_uri  		= $uri_arguments[0];
		$page_index 		= count($uri_arguments) > 1 ? intval($uri_arguments[1]) - 1 : 0;
		$arguments['board'] = null;
		
		// Load board settings.
		$arguments['board'] = $this->m_engine->GetBoardByUri($board_uri);
		if ($arguments['board'] == null)
		{
			$this->m_engine->Logger->InternalError("Could not retrieve board information (for uri /{$board_uri}/) from database.");
		}
		
		// Is an access password required?
		
	
		// Render the template.
		$this->m_engine->RenderTemplate("BoardIndex.tmpl", $arguments);
	}
	
}