<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	mysqldatabaseprovider.class.php
//	Author: tim
// -------------------------------------------------------------
//	This file contains the database provider that allows 
//	interaction with mysql databases.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//	This class provides a database provider implementation  
//	that allows interaction with mysql databases.
// -------------------------------------------------------------
class MySQLDatabaseProvider extends DatabaseProvider
{
	// Database link identifier.
	private $m_connection;
	private $m_queryCount;

	// Engine instance.
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
	//	Returns true if connected.
	// -------------------------------------------------------------
	public function IsConnected()
	{
		return ($this->m_connection != null);
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
	//	When called the provider will attempt to connect to the
	//	database. If it fails an internal server error is thrown.
	// -------------------------------------------------------------
	public function Connect()
	{
		try
		{
			$this->m_connection = new PDO('mysql:host=' . $this->m_engine->Settings->DatabaseHost . 
												';dbname=' . $this->m_engine->Settings->DatabaseName . 
												';charset=utf8', 
												$this->m_engine->Settings->DatabaseUsername, 
												$this->m_engine->Settings->DatabasePassword
										 );
			$this->m_connection->setAttribute(PDO::ATTR_ERRMODE, 			PDO::ERRMODE_EXCEPTION);
			$this->m_connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, 	false);		
		} 
		catch(PDOException $ex) 
		{
			$this->m_engine->Logger->InternalError("Encountered error when attempting to connect to MySQL Server:<br/><br/><i>" . $ex->getMessage() . "</i>");		
		}
	}
	
	// -------------------------------------------------------------
	//	When called the provider will attempt to load the prepared
	//	statement defined by the name given and will attempt to run
	//	it with the arguments given.
	//
	//	@param name 	 Name of prepared query to execute.
	//	@param arguments Arguments to pass to the prepared query.
	//	@param raw_arguments	Raw arguments allow you to replace ?? values in
	//							prepared statements with strings. Be aware no
	//							cleaning is done for this, and should be used as 
	//							an absolute last resort. It is only here for things
	//							like passing arrays which PDO dosen't support.
	//
	//	@returns A DatabaseResult instance.
	// -------------------------------------------------------------
	public function Query($name, $arguments = array(), $raw_arguments = array())
	{
		if ($this->m_connection == null)
		{
			$this->m_engine->Logger->InternalError("Attempt to perform query '" . $name . "' on unconnected database.");
		}
	
		if (file_exists(BASE_PATH . '/Source/Sql/' . $name . '.sql'))
		{
			$query = file_get_contents(BASE_PATH . '/Source/Sql/' . $name . '.sql');
			if ($query === FALSE)
			{
				$this->m_engine->Logger->InternalError("Failed to read prepared query with the name '" . $name . "'.");
			}
			
			// Replace prepared statements.
			if (count($raw_arguments) > 0)
			{
				$search = array_fill(0, count($raw_arguments), "?");
				$query = str_replace($search, $raw_arguments, $query);
			}
			
			$this->m_queryCount++;
			try
			{
				$stmt 					= $this->m_connection->prepare($query);
				$affected 				= $stmt->execute($arguments);
				
				$result 				= new DatabaseResult();
				$result->LastInsertID 	= $this->m_connection->lastInsertId();
				$result->RowsAfffected 	= $affected;
				$result->Rows 			= array();
				
				while ($res = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					array_push($result->Rows, $res);
				}
				
				return $result;
			}
			catch(PDOException $ex) 
			{
				$this->m_engine->Logger->InternalError("Encountered error when attempting to run query '" . $name . "':<br/><br/><i>" . $ex->getMessage() . "</i>");		
			}			
		}
		else
		{
			$this->m_engine->Logger->InternalError("Failed to find prepared query with the name '" . $name . "'.");
		}
	}
	
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
	//					"name" 		=> "herp",
	//					"type" 		=> "varchar",
	//					"size"		=> 23;
	//					"default" 	=> "herp",
	//				)
	//			)
	//
	// -------------------------------------------------------------
	public function GetTableInfo($name)
	{
		$result = $this->Query("describe_table", array(), array( $name ));
		if ($result == null)
		{
			return null;
		}
		
		$columns = array();
		foreach ($result->Rows as $row)
		{
			$type_split = explode("(", $row['Type']);
		
			$column_array = array();
			$column_array['name'] 	 = $row['Field'];
			$column_array['type'] 	 = $type_split[0];
			$column_array['size'] 	 = count($type_split) > 1 ? intval(rtrim($type_split[1], ")")) : 0;
			$column_array['default'] = $row['Default'];
			
			if ($column_array['type'] == "tinytext")
			{
				$column_array['size'] = 256;
			}
			if ($column_array['type'] == "text")
			{
				$column_array['size'] = 65535;
			}
			if ($column_array['type'] == "mediumtext")
			{
				$column_array['size'] = 16777215;
			}
			if ($column_array['type'] == "longtext")
			{
				$column_array['size'] = 4294967295;
			}
			
			$columns[$column_array['name']] = $column_array;
		}		
		
		return $columns;
	}
	
	// -------------------------------------------------------------
	//	When called the provider will attempt to disconnect
	//	from the database its connected to.
	// -------------------------------------------------------------
	public function Disconnect()
	{
		$this->m_connection = null;
	}
	
	// -------------------------------------------------------------
	//	Returns the number of queries this provider has handled.
	// -------------------------------------------------------------
	public function GetQueryCount()
	{
		return $this->m_queryCount;
	}
	
}