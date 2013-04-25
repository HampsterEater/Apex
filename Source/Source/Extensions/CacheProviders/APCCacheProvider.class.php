 <?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	apccacheprovider.class.php
//	Author: tim
// -------------------------------------------------------------
//	This file contains a class provider implementation that
//	uses the advanced php cache (APC).
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//	Provides an implementation of a cache that supports the
//	advanced php cache (APC) system.
// -------------------------------------------------------------
class APCCacheProvider extends CacheProvider
{

	// Engine instance which created us.
	private $m_engine;
	
	// -------------------------------------------------------------
	//	If returns true then this provider is capable of being
	//	instantiated and used.
	// -------------------------------------------------------------
	public function IsSupported()
	{
		return extension_loaded("apc") && ini_get('apc.enabled');
	}
	
	// -------------------------------------------------------------
	//  Constructs this class.
	// -------------------------------------------------------------
	public function __construct($engine)
	{
		$this->m_engine = $engine;
	}
	
	// -------------------------------------------------------------
	//	Checks if a key exists in the cache.
	//
	//	@param key 	 Key to associated with cache entry to check.
	//
	//  @returns True if the key is in the cache.
	// -------------------------------------------------------------
	public function Exists($key)
	{
		return apc_exist($key);
	}
	
	// -------------------------------------------------------------
	//  Gets a cache entry with the given key.
	//
	//	@param key 	 Key to associated with cache entry to retrieve.
	//
	//  @returns Cache entry if it exists, otherwise null.
	// -------------------------------------------------------------
	public function Get($key)
	{
		$val = apc_fetch($key);

		if ($val === FALSE)
		{
			return null;
		}
		else
		{
			return $val;
		}
	}
	
	// -------------------------------------------------------------
	//  Stores a cache entry with the given key.
	//
	//	@param key 	 Key to associate cache entry with.
	//	@param value Value to insert into cache.
	//
	//  @returns True if successful.
	// -------------------------------------------------------------
	public function Set($key, $value)
	{
		apc_delete($key);
		return apc_add($key, $value, $this->m_engine->Settings->CacheTimeToLive);
	}

	// -------------------------------------------------------------
	//	Removes the given entry from the cache.
	//
	//	@param key Key of entry to be removed.
	//
	//  @returns True if successful.
	// -------------------------------------------------------------
	public function Remove($key)
	{
		return apc_delete($key);
	}
	
	// -------------------------------------------------------------
	//  Lists all keys currently in the cache.
	//
	//  @returns Array of keys in the cache.
	// -------------------------------------------------------------
	public function ListKeys()
	{
		$keys = array();
	
		$iterator = new APCIterator('user', NULL, APC_ITER_KEY);
		foreach($iterator as $key) 
		{
			array_push($key);
		}
	
		return $keys;
	}
	
}