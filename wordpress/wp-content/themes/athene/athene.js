jQuery(document).ready(function() {
	// Apply background noise
	(function(noisy) {
		// Footer background
		noisy('body, footer#colophon');
		// Content area background
		noisy('#page');
		// Header background
		noisy('header#branding');

		// Content block backgrounds
		noisy('#main .menu-item .intro');
		noisy('.entry-content');
		noisy('.widget-content, .gce-widget-list, .wp-polls');
	})(function(e) {
		return jQuery(e).noisy({
	 	    'intensity' : 5,
		    'size' : 200,
		    'opacity' : 0.05,
		    'fallback' : '',
		    'monochrome' : true
		});
	});
});