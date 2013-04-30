<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	cacheprovider.class.php
//	Author: tim
// -------------------------------------------------------------
//	This file contains the base cache provider class. This
//	class is the abstract base of for all classes that provide
//	caching support to this software.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//	This class is the abstract base of for all classes that 
//	provide caching support to this software.
// -------------------------------------------------------------
abstract class CacheProvider
{

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
	//	Checks if a key exists in the cache.
	//
	//	@param key 	 Key to associated with cache entry to check.
	//
	//  @returns True if the key is in the cache.
	// -------------------------------------------------------------
	abstract public function Exists($key);
	
	// -------------------------------------------------------------
	//  Gets a cache entry with the given key.
	//
	//	@param key 	 Key to associated with cache entry to retrieve.
	//
	//  @returns Cache entry if it exists, otherwise null.
	// -------------------------------------------------------------
	abstract public function Get($key);
	
	// -------------------------------------------------------------
	//  Stores a cache entry with the given key.
	//
	//	@param key 	 Key to associate cache entry with.
	//	@param value Value to insert into cache.
	//
	//  @returns True if successful.
	// -------------------------------------------------------------
	abstract public function Set($key, $value);
	
	// -------------------------------------------------------------
	//	Removes the given entry from the cache.
	//
	//	@param key Key of entry to be removed.
	//
	//  @returns True if successful.
	// -------------------------------------------------------------
	abstract public function Remove($key);
	
	// -------------------------------------------------------------
	//  Lists all keys currently in the cache.
	//
	//  @returns Array of keys in the cache.
	// -------------------------------------------------------------
	abstract public function ListKeys();
		
	// -------------------------------------------------------------
	//  Removes all keys that match the given file name pattern.
	//
	//	@param pattern Pattern to match, can include wildcards.
	// -------------------------------------------------------------
	public function RemoveByPattern($pattern)
	{
		$keys = $this->ListKeys();
		foreach ($keys as $key)
		{
			if (fnmatch($pattern, $key))
			{
				$this->Remove($key);
			}
		}
	}
	
	// -------------------------------------------------------------
	//  Creates the given cache provider.
	//
	//	@param engine Engine that is creating a provider.
	//	@param name	  Name of provider class.
	//
	//  @returns A cache provider if one could be created, other-
	//			 -wise null.
	// -------------------------------------------------------------	
	public static function CreateProvider($engine, $class_name)
	{
		// Does class exist?
		if (!class_exists($class_name))
		{		
			$m_engine->Logger->InternalError("Cache provider specified ('{$class_name}') does not exist, or has not been imported (did you forget to add it to the extension list?).");		
		}		
		
		// Check it derives from cacheprovider.
		if (!is_subclass_of($class_name, "CacheProvider"))
		{
			$engine->Logger->InternalError("Cache provider specified ('{$class_name}') is not derived from the CacheProvider class.");		
		}		
		
		// Instantiate.
		$class 			= new ReflectionClass($class_name);
		$instance		= $class->newInstance($engine);			
		
		// Check provider is supported.
		$isSupported 	= $class->getMethod('IsSupported')->invoke($instance);	
		if (!$isSupported)
		{
			$engine->Logger->InternalError("Cache provider specified ('{$class_name}') is not supported or correctly configured for this server.");
		}
		
		return $instance;	
	}
	
}