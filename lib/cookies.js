/*
 * Will set a new cookie to send in HTTP further on
 */
function setCookie(name, value, days)
{
	if(days) //If an amount of days was specified the cookie will have a "best-before-date", if it exceeded, the cookie will be deleted
	{
		var date = new Date(); //Supplied time is relative to now
		date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000)); //Calculate real time as GMTString
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = ""; //Else it will never expire
	document.cookie = name + "=" + value + expires + "; path=/"; //Set cookie to DOM
}
/*
 * Will return the value of the specified cookie
 */
function getCookie(name)
{
	var nameEQ = name + "=";
	var ca = document.cookie.split(';'); //Split at ; as the cookie ist store this way: "foo=blah;expiration;path=/foo/blah"
	for(var i = 0;i < ca.length;i++) //Go through all elements in the array
	{
		var c = ca[i];
		while (c.charAt(0) == ' ') c = c.substring(1, c.length); //Trim whitespaces
		if(c.indexOf(nameEQ) == 0) {//If the value is empty after trimming or the original content, then it is a malformed cookie or a cookie without a value
			return c.substring(nameEQ.length, c.length); //Only return if it is wellformed and has a value
		}
	}
	return undefined; //If the cookie was not found
}
/*
 * Will delete the specified cookie
 * The cookie will no longer be transmitted via HTTP
 */
function deleteCookie(name)
{
	setCookie(name, "", -1);
}
