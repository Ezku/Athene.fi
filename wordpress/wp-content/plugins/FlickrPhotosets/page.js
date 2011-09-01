jQuery(function() {
  flickrbrowser.getPhotosets();
  
  jQuery(window).hashchange( function(){
    var hashParams = flickrbrowser.explodeHash();
    if (flickrbrowser.photosetClicked) { // do not trigger event when showing the photoset manually
      flickrbrowser.photosetClicked = false;
    } else {
      var inactive = jQuery('.photoset').not('#photoset'+hashParams.photoset);
      inactive.removeClass('active');
	    inactive.children(".photos").addClass('hide');
      if (hashParams.photoset) {
  	    flickrbrowser.getPhotoset(hashParams.photoset);
  	  }
    }
	  
  })
});