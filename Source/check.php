<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	check.php
//	Author: tim
// -------------------------------------------------------------
// 	Checks that our installation is valid and that we 
//	can correctly function on this server.
// -------------------------------------------------------------
//	Note: Do not include any files from this script, everything
//		  needs to be self-contained as we have to assume 
//		  everything we want to use could potentially be missing.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// What versions of PHP we support.
$SUPPORTED_PHP_VERSIONS = array("5.2", "5.3", "5.4");

// What directories we have to have.
$WHITE_DIRECTORIES   = array("Source", 
								"Source/Core",
									"Source/Core/Helpers",
									"Source/Core/Libraries",
										"Source/Core/Libraries/Twig",
								"Source/Extensions",
									"Source/Extensions/CacheProviders",
									"Source/Extensions/HookProviders",
									"Source/Extensions/PageHandlers",
								"Source/Sql",
							 "Themes",
								"Themes/Default", 
									"Themes/Default/StyleSheets",
									"Themes/Default/JavaScript",
									"themes/Default/Templates",
									"themes/Default/Languages",
							 "Cache",
								"Cache/Templates");

// What directories we are not allowed to have.
$BLACK_DIRECTORIES   = array("Install");

// What files we have to have.
$WHITE_FILES   		 = array("index.php",
							 "check.php",
							 "php.ini",
								"Source/Core/Settings.class.php",
								"Source/Core/Engine.class.php",
								"Source/Core/Logger.class.php",
								"Source/Core/CacheProvider.class.php",
								"Source/Core/HookProvider.class.php",
								"Source/Core/DatabaseProvider.class.php",
								"Source/Core/PageHandler.class.php",
								"Source/Core/Language.class.php",
								
								"Source/Core/Helpers/StringHelper.class.php",
								"Source/Core/Helpers/FileHelper.class.php",
								"Source/Core/Helpers/BrowserHelper.class.php",
								"Source/Core/Helpers/ReflectionHelper.class.php",
								
								"Source/Core/Libraries/Twig/Autoloader.php");

// What files we are not allowed to have.
$BLACK_FILES   		 = array();

// -------------------------------------------------------------
// This function just emits a nice "failed check" screen.
//
//	@param content Error text to display on failure page.
// -------------------------------------------------------------
function fail_install_check($content)
{
	header("Status: 500 Internal Server Error");
	die("
		<!DOCTYPE html>
		<html>
			<head>
				<title>Install Check Failed</title>
			</head>
			<body>
				<h2>Install Check Failed</h2>
				" . $content . "
				<br/>
				<br/>
				Installation validation checks can be removed by deleting check.php.<br/>
				However it's suggested you solve the issues rather than ignore them!<br/>
				<br/>
				<hr/>
				<span style='font-size: 75%;'>" . SOFTWARE_SIGNATURE . ", " . $_SERVER['SERVER_SOFTWARE'] . "</span>
			</body>
		</html>
	");
}

// -------------------------------------------------------------
//	Check our PHP version is valid.
// -------------------------------------------------------------

// Strip off release version# from php, we only care about major/minor versions.
$php_version_exploded   = explode('.', phpversion());
$php_version_no_release = $php_version_exploded[0] . '.' . $php_version_exploded[1];

if (!in_array($php_version_no_release, $SUPPORTED_PHP_VERSIONS))
{
	fail_install_check("
		This server is running PHP version: <b>{$php_version_no_release}</b>.<br/>
		This software only supports versions: <b>" . implode(" - ", $SUPPORTED_PHP_VERSIONS) . "</b>
	");
}

// -------------------------------------------------------------
//	Check magic quotes are not enabled.
// -------------------------------------------------------------
if (get_magic_quotes_runtime())
{
	fail_install_check("
		This server has magic quotes enabled!<br/>
		Please disable before attempting to run this software.
	");
} 

// -------------------------------------------------------------
//	Check for GD.
// -------------------------------------------------------------
if (!extension_loaded('gd'))
{
	fail_install_check("
		Server does not have the GD (<a href='http://www.php.net/manual/en/image.setup.php'>Download Link</a>) extension installed and enabled.<br/>
		This software requires GD to run.
	");
} 

// -------------------------------------------------------------
// Check settings file exists, if it dosen't then we need to install
// so direct user to install directory.
// -------------------------------------------------------------
if (!file_exists(BASE_PATH . "source/core/settings.class.php"))
{
	if (!file_exists(BASE_PATH . "install/index.php"))
	{
		fail_install_check("
			Settings file does not exist and neither does installation folder.<br/>
			WHAT HAVE YOU DONE D:<br/>
		");
	}
	else
	{
		header("Location: " . BASE_URI_DIR . "install/index.php");
		exit();
	}
}

// -------------------------------------------------------------
//	Check whitelisted directories are there
// -------------------------------------------------------------
foreach ($WHITE_DIRECTORIES as $dir)
{
	if (!is_dir(BASE_PATH . $dir))
	{
		fail_install_check("
			Expected directory does not exist: <b>" . BASE_PATH . "{$dir}</b>.<br/>
		");
	}
}
// -------------------------------------------------------------
//	Check whitelisted files are there
// -------------------------------------------------------------
foreach ($WHITE_FILES as $file)
{
	if (!file_exists(BASE_PATH . $file))
	{
		fail_install_check("
			Expected file does not exist: <b>" . BASE_PATH . "{$file}</b>.
		");
	}
}

// -------------------------------------------------------------
//	Check blacklisted directories are there
// -------------------------------------------------------------
foreach ($BLACK_DIRECTORIES as $dir)
{
	if (is_dir(BASE_PATH . $dir))
	{
		fail_install_check("
			Software cannot be used while the following directory exists: <b>" . BASE_PATH . "{$dir}</b>.
		");
	}
}
// -------------------------------------------------------------
//	Check blacklisted files are there
// -------------------------------------------------------------
foreach ($BLACK_FILES as $file)
{
	if (file_exists(BASE_PATH . $file))
	{
		fail_install_check("
			Software cannot be used while the following file exists: <b>" . BASE_PATH . "{$file}</b>.
		");
	}
}

	
	