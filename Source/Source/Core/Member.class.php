<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	member.class.php
//	Author: tim
// -------------------------------------------------------------
//	This file contains the member class. This class deals with 
//	logging loading and storing information on a single member.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//  This class deals with loading and containing information
//	about a given member. This is primarily used for the login
//	system.
// -------------------------------------------------------------
class Member
{
	private $m_engine;

	public $ID;
	public $IsVisitor;
	public $Settings;
	public $Usergroups;
	
	// -------------------------------------------------------------
	//  Constructs this class.
	//
	//	@param engine Instance of engine that constructed this
	//				  class.
	//	@param id	  Numeric ID in database of member.
	// -------------------------------------------------------------
	public function __construct($engine, $id)
	{
		$this->m_engine 	= $engine;
		$this->ID 			= $id;
		$this->IsVisitor 	= ($engine->Settings->DatabaseSettings['visitor_member_id'] == $id); 
	}
	
	// -------------------------------------------------------------
	//  Loads all information about the member from the database.
	//
	//	@returns True if successful.
	// -------------------------------------------------------------
	public function LoadFromDatabase()
	{
		// Load member settings from database.		
		$result = $this->m_engine->Database->Query("select_member_by_id", array(":id" => $this->ID));
		if (count($result->Rows) != 1)
		{
			return false;
		}	
		$this->Settings = $result->Rows[0];

		// Load usergroups from database.
		$result 		  = $this->m_engine->Database->Query("select_member_usergroups_by_id", array(":id" => $this->ID));
		$this->Usergroups = $result->Rows;

		for ($i = 0; $i < count($this->Usergroups); $i++)
		{
			$this->Usergroups[$i]['permissions'] 			= explode(",", $this->Usergroups[$i]['permissions']);
			$this->Usergroups[$i]['has_all_permissions'] 	= in_array("all", $this->Usergroups[$i]['permissions']);

			$this->Usergroups[$i]['has_all_boards']	  		= ($this->Usergroups[$i]['boards'] == "all");			
			$this->Usergroups[$i]['boards'] 	 			= array_map('intval', explode(",", $this->Usergroups[$i]['boards']));
		}
		 
		return true;
	}
	
	// -------------------------------------------------------------
	//  Checks if the user has permission to to perform the given
	//	action on the given board.
	//
	//	@param	 action		Permission to check for.
	//	@param	 board		Board to check permission on.
	//
	//	@returns True if you can.
	// -------------------------------------------------------------
	public function IsAllowedTo($action, $board = -1)
	{
		foreach ($this->Usergroups as $usergroup)
		{			
			// Is usergroup valid on this board?
			if ($board != -1 && 
				in_array($board, $usergroup['boards']) == false &&
				$usergroup['has_all_boards'] == false)
			{
				continue;
			}
			
			// God mode?
			if ($usergroup['has_all_permissions'] == true)
			{
				return true;
			}
			
			// Go through all permissions in usergroup.
			foreach ($usergroup['permissions'] as $permission)
			{
				if ($permission == $action)
				{
					return true;
				}
			}
		}
		
		return false;
	}
	
}