/* -----------------------------------------------------------
	Apex Imageboard Software
	Copyright (C) 2013 TwinDrills, All Rights Reserved
	----------------------------------------------------------
	File: 	boardindex.js
	Author: tim
	----------------------------------------------------------
 	Contains code specific to the boardindex page. Its main
	purpose really is doing things like filling in saved
	values in forms.
 ------------------------------------------------------------- */
 
// Executed when the document loads. Changes to the correct
// news page.
$(document).ready(function() 
{
	// Load all cookie settings.
	var cookie_settings = JSON.parse(decodeURIComponent(getCookie(COOKIE_NAME)));
	
	// For all fields that have a saved_field attribute, restore
	// their value from cookies.
	$('input[saved_field]').each(function(i, obj) 
	{
		obj = $(obj);

		var saved_field_name = obj.attr('saved_field');
		if (saved_field_name != "")
		{
			if (saved_field_name in cookie_settings)
			{
				obj.val(cookie_settings[saved_field_name]);
			}
		}
	});
		
});

// Changes the number of file upload boxs that are displayed.
function set_file_count(count)
{
	for (var i = 1; i <= count; i++)
	{
		$('#file' + i + '_row').css('display', 'table-row');
	}
}