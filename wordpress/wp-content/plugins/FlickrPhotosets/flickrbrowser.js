var flickrbrowser = {
  photosetClicked: false,
  debug: false,
  getQueryString: function(method, params) {
    
    var paramsString = "&"
    jQuery.each(params, function(index, value) {
      paramsString += index + "=" + value + "&";
    });
    
    url = "http://api.flickr.com/services/rest/" 
  	+ "?method="+method
  	+ "&api_key="+flickrbrowser.api_key
  	//+ "&user_id="+flickrbrowser.user_id
  	+ "&format=json"
  	+ "&jsoncallback=?"
  	+ paramsString;
  	
  	return url;
  },
  log: function(msg) {
    if (flickrbrowser.debug && window.console && typeof console.log == "function") {
      console.log(msg);
    }
  },
  getPhotosets: function(attrs) {
    var params = {user_id: flickrbrowser.user_id};
    if (attrs && attrs.photosets) {
      params.page = 1;
      params.per_page = attrs.photosets;
    }
    
    var url = flickrbrowser.getQueryString("flickr.photosets.getList", params);
    
    flickrbrowser.showSpinner(true);
    jQuery.getJSON(url,function(data){
      flickrbrowser.log(data);
  	  flickrbrowser.showSpinner(false);
  	  jQuery("#flickrphotos").html("");
  	  if (data.stat === "fail") {
  	    jQuery("#flickrphotos").html("Error fetching photosets: "+data.message);
  	  } else {
  	  jQuery.each(data.photosets.photoset, function(i, val) {
  	      if (attrs && attrs.photos > i) {
  	        return;
  	      }
  	      var title = val.title._content;
  	      var timestampStr;
  	      var dateCreatedStr = flickrbrowser.formatDate(val.date_create);
  	      var dateUpdatedStr = flickrbrowser.formatDate(val.date_update);
  	      if (dateCreatedStr === dateUpdatedStr) {
  	        timestampStr = dateCreatedStr;
  	      } else {
  	        timestampStr = dateCreatedStr+" (päivitetty "+dateUpdatedStr+")";
  	      }
    	    jQuery("#flickrphotos").append("<div id=\"photoset"+val.id+"\" class=\"photoset\" data-photoset-id=\"" + val.id + "\">"
    	      + "<div class=\"photosettitle\"><a href=\"#\" style='display: block; position: relative;'><img class=\"primary\" src=\"\" alt=\"\" style='height: 75px; width: 75px' /><span style='margin: 0 5px; position: absolute; top: 50%; height: 2em; margin-top: -1em;'>" + title 
    	      + "<br/><i>"+val.photos+" kuvaa - "+timestampStr+"</i></span><br /></a></div>"
    	      + "<div class=\"photos hide\"></div>"
    	      + "</div>");
    	    jQuery.getJSON(flickrbrowser.getQueryString("flickr.photos.getInfo",{photo_id:val.primary}), function(data) {
    	      flickrbrowser.log(data);
    	      jQuery("#photoset"+val.id + " img.primary").attr('src', flickrbrowser.getPhotoURL(data.photo, "thumbnail"));
    	    });
    	    var img = this.primary;
    	  });
  	  }
  	  
  	  jQuery("#flickrphotos .photoset a").click(function(e) {
  	    jQuery(this).parents('.photoset').toggleClass('active');
  	    var el = jQuery(this).parents('.photoset').children(".photos");
  	    el.toggleClass("hide");
  	    if (!el.hasClass("hide")) {
  	      flickrbrowser.getPhotoset(jQuery(this).parents('.photoset').attr('data-photoset-id'), el);
  	    } else {
  	      flickrbrowser.setHash({}); // empty hash
  	    }
  	    e.preventDefault();
  	  });
  	  
  	  if (flickrbrowser.explodeHash().photoset) {
  	    flickrbrowser.photosetClicked = true;
  	    flickrbrowser.getPhotoset(flickrbrowser.explodeHash().photoset);
  	  }
	  });
  },
  getPhotoset: function(id, el) {
    flickrbrowser.setHash({photoset: id});
    var url = flickrbrowser.getQueryString("flickr.photosets.getPhotos", {photoset_id: id});
    flickrbrowser.showSpinner(true);
    jQuery.getJSON(url,function(data){
      flickrbrowser.showSpinner(false);
      var photosetString = "";
      jQuery.each(data.photoset.photo, function(index, photo) {
        var photoURLt = flickrbrowser.getPhotoURL(photo, "thumbnail");
        var photoURL = flickrbrowser.getPhotoURL(photo, "large");
        photosetString += "<div class=\"photo\"><a rel=\"photoset"+id+"\" title=\""+photo.title+"\" href=\""+photoURL+"\"><img src=\""+ photoURLt +"\" alt=\"\" /></a></div>";
      });
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
    var paramString = "";
    jQuery.each(params, function(index, value) {
      paramString += index+"="+value+"&";
    });
    document.location.hash = paramString;
  },
  explodeHash: function() {
    var hashParams = document.location.hash.substring(1).split("&");
    var processedParams = {};
    jQuery.each(hashParams, function(i, param) {
      var keyValue = param.split("=");
      if (keyValue.length == 2) {
        flickrbrowser.log('Adding key "' + keyValue[0] + '" with value "'+keyValue[1]+'"')
        processedParams[keyValue[0]] = keyValue[1];
      } else {
        flickrbrowser.log("Illegal hash param: "+param);
      }
    });
      
      return processedParams;
    },
    showSpinner: function(flag) {
      var el = jQuery('#spinner');
      if (flag) {
        el.removeClass('hide');
      } else {
        el.addClass('hide');
      }
    },
    formatDate : function(unixtime) {
      var date = new Date(unixtime*1000);
      return ""+date.getDate()+"."+(date.getMonth()+1)+"."+date.getFullYear()
    },
    showWidget: function() {
      var params = {user_id: flickrbrowser.user_id, per_page: 3, page: 1};
      var url = flickrbrowser.getQueryString("flickr.photosets.getList", params);
      jQuery.getJSON(url, function(data) {
        jQuery('#flickr-widget').html('');
        var output = '<ul>';
        jQuery.each(data.photosets.photoset, function(i, val) {
      	  var title = val.title._content;
      	  jQuery("#flickr-widget").append("<div id=\"photoset"+val.id+"\" class=\"photoset\" data-photoset-id=\"" + val.id + "\">"
      	    + "<div class=\"photosettitle\"><a href=\""+flickrbrowser.link_url+"#photoset="+val.id+"\" style='display: block; position: relative;'><img class=\"primary\" src=\"\" alt=\"\" style='height: 50px; width: 50px' /><span style='margin: 0 5px; position: absolute; top: 50%; height: 1em; margin-top: -0.5em;'>" + title + "</span></a></div>"
      	    + "<div class=\"photos hide\"></div>"
      	    + "</div>");
      	  jQuery.getJSON(flickrbrowser.getQueryString("flickr.photos.getInfo",{photo_id:val.primary}), function(data) {
      	    jQuery("#photoset"+val.id + " img.primary").attr('src', flickrbrowser.getPhotoURL(data.photo, "thumbnail"));
      	  });
      	});
      	jQuery("#flickr-widget").append('<p class="more"><a href="'+flickrbrowser.link_url+'">lisää...</a></p>')
      });
    }
  };