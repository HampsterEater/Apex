<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	databaseprovider.class.php
//	Author: tim
// -------------------------------------------------------------
//	This file contains the base database provider class. This
//	class is the abstract base of for all classes that provide
//	database access to this server.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//	This class stores the result of a database query.
// -------------------------------------------------------------
class DatabaseResult
{
	public $Rows;
	public $LastInsertID;
	public $AffectedRows;
}

// -------------------------------------------------------------
//	This class is the abstract base of for all classes that 
//	provide database acces to this server.
// -------------------------------------------------------------
abstract class DatabaseProvider
{

	// -------------------------------------------------------------
	//	If returns true then this provider is capable of being
	//	instantiated and used.
	// -------------------------------------------------------------
	abstract public function IsSupported();
		
	// -------------------------------------------------------------
	//	Returns true if connected.
	// -------------------------------------------------------------
	abstract public function IsConnected();
	
	// -------------------------------------------------------------
	//  Constructs this class.
	//
	//	@param engine Instance of engine that constructed this
	//				  class.
	// -------------------------------------------------------------
	abstract public function __construct($engine);
	
	// -------------------------------------------------------------
	//	When called the provider will attempt to connect to the
	//	database. If it fails an internal server error is thrown.
	// -------------------------------------------------------------
	public abstract function Connect();
	
	// -------------------------------------------------------------
	//	When called the provider will attempt to load the prepared
	//	statement defined by the name given and will attempt to run
	//	it with the arguments given.
	//
	//	@param name 	 		Name of prepared query to execute.
	//	@param arguments 		Arguments to pass to the prepared query.
	//	@param raw_arguments	Raw arguments allow you to replace ?? values in
	//							prepared statements with strings. Be aware no
	//							cleaning is done for this, and should be used as 
	//							an absolute last resort. It is only here for things
	//							like passing arrays which PDO dosen't support.
	//
	//	@returns A DatabaseResult instance.
	// -------------------------------------------------------------
	public abstract function Query($name, $arguments = array(), $raw_arguments = array());
	
	// -------------------------------------------------------------
	//	When called the provider will attempt to get as much 
	//	information as it can about each column in the given table.
	//
	//	@param name 	 		Name of table to get information 
	//							about.
	//
	//	@returns An array of column information or null if it
	//			 could not be retrieved.
	//
	//			array
	//			(
	//				"column_a" => array
	//				(
	//					"name" 		=> 0,
	//					"datatype" 	=> "varchar",
	//					"size" 		=> 4,
	//				)
	//			)
	//
	// -------------------------------------------------------------
	public abstract function GetTableInfo($name);
	
	// -------------------------------------------------------------
	//	When called the provider will attempt to disconnect
	//	from the database its connected to.
	// -------------------------------------------------------------
	public abstract function Disconnect();
	
	// -------------------------------------------------------------
	//	Returns the number of queries this provider has handled.
	// -------------------------------------------------------------
	public abstract function GetQueryCount();
	
	// -------------------------------------------------------------
	//  Creates the given database provider.
	//
	//	@param engine Engine that is creating a provider.
	//	@param name	  Name of provider class.
	//
	//  @returns A database provider if one could be created, other-
	//			 -wise null.
	// -------------------------------------------------------------	
	public static function CreateProvider($engine, $class_name)
	{
		// Does class exist?
		if (!class_exists($class_name))
		{		
			$engine->Logger->InternalError("Database provider specified ('{$class_name}') does not exist, or has not been imported (did you forget to add it to the extension list?).");		
		}		
		
		// Check it derives from cacheprovider.
		if (!is_subclass_of($class_name, "DatabaseProvider"))
		{
			$engine->Logger->InternalError("Database provider specified ('{$class_name}') is not derived from the DatabaseProvider class.");		
		}		
		
		// Instantiate.
		$class 			= new ReflectionClass($class_name);
		$instance		= $class->newInstance($engine);			
		
		// Check provider is supported.
		$isSupported 	= $class->getMethod('IsSupported')->invoke($instance);	
		if (!$isSupported)
		{
			$engine->Logger->InternalError("Database provider specified ('{$class_name}') is not supported or correctly configured for this server.");
		}
		
		return $instance;	
	}
	
}