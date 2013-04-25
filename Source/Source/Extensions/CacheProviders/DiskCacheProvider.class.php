 <?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	diskcacheprovider.class.php
//	Author: tim
// -------------------------------------------------------------
//	This file contains a class provider implementation that
//	uses the filesystem for caching.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//	Provides an implementation of a cache that supports using
//	the filesystem for caching.
// -------------------------------------------------------------
class DiskCacheProvider extends CacheProvider
{

	// Engine instance which created us.
	private $m_engine;

	// Absolute path to cache directory.
	private $m_cache_path;
	
	// -------------------------------------------------------------
	//	If returns true then this provider is capable of being
	//	instantiated and used.
	// -------------------------------------------------------------
	public function IsSupported()
	{
		return $this->ConstructCacheDirectory();
	}
			
	// -------------------------------------------------------------
	//  Constructs this class.
	// -------------------------------------------------------------
	public function __construct($engine)
	{
		$this->m_engine 	  = $engine;
		$this->m_cache_path   = FileHelper::StripTrailingSlash(BASE_PATH . $this->m_engine->Settings->CacheDirectory) . DIRECTORY_SEPARATOR;

		// Try and construct the cache directory.
		$this->ConstructCacheDirectory();
	}
	
	// -------------------------------------------------------------
	//	Attempts to construct the cache directory. If it fails
	//	an internal error will be shown.
	// -------------------------------------------------------------
	private function ConstructCacheDirectory()
	{
		if (!is_dir($this->m_cache_path))
		{
			if (!mkdir($this->m_cache_path, 0777, true))
			{
				return false;
			}
		}
		
		if (!is_writable($this->m_cache_path))
		{
			return false;
		}
		
		return true;
	}

	// -------------------------------------------------------------
	//	Clears out old entries in the cache.
	//
	//	@param ignore_ttl If true entries will be deleted regardless
	//					  of their ttl until the cache has at least
	//					  free_size space.
	//	@param free_size  Target free size if we are ignoring ttl.
	//
	//  @returns True if successful.
	// -------------------------------------------------------------
	private function PurgeCache($ignore_ttl = false, $free_size = 0)
	{
		$files = scandir($this->m_cache_path);
		
		// Are we ignoring ttl and going full-hog?
		if ($ignore_ttl == true)
		{
			// Purge any entiries until we have enough space.
			foreach ($files as $file)
			{
				// Enough free space yet?
				if ($this->GetTotalFreeCacheSpace() >= $free_size)
				{
					return true;
				}
				
				// Bye bye file.
				$path = $this->m_cache_path . $file;
				if (file_exists($path))
				{
					unlink($path);
				}
			}
		}
		
		else
		{
			// Purge any old file entries.
			foreach ($files as $file)
			{
				$path = $this->m_cache_path . $file;
				if (file_exists($path))
				{
					$time_elapsed = time() - filemtime($path);
					if ($time_elapsed >= $this->m_engine->Settings->CacheTimeToLive)
					{
						unlink($path);
					}
				}
			}
		}
	}

	// -------------------------------------------------------------
	//	Works out if there is enough space for the given item
	//	if there is not the function will attempt to purge the 
	//	cache.
	//
	//	@param size Size of entry we want to cache.
	//	@param allow_purge If true we are allowed to purge the cache
	//					   to trye and make room.
	//
	//  @returns True if there is enough space for cache entry.
	// -------------------------------------------------------------
	private function CanCacheSize($size, $allow_purge = true)
	{
		$enough_space = true;
	
		// Enough space in cache?
		if ($this->GetTotalFreeCacheSpace() 	 < $size ||
			disk_free_space($this->m_cache_path) < $size)
		{
			$enough_space = false;
		}
				
		// Enough space?
		if ($enough_space == true)
		{
			return true;
		}		
		else
		{
			if ($allow_purge == false)
			{
				return false;
			}
			else
			{
				// Try and purge just old entries first.
				$this->PurgeCache();
				if ($this->CanCacheSize($size, false) == true)
				{
					return true;
				}
				
				// Now purge anything to make room.
				$this->PurgeCache(true, $size);
				return $this->CanCacheSize($size, false);
			}
		}
	}

	// -------------------------------------------------------------
	//	Works out the total size of the cache.
	//
	//  @returns Size of cache in bytes.
	// -------------------------------------------------------------
	private function GetTotalCacheSize()
	{
		return FileHelper::DirectorySize($this->m_cache_path, false);
	}

	// -------------------------------------------------------------
	//	Works out the total free space in the cache.
	//
	//  @returns Size of cache in bytes.
	// -------------------------------------------------------------
	private function GetTotalFreeCacheSpace()
	{
		return $this->m_engine->Settings->CacheMaxSize - $this->GetTotalCacheSize();
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
		$key  = base64_encode($key);
		$file = $this->m_cache_path . $key;
		
		return file_exists($file);
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
		$key  = base64_encode($key);
		$file = $this->m_cache_path . $key;
		
		// Is it in cache?
		if (file_exists($file))
		{
			$data = file_get_contents($file);
			if ($data === FALSE)
			{
				return null;
			}
			
			return unserialize($data);
		}
		else
		{
			return null;
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
		// CRC32 hash to key rather than use other hashes for speed.
		// We can't allow user to set key directly as it may have non-path valid elements in it.
		$key 	= base64_encode($key);

		// Serialize the value
		$value	= serialize($value);
		
		// Work out size and place to store.
		$size 	= strlen($value);		// TODO: Pretty sure this works with MB strings, worth checking tho.
		$file 	= $this->m_cache_path . $key;
		
		// Can we cache?
		if (!$this->CanCacheSize($size))
		{
			return false;
		}
		
		// In to the cache you go!
		return (file_put_contents($file, $value) !== FALSE);
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
		$key  = base64_encode($key);
		$file = $this->m_cache_path . $key;
		
		// Is it in cache?
		if (file_exists($file))
		{
			return unlink($file);
		}
		else
		{
			return false;
		}
	}
	
	// -------------------------------------------------------------
	//  Lists all keys currently in the cache.
	//
	//  @returns Array of keys in the cache.
	// -------------------------------------------------------------
	public function ListKeys()
	{
		$keys = array();
		
		$files = scandir($this->m_cache_path);
		
		foreach ($files as $file)
		{
			$key = base64_decode($file);
			if ($key !== FALSE)
			{
				array_push($keys, $key);
			}
		}
		
		return $keys;
	}
	
}