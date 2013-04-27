<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	pagehandler.class.php
//	Author: tim
// -------------------------------------------------------------
//	This file contains the base page handler. This
//	class is the abstract base for all classes that handle
//	different pages. 
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//	This class is the abstract base for all classes that handle
//	different pages. Which page handler is used depends on the 
//	URI arguments passed to the script. 
// -------------------------------------------------------------
abstract class PageHandler
{

	// List of all providers instantiated.
	private static $m_handlers;
	
	// Reflection class for this class.
	private $m_reflection_class;

	// -------------------------------------------------------------
	//	If returns true then this provider is capable of being
	//	instantiated and used.
	// -------------------------------------------------------------
	abstract public function IsSupported();
	
	// -------------------------------------------------------------
	//	If returns true then this provider will be responsible 
	//	for handling pages with the given URI.
	// -------------------------------------------------------------
	abstract public function CanHandleURI($uri_arguments);
	
	// -------------------------------------------------------------
	//  Constructs this class.
	//
	//	@param engine Instance of engine that constructed this
	//				  class.
	// -------------------------------------------------------------
	abstract public function __construct($engine);
	
	// -------------------------------------------------------------
	//	Invoked when this page handler is responsible for rendering
	//	the current page.
	// -------------------------------------------------------------
	abstract public function RenderPage($arguments = array());
	
	// -------------------------------------------------------------
	//  Creates the all the page handlers providers 
	//	this system contains.
	// -------------------------------------------------------------	
	public static function CreateHandlers($engine)
	{
		PageHandler::$m_handlers = array();
		$classes = ReflectionHelper::GetSubClasses("PageHandler");
		
		foreach ($classes as $class_name)
		{
			$class 		= new ReflectionClass($class_name);
			$instance   = $class->newInstance($engine);		
			$isEnabled 	= $class->getMethod('IsSupported')->invoke($instance);

			if ($isEnabled == true)
			{
				$instance->m_reflection_class = $class;				
				array_push(PageHandler::$m_handlers, $instance);
			}
		}				
	}
	
	// -------------------------------------------------------------
	//  Invokes the given hook.
	// -------------------------------------------------------------	
	public static function FindHandlerForURI($uri_arguments)
	{
		foreach (PageHandler::$m_handlers as $hook)
		{
			if ($hook->CanHandleURI($uri_arguments))
			{
				return $hook;
			}
		}			
		return null;		
	}
	
}