/*
 * Flickr photosets browser 
 * version 0.1
 * By Pyry Kröger (http://pkroger.org)
 * 
 */
var flickrphotosets = {
  debug: false,
  texts: {
    flickr_link: "This photoset in Flickr",
    photos: "photos",
    updated: "updated"
  },
  /**
   * API key for flickr photosets. Must be defined.
   */
  api_key: null,
  /**
   * User ID of the target Flickr user. Must be defined.
   */
  user_id: null,
  
  /**
   * Initializes the flickr photosets app. 
   */
  init: function(el, attrs) {
    this.checkRequiredAttrs(el, attrs);
    
    // add loading indicator
    jQuery(el).html('<div class="spinner"></div>');
    
    flickrphotosets.getPhotosets(el, attrs);
    
    // enable the hash change event for back button compatibility
    jQuery(window).hashchange( function(){
      var hashParams = flickrphotosets.explodeHash();
      if (flickrphotosets.photosetClicked) { // do not trigger event when showing the photoset manually
        flickrphotosets.photosetClicked = false;
      } else {
        var inactive = jQuery('.photoset').not('#photoset'+hashParams.photoset);
        inactive.removeClass('active');
  	    inactive.children(".photos").addClass('hide');
        if (hashParams.photoset) {
    	    flickrphotosets.getPhotoset(hashParams.photoset);
    	  }
      }

    });
  },
  checkRequiredAttrs: function(el, attrs) {
    if (!el) {
      throw "Target element for flickr photosets must be defined!";
    }
    if (!this.api_key && !attrs.api_key) {
      throw "API key must be defined!"
    }
    if (!this.user_id && !attrs.user_id) {
      throw "Target user ID must be defined!"
    }
  },
  /**
   * Returns the URL (with parameters, if any specified) for a specific flickr method
   */
  getQueryString: function(method, params) {
    
    var paramsString = "&"
    jQuery.each(params, function(index, value) {
      paramsString += index + "=" + value + "&";
    });
    
    url = "http://api.flickr.com/services/rest/" 
  	+ "?method="+method
  	+ "&api_key="+flickrphotosets.api_key
  	+ "&format=json"
  	+ "&jsoncallback=?"
  	+ paramsString;
  	
  	return url;
  },
  log: function() {
    if (flickrphotosets.debug && window.console && typeof console.log == "function") {
      console.log(Array.prototype.slice.call(arguments));
    }
  },
  getDate: function(seconds) {
    return new Date(parseInt(seconds)*1000);
  },
  getPhotosets: function(element, attrs) {
    var params = {user_id: flickrphotosets.user_id, page: 1};
    if (attrs) {
      for(var i in attrs) {
        params[i] = attrs[i];
      }
    }
    
    var url = flickrphotosets.getQueryString("flickr.photosets.getList", params);
    
    flickrphotosets.showSpinner(element, true);
    jQuery.getJSON(url,function(data){
      flickrphotosets.log(data);
  	  flickrphotosets.showSpinner(element, false);
  	  jQuery(element).html("");
  	  if (data.stat === "fail") {
  	    jQuery(element).html("Error fetching photosets: "+data.message);
  	  } else {
  	  jQuery.each(data.photosets.photoset, function(i, val) {
  	      if (attrs && attrs.photos > i) {
  	        return;
  	      }
  	      var title = val.title._content;
  	      var timestampStr;
  	      var dateCreatedStr = flickrphotosets.formatDate(val.date_create);
  	      var dateUpdatedStr = flickrphotosets.formatDate(val.date_update);
  	      if (dateCreatedStr === dateUpdatedStr) {
  	        timestampStr = dateCreatedStr;
  	      } else {
  	        timestampStr = dateCreatedStr+" (" + flickrphotosets.texts.updated + " "+dateUpdatedStr+")";
  	      }
    	    jQuery(element).append(
    	        "<div id=\"photoset"+val.id+"\" class=\"photoset\" data-photoset-id=\"" + val.id + "\">"
    	        + "<div class=\"photosettitle\">"
    	            + "<a href=\"#\" class='flickr-photoset'>"
    	                + "<img class=\"primary\" src=\"\" alt=\"\" style='height: 75px; width: 75px' />"
    	                + "<span class='flickr-photoset-info' style=''>"
    	                    + '<span class="flickr-photoset-title">'+ title + "</span><br/>"
    	                    + "<span class=\"flickr-photoset-details\">"
    	                      + "<i>"+val.photos+" "+flickrphotosets.texts.photos+" - "+timestampStr+"</i>"
    	                    + "</span>"
    	                + "</span><br />"
    	            + "</a>"
    	        + "</div>"
    	        + "<div class=\"spinner hide\">"
                + "</div>"
    	        + "<div class=\"photos hide\">"
    	        + "</div>"
    	      + "</div>");
    	    jQuery.getJSON(flickrphotosets.getQueryString("flickr.photos.getInfo",{photo_id:val.primary}), function(data) {
    	      flickrphotosets.log(data);
    	      jQuery("#photoset"+val.id + " img.primary").attr('src', flickrphotosets.getPhotoURL(data.photo, "thumbnail"));
    	    });
    	    var img = this.primary;
    	  });
  	  }
  	  
  	  jQuery(element).find(".photoset a").click(function(e) {
  	    jQuery(this).parents('.photoset').toggleClass('active');
  	    var el = jQuery(this).parents('.photoset').children(".photos");
  	    el.toggleClass("hide");
  	    if (!el.hasClass("hide")) {
  	      flickrphotosets.getPhotoset(jQuery(this).parents('.photoset').attr('data-photoset-id'), el, attrs);
  	    } else {
  	      flickrphotosets.setHash({}); // empty hash
  	    }
  	    e.preventDefault();
  	  });
  	  
  	  var hashParams = flickrphotosets.explodeHash();
  	  if (hashParams.photoset) {
  	    flickrphotosets.photosetClicked = true;
  	    flickrphotosets.getPhotoset(hashParams.photoset);
  	    flickrphotosets.scrollTo('#photoset'+hashParams.photoset);
  	  }
	  });
  },
  scrollTo: function(selector) {
      if(typeof jQuery.scrollTo == 'function') {
          jQuery.scrollTo(selector);
      } else {
          flickrphotosets.log('WARN: scrollTo function does not exist');
      }
  },
  getPhotoset: function(id, el, attrs) {
    flickrphotosets.setHash({photoset: id});
    
    var params = {photoset_id: id};
    if (attrs) {
      for(var i in attrs) {
        params[i] = attrs[i];
      }
    }
    
    var url = flickrphotosets.getQueryString("flickr.photosets.getPhotos", params);
    flickrphotosets.showSpinner('#photoset'+id, true);
    jQuery.getJSON(url,function(data){
      flickrphotosets.showSpinner('#photoset'+id, false);
      var photosetString = "";
      jQuery.each(data.photoset.photo, function(index, photo) {
        var photoURLt = flickrphotosets.getPhotoURL(photo, "thumbnail");
        var photoURL = flickrphotosets.getPhotoURL(photo, "large");
        photosetString += 
            '<div class="photo">'+
                '<a rel="photoset'+id+'" title="'+photo.title+'" href="'+photoURL+'"><img src="'+ photoURLt +'" alt="" /></a>'+
            '</div>';
      });
      photosetString += '<div style="clear: both;"></div>';
      photosetString += '<a class="flickr-link" href="http://www.flickr.com/photos/'+
        flickrphotosets.user_id+'/sets/'+id+'">'+flickrphotosets.texts.flickr_link+'</a>';
      photosetString += '<div style="clear: both;"></div>';
      if (!el) {
        el = jQuery('#photoset'+id + " .photos");
      } 
      el.html("");
      el.append(photosetString);
      
      el.find("div a").fancybox({
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'titlePosition' 	: 'over',
				'cyclic'          : true,
				'titleFormat'		: function(title, currentArray, currentIndex, currentOpts) {
					return '<span id="fancybox-title-over">Image ' + (currentIndex + 1) + ' / ' + currentArray.length + (title.length ? ' &nbsp; ' + title : '') + '</span>';
				}
			});
      el.removeClass("hide");
      el.parents('.photoset').addClass('active');
      
    });
  },
  getPhotoURL: function(opts, size) {
    var sizes = {large:"b", thumbnail:"s"};
    return "http://farm"+opts.farm+".static.flickr.com/"+opts.server+"/"+opts.id+"_"+opts.secret+"_"+sizes[size]+".jpg"
  },
  setHash: function(params) {
    var paramString = "!";
    jQuery.each(params, function(index, value) {
      paramString += index+"="+value+"&";
    });
    document.location.hash = paramString;
  },
  explodeHash: function() {
    var hashParams = document.location.hash.substring(2).split("&");
    var processedParams = {};
    jQuery.each(hashParams, function(i, param) {
      var keyValue = param.split("=");
      if (keyValue.length == 2) {
        flickrphotosets.log('Adding key "' + keyValue[0] + '" with value "'+keyValue[1]+'"')
        processedParams[keyValue[0]] = keyValue[1];
      } else {
        flickrphotosets.log("Illegal hash param: "+param);
      }
    });
      
      return processedParams;
    },
    showSpinner: function(section, flag) {
      var el = jQuery(section + ' .spinner');
      this.log(el);
      if (flag) {
        this.log("Showing spinner");
        el.removeClass('hide');
      } else {
        this.log("Hiding spinner");
        el.addClass('hide');
      }
    },
    formatDate : function(unixtime) {
      var date = new Date(unixtime*1000);
      return ""+date.getDate()+"."+(date.getMonth()+1)+"."+date.getFullYear()
    },
    
    initWidget: function(el, attrs) {
      this.checkRequiredAttrs(el, attrs);
      
      var params = {user_id: flickrphotosets.user_id, per_page: 3, page: 1};
      if (attrs) {
        for(var i in attrs) {
          params[i] = attrs[i];
        }
      }
      
      
      // add loading indicator
      jQuery(el).html('<div class="spinner"></div>');
      
      var url = flickrphotosets.getQueryString("flickr.photosets.getList", params);
      jQuery.getJSON(url, function(data) {
        jQuery(el).html('');
        jQuery.each(data.photosets.photoset, function(i, val) {
            flickrphotosets.log(val);
      	    var title = val.title._content;
      	    var date = flickrphotosets.getDate(val.date_update);
      	    jQuery(el).append(
      	      "<div id=\"photoset"+val.id+"\" class=\"photoset date-indexed\" data-photoset-id=\"" + val.id + "\">"
      	      + "<div class=\"photosettitle\">"
      	        + "<a href=\""+flickrphotosets.link_url+"#!photoset="+val.id+"\" style='min-height: 50px; display: block;'>"
      	            + "<img class=\"primary\" src=\"\" alt=\"\" style='height: 50px; width: 50px; float: left; margin-right: 5px;' />"
      	            + "<span style='margin: 0; height: 50px; display: table-cell; vertical-align: middle;'>" + title + "</span>"
      	        + "</a>"+
      	        "</div>"
      	    + "</div>");
      	  jQuery.getJSON(flickrphotosets.getQueryString("flickr.photos.getInfo",{photo_id:val.primary}), function(data) {
      	    jQuery("#photoset"+val.id + " img.primary").attr('src', flickrphotosets.getPhotoURL(data.photo, "thumbnail"));
      	  });
      	});
      	jQuery(el).append('<p class="more"><a href="'+flickrphotosets.link_url+'">lisää...</a></p>')
      });
    },
    photosetClicked: false
  };