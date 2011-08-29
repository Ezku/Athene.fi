$(document).ready(function() {
	var noisy = function(e) {
		return $(e).noisy({
	 	    'intensity' : 5,
		    'size' : 200,
		    'opacity' : 0.05,
		    'fallback' : '',
		    'monochrome' : true
		});
	};
	noisy('header').css('background-color', '#79B654');
	noisy('#main').css('background-color', '#F3F4F0');
	noisy('body, footer').css('background-color', '#1E2626');
});