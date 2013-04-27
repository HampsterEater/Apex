<?php

// -------------------------------------------------------------
//	Apex Imageboard Software
//	Copyright (C) 2013 TwinDrills, All Rights Reserved
// -------------------------------------------------------------
//	File: 	browserhelper.class.php
//	Author: tim
// -------------------------------------------------------------
//	Contains several commonly used helper functions for dealing
//	with different browsers.
// -------------------------------------------------------------

// Check we are not being accessed directly.
if (!defined("ENTRY_POINT"))
{
	die("This file should not be accessed directly.");
}

// -------------------------------------------------------------
//	Contains several commonly used helper functions for dealing
//	with different browsers.
// -------------------------------------------------------------
class BrowserHelper
{

	// -------------------------------------------------------------
	//	Cracks the given URI and returns all arguments.
	//
	//		The URI:
	//			127.0.0.1/apex/index.php/thread/12312
	//
	//		Will Return:
	//			array( "thread", "12312" );
	//
	//	@returns Array containing cracked uri arguments.
	// -------------------------------------------------------------
	public static function CrackURIArguments($uri, $index_page = "index.php")
	{
		$cracked = explode('/', $uri);
		while (true)
		{
			$val = $cracked[0];
			array_shift($cracked);

			if ($val == $index_page)
			{
				break;
			}
		}
		
		return $cracked;
	}

	// -------------------------------------------------------------
	//	Takes a user agent and attempts to identify some useful bits
	//	if info about the users browser from it. This function
	//	needs to be as optimal as possible, so don't try to detect
	//	everything, only what is needed.
	//
	//	@param agent User agent string to parse.
	//
	//	@returns Array containing different bits of information 
	//			 extracted from the user agent.
	// -------------------------------------------------------------
	public static function ParseUserAgent($agent)
	{
		$result = array(
			"user_agent"		=> $agent,
			"is_mobile" 		=> false,
			"is_bot"			=> false,
		);
		
		// Lowercase that agent!
		$agent = strtolower($agent);
		
		// Work out if bot.
		if (preg_match("#spider|crawl|slurp|bot|validator#", $agent, $match)) 
		{ 
			$result["is_bot"] = true;
		} 
			
		// Work out if mobile.
		// Based on WURFL stuff. Should detect the majority of mobile browsers quickly and easily. Bit ugly to look at though :).
		if (strpos($agent, 'mobile') !== false || strpos($agent, 'android') !== false)
		{
			$result["is_mobile"] = true;
		}
		else if (preg_match('/android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/', $agent))
		{
			$result["is_mobile"] = true;
		}
		else if (preg_match('/(bolt\/[0-9]{1}\.[0-9]{3})|nexian(\s|\-)?nx|(e|k)touch|micromax|obigo|kddi\-|;foma;|netfront/', $agent))
		{
			$result["is_mobile"] = true;
		}
		else if (preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/', substr($agent, 0, 4)))
		{
			$result["is_mobile"] = true;
		}		
		
		return $result;
	}

	// -------------------------------------------------------------
	//	Redirects the user to the given page and exits.
	// -------------------------------------------------------------
	public static function RedirectExit($location)
	{
		header("Location: " . $location);
		exit(0);
	}	
	
}