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
	//
	//	@returns A DatabaseResult instance.
	// -------------------------------------------------------------
	public function Query($name, $arguments = array())
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
	//	When called the provider will attempt to disconnect
	//	from the database its connected to.
	// -------------------------------------------------------------
	public function Disconnect()
	{
		$this->m_connection = null;
	}
	
}