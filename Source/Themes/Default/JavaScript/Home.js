/* -----------------------------------------------------------
	Apex Imageboard Software
	Copyright (C) 2013 TwinDrills, All Rights Reserved
	----------------------------------------------------------
	File: 	home.js
	Author: tim
	----------------------------------------------------------
 	Contains code specific to the home page. Namely code
	to switch between news pages.
 ------------------------------------------------------------- */
 
// Executed when the document loads. Changes to the correct
// news page.
$(document).ready(function() 
{
	$selected_page = window.location.hash.substring(1);

	// If we have been given a page in the hash, select
	// that one.
	if ($selected_page != "")
	{
		if (select_news_menu($selected_page) == true)
		{
			return;
		}
	}
	
	// Otherwise select the first available page.
	$('.news_page').each(function(i, obj) 
	{
		select_news_menu($(obj).attr('id'));
		return false;
	});
	
});

// Selects a given menu item!
function select_news_menu(name)
{	
	// Hide other tabs.
	$('.news_page').each(function(i, obj) 
	{
		$(obj).css('display', 'none');
	});
	$('.news_button_selected').each(function(i, obj) 
	{
		$(obj).attr('class', 'news_button');
	});

	// Select the page.
	var object = $("#" + name);
	if (object != null)
	{
		object.css('display', 'block');
	}	
	else
	{	
		return false;
	}

	// Select tab button.
	var object = $("#" + name + "_button");
	if (object != null)
	{
		object.attr('class', 'news_button_selected');
	}		
	else
	{	
		return false;
	}
	
	return true;
}