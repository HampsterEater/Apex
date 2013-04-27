<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	hookprovider.class.php
//	Author: tim
// -------------------------------------------------------------
//	This file contains the base hook provider class. This
//	class is the abstract base for all classes that hook
//	into different events.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//	This class is the abstract base for all classes that hook
//	into different events.
// -------------------------------------------------------------
abstract class HookProvider
{

	// List of all providers instantiated.
	private static $m_providers;
	
	// Reflection class for this class.
	private $m_reflection_class;

	// -------------------------------------------------------------
	//	If returns true then this provider is capable of being
	//	instantiated and used.
	// -------------------------------------------------------------
	abstract public function IsSupported();
	
	// -------------------------------------------------------------
	//  Constructs this class.
	//
	//	@param engine Instance of engine that constructed this
	//				  class.
	// -------------------------------------------------------------
	abstract public function __construct($engine);
	
	// -------------------------------------------------------------
	//	Invoked before we do anything.
	// -------------------------------------------------------------
	public function OnBegin() { }
	
	// -------------------------------------------------------------
	//	Invoked before the database is connected to.
	// -------------------------------------------------------------
	public function OnPreDatabaseConnection() {}
	
	// -------------------------------------------------------------
	//	Invoked after the database is connected to.
	// -------------------------------------------------------------
	public function OnPostDatabaseConnection() {}
	
	// -------------------------------------------------------------
	//	Invoked before the database is disconnected from.
	// -------------------------------------------------------------
	public function OnPreDatabaseDisconnection() {}
	
	// -------------------------------------------------------------
	//	Invoked after the database is disconnected from.
	// -------------------------------------------------------------
	public function OnPostDatabaseDisconnection() {}
	
	// -------------------------------------------------------------
	//	Invoked before the page is rendered.
	// -------------------------------------------------------------
	public function OnPreRender() {}
	
	// -------------------------------------------------------------
	//	Invoked after the page is rendered.
	// -------------------------------------------------------------
	public function OnPostRender() {}
	
	// -------------------------------------------------------------
	//	Invoked right before we finish running.
	// -------------------------------------------------------------
	public function OnFinish() {}
	
	// -------------------------------------------------------------
	//  Creates the all the hook providers this system contains.
	// -------------------------------------------------------------	
	public static function CreateHooks($engine)
	{
		HookProvider::$m_providers = array();
		$classes = ReflectionHelper::GetSubClasses("HookProvider");
		
		foreach ($classes as $class_name)
		{
			$class 		= new ReflectionClass($class_name);
			$instance   = $class->newInstance($engine);		
			$isEnabled 	= $class->getMethod('IsSupported')->invoke($instance);

			if ($isEnabled == true)
			{
				$instance->m_reflection_class = $class;				
				array_push(HookProvider::$m_providers, $instance);
			}
		}				
	}
	
	// -------------------------------------------------------------
	//  Invokes the given hook.
	// -------------------------------------------------------------	
	public static function InvokeEvent($name)
	{
		foreach (HookProvider::$m_providers as $hook)
		{
			$hook->$name();
		}				
	}
	
}