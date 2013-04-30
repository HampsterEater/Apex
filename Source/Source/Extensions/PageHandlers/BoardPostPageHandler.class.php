<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	boardpostpagehandler.class.php
//	Author: tim
// -------------------------------------------------------------
//	This file contains the code for handling submissions of
//	post data and inserting it into the database.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//	This class handles taking a post request and turning it
//	into delicious data.
//
//	URI for this handler is:
// 		/board-name/post
// -------------------------------------------------------------
class BoardPostPageHandler extends PageHandler
{

	// Engine that constructed this class.
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
	//	If returns true then this provider will be responsible 
	//	for handling pages with the given URI.
	// -------------------------------------------------------------
	public function CanHandleURI($uri_arguments)
	{
		if (count($uri_arguments) == 2 &&
			$uri_arguments[1] == "post")
		{
			$board_name = $uri_arguments[0];
		
			// Check first argument is a board name.
			foreach ($this->m_engine->Settings->PageSettings['boards'] as $board)
			{
				if ($board['url'] == $board_name)
				{
					return true;
				}				
			}
			
			return false;
		}
		
		return false;
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
	//	Invoked when this page handler is responsible for rendering
	//	the current page.
	// -------------------------------------------------------------
	public function RenderPage($arguments = array())
	{		
		// Load board settings.
		$posts_table_info	= $this->m_engine->Database->GetTableInfo("posts");
		$board_uri 			= $this->m_engine->Settings->URIArguments[0];
		$board 	   			= $this->m_engine->GetBoardByUri($board_uri);
		$request_vars  		= $this->m_engine->Settings->RequestValues;
		if ($board == null)
		{
			$this->m_engine->Logger->InternalError("Could not retrieve board information (for uri /{$board_uri}/) from database.");
		}
		
		// Check permissions.
		$this->m_engine->Member->AssertAllowedTo("view_board_index_page", $board['id']);
		
		// Check board is not locked.
		if ($board['is_locked'] == true)
		{
			$this->m_engine->Logger->InternalError("This board is locked, you cannot post on it at the moment.");
		}
		
		// If board is password protected, check we are logged in.
		$cookie_password_key = 'board_' . ($board['id']) . '_password';
		if ($board['password'] != '' &&
			$this->m_engine->Member->IsAllowedTo("bypass_passwords", $board['id']) == false)
		{
			if (isset($_SESSION[$cookie_password_key]) == false ||
				$_SESSION[$cookie_password_key] 	   != $board['password'])
			{
				// We could show an error here, but I think its probably better to redirect the user
				// to the login page incase its just a session expiration or something.
				BrowserHelper::RedirectExit(BASE_SCRIPT_URI . $board_uri . "/login");
				return;
			}
		}	
	
		// Check our captcha result is correct.
		if ($board['use_recaptcha'] == true)
		{
			if (!isset($request_vars["recaptcha_challenge_field"]) ||
				!isset($request_vars["recaptcha_response_field"]))
			{
				$this->m_engine->Logger->InternalError("You did not submit a reCAPTCHA response.");
			}
		
			$result = recaptcha_check_answer($this->m_engine->Settings->RecaptchaPrivateKey,
											 $_SERVER["REMOTE_ADDR"],
											 $request_vars["recaptcha_challenge_field"],
											 $request_vars["recaptcha_response_field"]);
			
			if ($result->is_valid == false)
			{
				$this->m_engine->Logger->InternalError("Your reCAPTCHA response was incorrect.");
			}
		}
		
		// Check required fields are submitted.
		if (!isset($request_vars['subject']) ||
			!isset($request_vars['comment']) ||
			!isset($request_vars['password']) ||
				(
					$board['is_forced_anonymous'] == false &&
					(
						!isset($request_vars['name']) ||
						!isset($request_vars['email'])
					)
				)
			)
		{
			$this->m_engine->Logger->InternalError("You did not submit the required fields.");					
		}	
		
		// Do not allow name/email if forced anon.
		if ($board['is_forced_anonymous'] == true &&
			(isset($request_vars['name']) || isset($request_vars['email'])))
		{
			$this->m_engine->Logger->InternalError("This board is forced anonymous, you cannot provide a name or email.");							
		}
		
		// Check that this IP has not posted during the flood delay interval.
		if ($board['flood_delay'] != 0)
		{
			$result = $this->m_engine->Database->Query("select_posts_by_ip_and_time", array( ":create_ip" 	=> $_SERVER['REMOTE_ADDR'], 
																							 ":min_time" 	=> time() - $board['flood_delay'] ));
			if (count($result->Rows) > 0)
			{
				$this->m_engine->Logger->InternalError("Flood delay is enabled on this board. Please wait {$board['flood_delay']} seconds between posts.");							
			}																			
		}
		
		// Check we have submitted something.
		$parent_id 			= isset($request_vars['parent_id']) ? intval($request_vars['parent_id']) 	: 0;
		$name 				= isset($request_vars['name']) 		? $request_vars['name'] 				: "";
		$email 				= isset($request_vars['email']) 	? $request_vars['email'] 				: "";
		$subject 			= $request_vars['subject'];
		$comment 			= $request_vars['comment'];
		$password 			= $request_vars['password'];
		$tripcode			= "";
		$is_spoiler			= isset($request_vars['is_spoiler']);
		$html_enabled 		= false;
		$topic_bump_enabled = true;
		$return_to_thread 	= false;
		
		// No password given? Generate one.
		if ($password == "")
		{
			$password = substr(hash("md5", microtime(true)), 0, 8);
		}
		
		// Allow to post reply/topic.
		if ($parent_id == 0)
		{
			$this->m_engine->Member->AssertAllowedTo("create_topics", $board['id']);
		}
		else
		{
			$this->m_engine->Member->AssertAllowedTo("create_replies", $board['id']);		
		}
				
		// Give default anonymous name if one is not supplied.
		if ($name == "")
		{
			$name = $board['anonymous_name'];
		}
		
		// If we do not allow blank posts make sure we got a comment.
		if ($board['allow_blank_posts'] == false && trim($comment) == "")
		{
			$this->m_engine->Logger->InternalError("You did not submit a comment. This board does not permit blank posts.");			
		}

		// Check we supplied a file if one is required for a topic.
		if ($board['require_upload_for_topic'] == true && 
			$parent_id != 0 &&
			count($request_vars['files']) <= 0)
		{
			$this->m_engine->Logger->InternalError("You did not submit a file. This board requires uploads when making topics.");					
		}
		
		// Too many files supplied?
		if (count($request_vars['files']) > $board['post_upload_limit'])
		{
			$this->m_engine->Logger->InternalError("You supplied more files than are allowed per post in this board.");						
		}
		
		// Grab allowed extensions.
		$allowed_file_types = array();
		if ($board['allowed_upload_file_type_ids'] != "")
		{
			$result = $this->m_engine->Database->Query("select_file_types_by_id_array", array(), array($board['allowed_upload_file_type_ids'])); 
			$allowed_file_types = $result->Rows;
		}
		
		// Generate hashs for all uploaded files.		
		foreach ($request_vars['files'] as $key => $file)
		{
			$file['hash'] = md5_file($file['tmp_name']);
			
			// Also check file size of all uploads is not beyond maximum.
			if ($file['size'] > $board['max_upload_size'])
			{
				$this->m_engine->Logger->InternalError("File size of file is beyond maximum upload size.");							
			}
			
			// And check for upload errors.
			else if ($file['error'] != UPLOAD_ERR_OK)
			{
				switch ($file['error'])
				{
					case UPLOAD_ERR_INI_SIZE:
					{					
						$this->m_engine->Logger->InternalError("Upload beyond PHP's upload_max_filesize.");							
						break;
					}
					case UPLOAD_ERR_FORM_SIZE:
					{					
						$this->m_engine->Logger->InternalError("Upload beyond size specified in HTML form.");							
						break;
					}
					case UPLOAD_ERR_PARTIAL:
					{					
						$this->m_engine->Logger->InternalError("Upload was only partially uploaded.");							
						break;
					}
					case UPLOAD_ERR_NO_TMP_DIR:
					{					
						$this->m_engine->Logger->InternalError("Temporary folder appears to be missing on server, upload failed.");							
						break;
					}
					case UPLOAD_ERR_CANT_WRITE:
					{					
						$this->m_engine->Logger->InternalError("Could not write to disk, upload failed.");							
						break;
					}
					default:
					{
						$this->m_engine->Logger->InternalError("Unspecified error occured while uploading file.");							
						break;
					}
				}
			}
			
			// And finally check upload extension.
			else
			{
				$upload_ext = FileHelper::ExtractExtension($file['name']);
				$file_type  = null;
				
				foreach ($allowed_file_types as $ft)
				{
					if (strtolower($ft['extension']) == strtolower($upload_ext))
					{
						$file_type = $ft;
						break;
					}
				}
				
				if ($file_type == NULL)
				{
					$this->m_engine->Logger->InternalError("File type is not a valid upload for this board.");									
					$this->m_engine->Logger->Log("User attempted to upload file type (" . $upload_ext . ") that is not a valid upload for board.");									
				}
				$file['file_type'] = $file_type;
			}
			
			// Apply changes.
			$request_vars['files'][$key] = $file;
		}
		
		// Check if any uploads are duplicate uploads.
		if ($board['block_duplicate_uploads'] == true)
		{
			foreach ($request_vars['files'] as $file)
			{
				$result = $this->m_engine->Database->Query("select_undeleted_posts_by_hash", array( ":upload_hash" => $file['hash'] ));
				if (count($result->Rows) > 0)
				{
					$this->m_engine->Logger->InternalError("You attempted to upload an image file that already exists on this site. Duplicate images are blocked.");							
				}	
			}
		}
		
		// Check comment is below max post length.
		$max_comment_length = min($posts_table_info['comment']['size'], $board['max_comment_length']);
		if (strlen($comment) > $max_comment_length)
		{
			$this->m_engine->Logger->InternalError("Comment is beyond the maximum allowed size for this board. Maximum comment size is {$max_comment_length} characters.");						
		}
		
		// See if we have hit any filters.		
		if ($this->m_engine->Member->IsAllowedTo("bypass_filters", $board['id']) == false)
		{
			$result = $this->m_engine->Database->Query("select_filters");
			foreach ($result->Rows as $filter)
			{
				if ($filter['fields'] 	== "" ||
					$filter['pattern'] 	== "" ||
					$filter['result'] 	== "")
				{
					continue;
				}
			
				$fields = explode(",", $filter['fields']);
				$found  = false;
				
				// Is this filter a regex?
				if (substr($filter['pattern'], 0, 1) == '/')
				{
					if (in_array('name', $fields)) 		$found = (preg_match($filter['pattern'], $name) 	== 1);
					if (in_array('email', $fields)) 	$found = (preg_match($filter['pattern'], $email) 	== 1);
					if (in_array('subject', $fields)) 	$found = (preg_match($filter['pattern'], $subject) 	== 1);
					if (in_array('comment', $fields)) 	$found = (preg_match($filter['pattern'], $comment) 	== 1);
					if (in_array('password', $fields)) 	$found = (preg_match($filter['pattern'], $password) == 1);
				}
				
				// Nope string-pos filter.
				else
				{
					if (in_array('name', $fields)) 		$found = (stripos($name, $filter['pattern']) 		!== FALSE);
					if (in_array('email', $fields)) 	$found = (stripos($email, $filter['pattern']) 		!== FALSE);
					if (in_array('subject', $fields)) 	$found = (stripos($subject, $filter['pattern']) 	!== FALSE);
					if (in_array('comment', $fields)) 	$found = (stripos($comment, $filter['pattern']) 	!== FALSE);
					if (in_array('password', $fields)) 	$found = (stripos($password, $filter['pattern']) 	!== FALSE);
				}
				
				// Have we found it?
				if ($found == true)
				{
					switch ($filter['result'])
					{
						// Revoke the post.
						case 'revoke':
						{
							$this->m_engine->Logger->InternalError("Your post contained a filtered word and was revoked.");						
							$this->m_engine->Logger->Log("Users post contained a filtered word ({$filter['pattern']}) and was revoked.");						
							break;
						}
						
						// Redirect to another URL.
						case 'redirect':
						{
							$this->m_engine->Logger->Log("Users post contained a filtered word ({$filter['pattern']}) and was redirected to: {$filter['extra']}");						
							BrowserHelper::RedirectExit($filter['extra']);
							return;
						}
						
						// Enable HTML in post.
						case 'enable-html':
						{
							if ($this->m_engine->Member->IsAllowedTo("post_with_html", $board['id']))
							{
								$html_enabled = true;
							}
							break;
						}
						
						// Replace matched values.
						case 'replace':
						{
							// Is this filter a regex?
							if (substr($filter['pattern'], 0, 1) == '/')
							{
								if (in_array('name', $fields)) 		$name		= preg_replace($filter['pattern'], $filter['extra'], $name);
								if (in_array('email', $fields)) 	$email 		= preg_replace($filter['pattern'], $filter['extra'], $email);
								if (in_array('subject', $fields)) 	$subject 	= preg_replace($filter['pattern'], $filter['extra'], $subject);
								if (in_array('comment', $fields)) 	$comment 	= preg_replace($filter['pattern'], $filter['extra'], $comment);
								if (in_array('password', $fields)) 	$password 	= preg_replace($filter['pattern'], $filter['extra'], $password);
							}
							
							// Nope string-pos filter.
							else
							{
								if (in_array('name', $fields)) 		$name		= str_ireplace($filter['pattern'], $filter['extra'], $name);
								if (in_array('email', $fields)) 	$email 		= str_ireplace($filter['pattern'], $filter['extra'], $email);
								if (in_array('subject', $fields)) 	$subject 	= str_ireplace($filter['pattern'], $filter['extra'], $subject);
								if (in_array('comment', $fields)) 	$comment 	= str_ireplace($filter['pattern'], $filter['extra'], $comment);
								if (in_array('password', $fields)) 	$password 	= str_ireplace($filter['pattern'], $filter['extra'], $password);
							}
							break;
						}
						
						// Replace the entire field.
						case 'replace-all':
						{
							if (in_array('name', $fields)) 		$name		= $filter['extra'];
							if (in_array('email', $fields)) 	$email		= $filter['extra'];
							if (in_array('subject', $fields)) 	$subject	= $filter['extra'];
							if (in_array('comment', $fields)) 	$comment	= $filter['extra'];
							if (in_array('password', $fields)) 	$password	= $filter['extra'];
							break;
						}
						
						// "Sages" a topic.
						case 'disable-topic-bump':
						{
							$topic_bump_enabled = false;
							break;
						}
						
						// Returns to thread after post.
						case 'return-to-thread':						
						{
							$return_to_thread = true;
							break;
						}
						
						// TODO: Bans the user!
						//case 'ban':
					}
				}
			}
		}

		// Check if parent_id supplied is a valid topic and is not locked.
		$topic = null;
		if ($parent_id != 0)
		{
			$topic_result = $this->m_engine->Database->Query("select_post_by_id", array( ":id" => $parent_id ));
			if (count($topic_result->Rows) != 0)
			{
				$topic = $topic_result->Rows[0];

				if ($topic['is_locked'] == true)
				{
					$this->m_engine->Logger->InternalError("Topic is locked. You cannot post in it.");											
				}
				if ($topic['parent_id'] != 0)
				{
					$this->m_engine->Logger->InternalError("Parent ID is not a topic. You cannot reply to replies!");											
				}
				if ($topic['is_deleted'] == true)
				{
					$this->m_engine->Logger->InternalError("Topic does not exist. It may have been deleted.");								
				}
				
				// TODO: Make sure you can't post to topics not shown on our board.
				//if ($this->m_engine->IsPostShownOnBoard($topic, $board) == false)
				//{
				//	$this->m_engine->Logger->InternalError("Topic is not in the board you are posting through.");														
				//}
			}
			else
			{
				$this->m_engine->Logger->InternalError("Topic does not exist. It may have been deleted.");								
			}
					
			// If we are posting to topic make sure we have not surpassed the 
			// topic upload limit.
			if ($board['topic_upload_limit'] > 0 and 
				$this->m_engine->Member->IsAllowedTo("bypass_topic_upload_limit", $board['id']) == false)
			{
				$result = $this->m_engine->Database->Query("select_undeleted_posts_with_upload_by_parent_id", array( ":parent_id" => $parent_id ));
				if (count($result->Rows) + count($request_vars['files']) > $board['topic_upload_limit'])
				{
					$this->m_engine->Logger->InternalError("Topic has passed it's upload limit, you cannot upload any new files to it.");					
				}
			}
			
			// If we are posting to topic make sure we have not surpassed the 
			// topic reply limit.
			if ($board['topic_reply_limit'] > 0 and 
				$this->m_engine->Member->IsAllowedTo("bypass_topic_reply_limit", $board['id']) == false)
			{
				$result = $this->m_engine->Database->Query("select_undeleted_posts_by_parent_id", array( ":parent_id" => $parent_id ));
				if (count($result->Rows) + count($request_vars['files']) > $board['topic_reply_limit'])
				{
					$this->m_engine->Logger->InternalError("Topic has passed it's reply limit, you cannot post new replies.");					
				}
			}
		}

		// Check no values are beyond database column lengths.
		if (strlen($name) > $posts_table_info['name']['size'])
		{
			$this->m_engine->Logger->InternalError("Name is to large to be stored in database.");								
		}
		else if (strlen($email) > $posts_table_info['email']['size'])
		{
			$this->m_engine->Logger->InternalError("Email is to large to be stored in database.");										
		}
		else if (strlen($subject) > $posts_table_info['subject']['size'])
		{
			$this->m_engine->Logger->InternalError("Subject is to large to be stored in database.");										
		}
		else if (strlen($comment) > $posts_table_info['comment']['size'])
		{
			$this->m_engine->Logger->InternalError("Comment is to large to be stored in database.");										
		}
		
		// Hash password.
		$password_salt = hash("sha512", microtime(true));
		$password_hash = hash("sha512", $password . $password_salt);
		
		// Calculate Secure tripcode.
		$trip_parts = explode("##", $name);
		if (count($trip_parts) == 2)
		{
			$name 	  = $trip_parts[0];
			$tripcode = '!!' . StringHelper::GenerateTripcode(md5($trip_parts[1] . $this->m_engine->Settings->DatabaseSettings['secure_tripcode_salt']));
		}
		else
		{
			// Calculate Normal tripcode.
			$trip_parts = explode("#", $name);
			if (count($trip_parts) == 2)
			{
				$name 	  = $trip_parts[0];
				$tripcode = '!' . StringHelper::GenerateTripcode($trip_parts[1]);			
			}
		}

		// We need to perform the following operations on all files.		
		$first_file = true;
		foreach ($request_vars['files'] as $file)
		{
			// Move uploaded file.
			$upload_directory	= $this->m_engine->Settings->UploadDirectory . date('Y/m/d') . '/';
			
			// Make sure directory exists.
			if (!is_dir($upload_directory))
			{
				mkdir($upload_directory, 0777, true);
			}
			if (!is_dir($upload_directory))
			{
				$this->m_engine->Logger->InternalError("Failed to create upload directory.");												
			}
			
			// Add filename watermark.
			$file['original_name'] = $file['name'];
			$file['name'] = FileHelper::AddFileNameWatermark($file['name'], $this->m_engine->Settings->DatabaseSettings['image_filename_watermark']);
			
			// Find a filename for us.
			$file_name = $upload_directory . FileHelper::SanitizeFileName($file['name']);
			$index     = 0;
			while (file_exists($file_name))
			{
				$file_name = $upload_directory . $index . '_' . FileHelper::SanitizeFileName($file['name']);			
				$index++;
			}
			
			// Find a thumbnail filename for us.
			$thumb_file_name = $upload_directory . 'thumb_' . FileHelper::SanitizeFileName($file['name']);
			$index     		 = 0;
			while (file_exists($file_name))
			{
				$thumb_file_name = $upload_directory . $index . '_thumb_' . FileHelper::SanitizeFileName($file['name']);			
				$index++;
			}
			
			// Move file over.
			if (move_uploaded_file($file['tmp_name'], $file_name) !== TRUE)
			{
				$this->m_engine->Logger->InternalError("Failed to move uploaded file to storage location.");												
			}
			
			// Strip EXIF.
			if ($file['file_type']['can_contain_exif'] == true)
			{
				if ($board['strip_exif'] == true)
				{
					ImageHelper::StripEXIF($file_name);
				}
			}				
			$contains_exif = ImageHelper::ContainsEXIF($file_name);
			
			// Generate thumbnail if required for this datatype.
			if ($file['file_type']['generate_thumbnail'] == true)
			{
				$size = @getimagesize($file_name);
				if ($size == NULL)
				{
					unlink($file_name);
					$this->m_engine->Logger->InternalError("Failed to create thumbnail for image.");															
				}
				
				// Is image large enough to need a thumbnail?
				if ($size[0] > $board['max_image_thumbnail_width'] || 
					$size[1] > $board['max_image_thumbnail_height'])
				{
					$result = ImageHelper::CreateImageThumbnail($file_name, $thumb_file_name, $board['max_image_thumbnail_width'], $board['max_image_thumbnail_height']);
					if ($result == false)
					{
						unlink($file_name);
						$this->m_engine->Logger->InternalError("Failed to create thumbnail for image.");															
					}
				}
				
				// No thumbnail required, our image is tiny.
				else
				{
					$thumb_file_name = "";
				}
			}
			
			// Add to database.
			$result = $this->m_engine->Database->Query("insert_post", array(
				":parent_id" 			=> $parent_id,
				":subject"	 			=> $subject,
				":email"				=> $email,
				":name"					=> $name,
				":comment"				=> $comment,
				":tripcode"				=> $tripcode,
				":upload_url"			=> $file_name,
				":upload_thumbnail_url"	=> $thumb_file_name,
				":upload_hash"			=> $file['hash'],
				":original_upload_name"	=> $file['original_name'],
				":file_type_id"			=> $file['file_type']['id'],
				":bump_time"			=> time(),
				":bump_count"			=> 1,
				":contains_exif"		=> intval($contains_exif),
				":create_ip"			=> $_SERVER['REMOTE_ADDR'],
				":create_time"			=> time()
			));
		
			// Add automatic tags to post.
			if ($board['auto_tag_ids'] != "")
			{
				$auto_tag_ids = explode(",", $board['auto_tag_ids']);
				foreach ($auto_tag_ids as $tag_id)
				{
					// TODO: Check tag exists
				
					$result = $this->m_engine->Database->Query("insert_post_tag", array(
						":post_id"				=> $result->LastInsertID,
						":tag_id"				=> intval($tag_id),
						":create_ip"			=> $_SERVER['REMOTE_ADDR'],
						":create_time"			=> time()
					));
				}
			}
			
			// If this is the first file, all subsequent posts will link to it.
			if ($first_file == true)
			{
				$comment = ">>" . $result->LastInsertID;
			}
			$first_file = false;
		}
		
		// If we posted to a topic and we are below bump limit, bump topic
		// back up to the front page!
		if ($parent_id != 0)
		{
			if ($topic['bump_count'] < $board['topic_bump_limit'])
			{
				$this->m_engine->Database->Query("bump_post", array( ":id" => $parent_id, ":time" => time() ));
			}
		}
		
		// Update cookie to store persistent values.
		$this->m_engine->Settings->RequestValues["cookies"]['name'] 	= $name;
		$this->m_engine->Settings->RequestValues["cookies"]['email'] 	= $email;
		$this->m_engine->Settings->RequestValues["cookies"]['password'] = $password;
		$this->m_engine->StoreCookieSettings();
		
		// Remove any topics that have fallen off the end of the board.
		if ($board['max_pages'] > 0)
		{
			$max_topics = $board['max_pages'] * $board['topics_per_page'];
			
			
			
		}
		
		// If new topic, remove cache of board index.
		if ($parent_id == 0)
		{
			$this->m_engine->Cache->RemoveByPattern("*/b/*/");
		}		

		// If reply, remove cache of topic and board index.
		else
		{
			$this->m_engine->Cache->RemoveByPattern("*/b/thread/" . $parent_id . '/');
			$this->m_engine->Cache->RemoveByPattern("*/b/*/");
		}
		
		// Return to board.
		if ($return_to_thread == true)
		{
			BrowserHelper::RedirectExit(BASE_SCRIPT_URI . $board_uri . "/thread/" . $post_id);	
		}
		else
		{
			BrowserHelper::RedirectExit(BASE_SCRIPT_URI . $board_uri . "/");
		}
	}
	
}