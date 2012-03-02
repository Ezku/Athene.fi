SearchUnleashed = {
  module_list : function(ajax_url, nonce) {
    jQuery('.modules a').click(function() {
    	jQuery(this).parent().parent().load( jQuery(this).attr('href'), { _ajax_nonce: nonce} );				
    	return false;
    });
    
    jQuery('.modules input[type=checkbox]').click(function() {
    	var action = 'su_module_off';
    	var item   = jQuery(this);

      if (item.attr('checked'))
    		action = 'su_module_on';

      jQuery.ajax( {
    			type: 'POST',
    			url: ajax_url,
    			data: ({ action: action, module: jQuery(this).attr('name'), _ajax_nonce: nonce }),
    			success: function(data) { item.parent().parent().toggleClass('disabled'); },
    			error: this.error_message
    	});
    });
  },
  
  module_form : function(form_id, module_id, nonce) {
    jQuery(form_id + ' input[name=cancel]').click(function () {
  	  jQuery(module_id).load (jQuery(form_id).attr('action'), {
  	      action: 'su_module_load',
  	      id: jQuery(form_id + ' input[name=id]').val(),
  	      _ajax_nonce: nonce
  	  }, function() {
  	    SearchUnleashed.module_list(jQuery(form_id).attr('action'), nonce);
  	  });
  	  
  		return false;
  	});

  	jQuery(form_id).ajaxForm({
  		target: module_id,
  		success: function() {
  	    SearchUnleashed.module_list(jQuery(form_id).attr('action'), nonce);
  		}
  	});
	},
	
  error_message : function() {
    alert('There is an error');
  }
};


(function($){
 $.fn.Progressor = function(args){
	args = args || {};

	return this.each(function() {
		// Store value in a cookie
		function timer() {
		  if (running) {
  		  $.ajax({
  		    url: opts.url,
  		    cache: false,
  		    data: ({ action: 'su_index', offset: offset, limit: opts.limit, _ajax_nonce: opts.nonce }),
  		    type: 'POST',
  		    error: SearchUnleashed.error_message,
  		    success: function(data) {
  		      var parts   = data.split(' ');
  		      var left    = parseInt(parts.shift());
  		      var percent = parts.shift();
  		      var message = parts.join(' ')
  		      
		        $(wrapper).find('p').html(message);
		        $('#' + opts.inside).css('width', percent);

  		      if (left > 0) {
  		        // More to come
  		        offset += opts.limit;
          		setTimeout(timer, 0);
  		      }
  		      else {
  		        // Finished
		          $(opts.start).val(original);
        		  $(opts.loading).hide();
  		        running = false;
  		      }
  		    }
  		  });
  		}
		}
	
    // Load values from args and merge with defaults
    var opts = $.extend({
      cancel: 'Cancel',
      inside: 'inner',
      limit: 20,
      nonce: '',
      loading: '#loading'
    }, args);
		
		var offset  = 0;
		var running = false;
		var wrapper = this;
		var original = $(opts.start).val();
		
		$(opts.start).click(function() {
		  var button = this;

		  if (running) {
		    // Cancel
		    running = false;
		    $(button).val(original);
  		  $(opts.loading).hide();
		  }
		  else {
		    offset = 0;
  		  running = true;
		  
  		  // Hide the button
  		  $(button).val(opts.cancel);
  		  $(opts.loading).show();
        
  		  // Setup the progress bar
  		  $(wrapper).empty();
  		  $(wrapper).append('<div id="' + opts.inside + '"></div><p></p>');
  		  $(wrapper).fadeIn();
        $('#' + opts.inside).css('width', '0px');
  		
    		// Now kick-start a timer to perform the progressor
    		setTimeout(timer, 0);
    	}
    	
  		return false;
		});
	  });
  };
})(jQuery);

function highlight (index,item)
{
  jQuery('#color_' + index).css ('backgroundColor', '#' + item.value);
}