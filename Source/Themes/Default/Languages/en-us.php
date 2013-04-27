<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	en-us.php
//	Author: tim
// -------------------------------------------------------------
//	This file contains the US english language strings for the
//	defualt theme. This is used as the base language file, so
//	if other language files do not implement strings, the ones
//	from here will be used instead.
// -------------------------------------------------------------

// -------------------------------------------------------------
//	Locale strings.
// -------------------------------------------------------------
$LANG['DATE_FORMAT'] 	 	= 'd/m/y';
$LANG['TIME_FORMAT'] 	 	= 'H:i:s';
$LANG['DATETIME_FORMAT'] 	= 'd/m/Y H:i:s';

// -------------------------------------------------------------
//	Header strings.
// -------------------------------------------------------------
$LANG['MENU_HOME']	   	   	= "Home";
$LANG['MENU_PREFERENCES']  	= "Preferences";
$LANG['MENU_MANAGE']	   	= "Manage";

// -------------------------------------------------------------
//	Footer strings.
// -------------------------------------------------------------
$LANG['COPYRIGHT_TEXT']	   	= "Apex ImageBoard Software (C) TwinDrills 2013";
$LANG['GENERATION_STRING'] 	= "Generated in %dms with %d query(s) at %s";

// -------------------------------------------------------------
//	Generic.
// -------------------------------------------------------------
$LANG['GO_BACK_LINK']	   	= "Go Back";

// -------------------------------------------------------------
//	Board Index.
// -------------------------------------------------------------
$LANG['BOARD_INDEX_LOCKED'] = "Board is locked, topics and replies cannot be posted at this time.";

// -------------------------------------------------------------
//	Management page.
// -------------------------------------------------------------
$LANG['MANAGE_TITLE'] 				= "Management Panel";
$LANG['NO_USERNAME']  				= "No username was given.";
$LANG['NO_PASSWORD']  				= "No password was given.";
$LANG['INVALID_LOGIN']  			= "The login provided was invalid.";
$LANG['ACCOUNT_DISABLED']			= "The login provided has been disabled.";
$LANG['USERNAME_LABEL']				= "Username";
$LANG['PASSWORD_LABEL']				= "Password";
$LANG['FORGOTTEN_PASSWORD_LINK']	= "Forgotten Password?";
$LANG['INCOMPLETE_FORM']			= "Please fill in all fields.";
$LANG['INVALID_PASSWORD']  			= "The password given was not correct.";
$LANG['UNCONFIRMED_PASSWORD']  		= "New password and confirmation do not match.";
$LANG['OLD_PASSWORD_LABEL']  		= "Old Password";
$LANG['NEW_PASSWORD_LABEL']  		= "New Password";
$LANG['CONFIRM_PASSWORD_LABEL']  	= "Confirm Password";
$LANG['EMAIL_PASSWORD_LABEL']		= "Email Address";
$LANG['CHANGE_PASSWORD_SUCCESS']	= "Password Changed Successfully";
$LANG['RESET_PASSWORD_EMAIL_SENT']	= "If email is valid, a password reset link will be sent.";
$LANG['RESET_PASSWORD_EMAIL']		= "Hello %s,

This email has been sent to you because a password reset was requested for your account on %s.

To reset your password please follow this link;
%s

If you didn't request this reset, please ignore this email.

From,
The %s Team
";
$LANG['RESET_PASSWORD_SUBJECT']		= "%s Password Reset";
$LANG['RESET_PASSWORD_SENDER_EMAIL']= "noreply@%s";

// -------------------------------------------------------------
//	404 Not Found.
// -------------------------------------------------------------
$LANG['404_ERROR_TITLE'] 	= '404 Not Found';
$LANG['404_ERROR_MESSAGE'] 	= 'The page you requested could not be found.';

// -------------------------------------------------------------
//	500 Internal Server.
// -------------------------------------------------------------
$LANG['500_ERROR_TITLE'] 	= '500 Internal Server Error';