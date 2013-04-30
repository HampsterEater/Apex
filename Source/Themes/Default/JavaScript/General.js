/* -----------------------------------------------------------
	Apex Imageboard Software
	Copyright (C) 2013 TwinDrills, All Rights Reserved
	----------------------------------------------------------
	File: 	general.js
	Author: tim
	----------------------------------------------------------
 	Contains code general code used in several different pages.
 ------------------------------------------------------------- */
 
// All this cookie code is old shit from ib4f. Can't remember 
// for the life of me where it actually came from. Pretty
// sure I didn't write it myself.
 
function setCookie(name, value)
{
	if (name != '')
	{
		document.cookie = name + '=' + value+ '; path=/';
	}
}

function getCookie(name)
{
	if (name == '')
	{
		return '';
	}

	name_index = document.cookie.indexOf(name + '=');

	if (name_index == -1)
	{
		return '';
	}

	cookie_value =  document.cookie.substr(name_index + name.length + 1, document.cookie.length);

	end_of_cookie = cookie_value.indexOf(';');
	if (end_of_cookie != -1)
		cookie_value = cookie_value.substr(0, end_of_cookie);

	space = cookie_value.indexOf('+');
	while (space != -1)
	{ 
		cookie_value = cookie_value.substr(0, space) + ' ' + 
		cookie_value.substr(space + 1, cookie_value.length);		 
		space = cookie_value.indexOf('+');
	}

	return cookie_value;
}

function clearCookie(name)
{                  
	expires = new Date();
	expires.setYear(expires.getYear() - 1);
	document.cookie = name + '=null' + '; expires=' + expires; 		 
}
         
function clearCookies()
{
	Cookies = document.cookie;
	Cookie = Cookies;
	expires = new Date();
	expires.setYear(expires.getYear() - 1);

	while (Cookie.length > 0)
	{
		Cookie = Cookies.substr(0, Cookies.indexOf(';'));
		Cookies = Cookies.substr(Cookies.indexOf(';') + 1, Cookies.length);
	
		if (Cookie != '')
			document.cookie = Cookie + '; expires=' + expires;
		else
			document.cookie = Cookies + '; expires=' + expires;			  			  	  
	}		 		 
}