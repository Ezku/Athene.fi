jQuery(document).ready(function() {
	var noisy = function(e) {
		return jQuery(e).noisy({
	 	    'intensity' : 5,
		    'size' : 200,
		    'opacity' : 0.05,
		    'fallback' : '',
		    'monochrome' : true
		});
	};
	// Footer background
	noisy('body, footer');
	// Content area background
	noisy('#page');
	// Header background
	noisy('header#branding');
	
	// Content block backgrounds
	noisy('#main .menu-item .intro');
	noisy('.entry-content');
});