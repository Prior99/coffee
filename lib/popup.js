function displayPopup(header, text) {
	var popup = $("<div class='popup'><h1>" + header + "</h1><p>" + text + "</p></div>");
	$("div.wrapper").append(popup);
	setTimeout(function() {
		popup.css({"opacity" : "1.0"});
		popup.click(function() {
			popup.css({"opacity" : "0.0"});
			setTimeout(function() {
				popup.remove();
			}, 100)
		});
	}, 2);
}
