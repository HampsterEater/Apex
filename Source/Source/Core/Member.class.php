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
	private static $g_allPermissions;

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
		// Load all permissions/usergroups.
		if (Member::$g_allPermissions == null)
		{
			$result = $this->m_engine->Database->Query("select_permissions");
			Member::$g_allPermissions = $result->Rows;
		}
	
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
			// Are we given all permissions or just some?
			if ($this->Usergroups[$i]['permission_ids'] == "all")
			{
				$this->Usergroups[$i]['permission_ids'] = array();
				foreach (Member::$g_allPermissions as $permission)
				{			
					array_push($this->Usergroups[$i]['permission_ids'], $permission['id']);
				}
			}
			else
			{
				$this->Usergroups[$i]['permission_ids'] = array_map('intval', explode(",", $this->Usergroups[$i]['permission_ids']));
			}
			
			// Are we given all boards or just some?
			if ($this->Usergroups[$i]['board_ids'] == "all")
			{
				$this->Usergroups[$i]['board_ids'] = array();
				foreach ($this->m_engine->Settings->PageSettings['boards'] as $board)
				{			
					array_push($this->Usergroups[$i]['board_ids'], $board['id']);
				}
			}
			else
			{
				$this->Usergroups[$i]['board_ids'] = array_map('intval', explode(",", $this->Usergroups[$i]['board_ids']));
			}
		}
		 
		return true;
	}
	
	// -------------------------------------------------------------
	//  Finds the numeric ID of a permission given its name.
	//
	//	@param	 action		Permission to check for.
	//
	//	@returns ID if one can be found, otherwise null.
	// -------------------------------------------------------------
	public function FindPermissionID($action)
	{
		foreach (Member::$g_allPermissions as $permission)
		{			
			if ($permission['name'] == $action)
			{
				return $permission['id'];
			}
		}
		return 0;
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
		$action_id = $this->FindPermissionID($action);
	
		foreach ($this->Usergroups as $usergroup)
		{			
			// Is usergroup valid on this board?
			if ($board != -1 && 
				in_array($board, $usergroup['board_ids']) == false)
			{
				continue;
			}
			
			// Go through all permissions in usergroup.
			foreach ($usergroup['permission_ids'] as $permission)
			{
				if ($permission == $action_id)
				{
					return true;
				}
			}
		}
		
		return false;
	}
		
	// -------------------------------------------------------------
	//  Checks if the user has permission to to perform the given
	//	action on the given board, if not it redirects to a 
	//	permission error screen.
	//
	//	@param	 action		Permission to check for.
	//	@param	 board		Board to check permission on.
	//
	//	@returns True if you can.
	// -------------------------------------------------------------
	public function AssertAllowedTo($action, $board = -1)
	{
		if (!$this->IsAllowedTo($action, $board))
		{
			$this->m_engine->Logger->PermissionDeniedError("You do not have permission to view this page.");
		}
	}
}