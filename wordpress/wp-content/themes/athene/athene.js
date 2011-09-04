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
	noisy('header#branding');
	noisy('header#subnavi-small');
	noisy('#main');
	noisy('body');
});