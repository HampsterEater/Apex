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
$LANG['MENU_HOME']	   	   		= "Home";
$LANG['MENU_PREFERENCES']  		= "Preferences";
$LANG['MENU_MANAGE']	   		= "Manage";
$LANG['MENU_LOGOUT_OF_BOARD']	= "Log out of /%s/";

// -------------------------------------------------------------
//	Footer strings.
// -------------------------------------------------------------
$LANG['COPYRIGHT_TEXT']	   	= "Apex ImageBoard Software (C) TwinDrills 2013";
$LANG['GENERATION_STRING'] 	= "Generated in %dms with %d query(s) at %s";

// -------------------------------------------------------------
//	Generic.
// -------------------------------------------------------------
$LANG['GO_BACK_LINK']	   					= "Go Back";
$LANG['SUBMIT']	   							= "Submit";
$LANG['APPLY_CHANGES']	 					= "Apply Changes";
$LANG['POST_BOX_NAME']						= "Name";
$LANG['POST_BOX_EMAIL']						= "Email";
$LANG['POST_BOX_SUBJECT']					= "Subject";
$LANG['POST_BOX_COMMENT']					= "Comment";
$LANG['POST_BOX_VERIFICATION']				= "Verification";
$LANG['POST_BOX_RECAPTCHA_PLACEHOLDER']		= "reCAPTCHA Challenge (Required)";
$LANG['POST_BOX_FILE']						= "File %d";
$LANG['POST_BOX_PASSWORD']					= "Password";
$LANG['POST_BOX_EXTRA']						= "Extra";
$LANG['POST_BOX_IS_SPOILER']				= "Is spoiler?";
$LANG['POST_BOX_SUPPORTED_FILE_TYPES']		= "Supported file types are: %s";
$LANG['POST_BOX_MAX_FILE_SIZE_ALLOWED']		= "Maximum file size allowed is: %s";
$LANG['POST_BOX_IMAGE_THUMBNAIL_SIZE']		= "Images larger than %d x %d will be thumbnailed.";
$LANG['POST_BOX_SHOW_POST_TAGS']			= "This board shows all posts tagged with: %s.";
$LANG['POST_BOX_AUTO_POST_TAGS']			= "This board automatically tags posts with: %s.";
$LANG['POST_BOX_UPLOAD_REQUIRED_FOR_TOPIC']	= "Upload required to create topic.";
$LANG['POST_BOX_EMPTY_COMMENTS_NOT_ALLOWED']= "Empty comments are not allowed.";
$LANG['POST_BOX_DUPLICATE_UPLOADS_BLOCKED']	= "Duplicate uploads are blocked.";

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
$LANG['LOGIN_TIMEOUT']				= "You have failed to login to many times.<br/><br/>You can now no longer login for %s seconds after your last failed attempt.";
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
$LANG['RESET_PASSWORD_SUBJECT']			= "%s Password Reset";
$LANG['RESET_PASSWORD_SENDER_EMAIL']	= "noreply@%s";
$LANG['MANAGE_MENU_ACCOUNT']			= "Account";
$LANG['MANAGE_MENU_CHANGE_PASSWORD']	= "Change Password";
$LANG['MANAGE_MENU_LOG_OUT']			= "Log Out";
$LANG['MANAGE_MENU_ADMINISTRATION']		= "Administration";
$LANG['MANAGE_MENU_OVERVIEW']			= "Overview";
$LANG['MANAGE_MENU_MANAGE_SETTINGS']	= "Manage Settings";
$LANG['MANAGE_MENU_MANAGE_BOARDS']		= "Manage Boards";
$LANG['MANAGE_MENU_MANAGE_NEWS']		= "Manage News";
$LANG['MANAGE_MENU_MANAGE_FILTERS']		= "Manage Filters";
$LANG['MANAGE_MENU_MANAGE_THEMES']		= "Manage Themes";
$LANG['MANAGE_MENU_MANAGE_MEMBERS']		= "Manage Members";
$LANG['MANAGE_MENU_FILE_MANAGER']		= "File Manager";
$LANG['MANAGE_MENU_MODERATION']			= "Moderation";
$LANG['MANAGE_MENU_MANAGE_BANS']		= "Manage Bans";
$LANG['MANAGE_MENU_VIEW_BAN_APPEALS']	= "View Ban Appeals";
$LANG['MANAGE_MENU_VIEW_DELETED_POSTS']	= "View Deleted Posts";
$LANG['MANAGE_MENU_VIEW_REPORTED_POSTS']= "View Reported Posts";
$LANG['MANAGE_MENU_VIEW_RECENT_POSTS']	= "View Recent Posts";
$LANG['MANAGE_MENU_VIEW_LOGS']			= "View Logs";
$LANG['MANAGE_MENU_MANAGE_TAGS']		= "Manage Tags";
$LANG['MANAGE_MENU_MANAGE_LANGUAGES']	= "Manage Languages";

// -------------------------------------------------------------
//	Preferences page.
// -------------------------------------------------------------
$LANG['PREFERENCES']							= "Preferences";
$LANG['PREFERENCES_DESCRIPTION']				= "Configure your personal settings.";
$LANG['PREFERENCES_MOBILE_SITE_TITLE']			= "Use Mobile Site?";
$LANG['PREFERENCES_MOBILE_SITE_DESCRIPTION']	= "If enabled the site will be rendered in a layout designed for small touch screen devices.";
$LANG['PREFERENCES_THEME_TITLE']				= "Choose Theme";
$LANG['PREFERENCES_THEME_DESCRIPTION']			= "Different themes render the site differently. Try them out and see what you like!";
$LANG['PREFERENCES_LANGUAGE_TITLE']				= "Choose Language";
$LANG['PREFERENCES_LANGUAGE_DESCRIPTION']		= "Allows you to localize the template text into different languages.";
$LANG['PREFERENCES_TIMEZONE_TITLE']				= "Choose TimeZone";
$LANG['PREFERENCES_TIMEZONE_DESCRIPTION']		= "Selecting different time-zones will change how dates and times are displayed throughout the site.";
$LANG['PREFERENCES_APPLIED_SUCCESSFULLY']		= "Preferences applied successfully.";

// -------------------------------------------------------------
//	Home page.
// -------------------------------------------------------------
$LANG['MANAGEMENT_PANEL_HOME_TITLE']		= "Management Panel";
$LANG['MANAGEMENT_PANEL_HOME_DESCRIPTION']	= "Welcome to the management panel.<br/>
<br/>
From here you can configure different aspects of this software, by following the links available on the left.<br/>
<br/>
If you are not an administrator you will likely only have limited access to this panel, enough to do your job only.<br/>";
$LANG['MANAGEMENT_PANEL_HOME_STATISTICS']	= "Statistics";
$LANG['MANAGEMENT_PANEL_HOME_GRAPHS']		= "Graphs";

// -------------------------------------------------------------
//	404 Not Found.
// -------------------------------------------------------------
$LANG['404_ERROR_TITLE'] 	= '404 Not Found';
$LANG['404_ERROR_MESSAGE'] 	= 'The page you requested could not be found.';

// -------------------------------------------------------------
//	403 Forbidden
// -------------------------------------------------------------
$LANG['403_ERROR_TITLE'] 	= '403 Forbidden';
$LANG['403_ERROR_MESSAGE'] 	= 'The do not have the required permission to access this page.';

// -------------------------------------------------------------
//	500 Internal Server.
// -------------------------------------------------------------
$LANG['500_ERROR_TITLE'] 	= '500 Internal Server Error';