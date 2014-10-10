function displayPopup(header, text) {
	var popup = $("<div class='popup'><h1>" + header + "</h1><p>" + text + "</p></div>");
	$("div.wrapper").append(popup);
	var wrapper = {
		child : popup,
		remove : function() {
			popup.css({"opacity" : "0.0"});
			setTimeout(function() {
				popup.remove();
			}, 100);
		}
	};
	setTimeout(function() {
		popup.css({"opacity" : "1.0"});
		popup.click(function() {
			wrapper.remove();
		});
	}, 2);
	return wrapper;
}

