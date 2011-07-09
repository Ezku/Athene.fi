<html>
<head>
	<title>Ajax Post Selector</title>
</head>
<body>
	<!-- 
	This is loaded via a thickbox iFrame from the WP manager when a post-selection field is generated
	It is parsed by the PostSelector.php class
	-->

	<!--script type="text/javascript" src="../../../../wp-includes/js/jquery/jquery.js"></script-->

	<!-- script src="[+cctm_url+]/uploader/fileuploader.js" type="text/javascript"></script>
	<link href="[+cctm_url+]/uploader/fileuploader.css" rel="stylesheet" type="text/css" -->
<div>
	<style>
		#media-upload-header {
			display: none;
		}
	</style>
			
	<!-- Safari seems to need the CSS and JS inside the body when loaded via WP. Standalone, it works fine. -->
	<style>	
		[+media_selector_css+]
	</style>
	<script type="text/javascript">
	
		// toggle var to determine if searches sort A->Z or Z->A
		var search_term;
		var sort_dir = 0; // default
		var current_page = '[+page+]';
		var default_mime_type = '[+default_mime_type+]';
		var column = 'post_modified';
		
		/*------------------------------------------------------------------------------
		Adds Upload form
		------------------------------------------------------------------------------*/
		function add_upload_form()
		{
			jQuery.get("[+cctm_url+]/upload_form.php","", write_results_to_page);
		}
	
		/*------------------------------------------------------------------------------
		
		------------------------------------------------------------------------------*/
		function change_page(new_page)
		{
			//jQuery("#global_current_page").val(new_page);
			current_page = new_page;
			search_posts("[+default_mime_type+]");
		}
	
		/*------------------------------------------------------------------------------
		Clears the search form
		------------------------------------------------------------------------------*/
		function clear_search()
		{	
			search_term = ''; //jQuery("#media_search_term").val(''); 
			current_page = 0;
			search_posts("[+default_mime_type+]");
		}

		/*------------------------------------------------------------------------------
		Handle uploading the file.
		------------------------------------------------------------------------------*/
		function handle_upload()
		{
			var the_file = jQuery("#async-upload").val();
			jQuery.post("[+cctm_url+]/upload_form_handler.php",{"async-upload":the_file}, write_results_to_page);
		}
		
		/*------------------------------------------------------------------------------
		Main AJAX function to kick off the query.
		------------------------------------------------------------------------------*/
		function new_media()
		{
			// jQuery.get("media-upload.php", { "flash":"0","inline":"false"}, write_results_to_page);
			// jQuery.get("media-upload.php","", write_results_to_page);
			jQuery.get("[+cctm_url+]/upload.php","", write_results_to_page);
			
		}

		/*------------------------------------------------------------------------------
		When someone clicks the search button
		------------------------------------------------------------------------------*/
		function new_search(mime_type)
		{
			search_term = jQuery("#media_search_term").val(); //  global 
			current_page = 0; // zero it out, otherwise the offset gets wonky when you search from the last page
			search_posts(default_mime_type);
		}
		
		/*------------------------------------------------------------------------------
		Main AJAX function to kick off the query.  This is what runs when you click
		on the next/prev pages
		------------------------------------------------------------------------------*/
		function search_posts(mime_type)
		{
			// var search_term = jQuery("#media_search_term").val();
			// search_term = jQuery("#media_search_term").val(); //  global ,"c":column,"dir":sort_dir
			var yyyymm = jQuery("#m").val();
			jQuery.get("[+ajax_controller_url+]", { "mode":"query", "s":search_term,"fieldname":"[+fieldname+]","post_mime_type":default_mime_type,"m":yyyymm,"page":current_page,"post_type":"[+post_type+]","c":column,"dir":sort_dir }, write_results_to_page);
		}
	
		/*------------------------------------------------------------------------------
		Where the magic happens: this sends our selection back to WordPress
		@param	integer	post_id is the ID of the attachment that has been selected
		@param	string	thumbnail_html is the html that displays a thumbnail of the post_id referenced
		------------------------------------------------------------------------------*/
		function send_back_to_wp( post_id, thumbnail_html )
		{
			jQuery('#[+fieldname+]').val(post_id);
			jQuery('#[+fieldname+]_media').html(thumbnail_html);
			tb_remove();
			return false;
		}

		/*------------------------------------------------------------------------------
		Sorts the posts by the column specified.
		------------------------------------------------------------------------------*/
		function sort_posts(new_column)
		{
			if ( column == new_column )
			{	
				// toggle sort order
				if ( sort_dir == 1 )
				{
					sort_dir = 0;
				}
				else
				{
					sort_dir = 1;
				}
			}
			column = new_column;
			search_posts(default_mime_type);
		}
		
		/*------------------------------------------------------------------------------
		Show / Hide 
		------------------------------------------------------------------------------*/
		function toggle_image_detail(css_id)
		{
			jQuery('#'+css_id).slideToggle(400);
	    	return false;
		}


		/*------------------------------------------------------------------------------
		SYNOPSIS: 
			Write the incoming data to the page. 
		INPUT: 
			data = the html to write to the page
			status = an HTTP code to designate 200 OK or 404 Not Found
			xhr = object
		OUTPUT: 
			Writes HTML data to the 'ajax_search_results_go_here' id.
		------------------------------------------------------------------------------*/
		function write_results_to_page(data,status, xhr) 
		{
			if (status == "error") {
				var msg = "Sorry but there was an error: ";
		    	console.error(msg + xhr.status + " " + xhr.statusText);
			}
			else
			{
				jQuery('#ajax_search_results_go_here').html(data);
			}
		}		
		
	</script>

<div id="[+fieldname+]_post_selector_wrapper">
	<p id="media-search-term-box" class="search-box">
		<input type="text" id="media_search_term" name="s" value="" />
		<span class="button" onclick="javascript:new_search('[+default_mime_type+]');">[+search_label+]</span>
		<span class="button" onclick="javascript:clear_search();">[+clear_label+]</span>
	</p>
	
	<h3>Narrow Results</h3>
	<ul class="subsubsub">
		[+post_mime_type_options+]
	</ul>

	
	<div class="tablenav">			
		<div class="alignleft actions">
			<select name="m" id="m" onchange="javascript:search_posts('[+default_mime_type+]');">
				[+date_options+]
			</select>
			
			[+add_image_button+]
			
		</div>	
	</div>
</div>

<br class="clear" />
<div class="sort_controls"><strong>Sort by:</strong> 
	<span onclick="javascript:sort_posts('post_modified');">Date</span> 
	<span onclick="javascript:sort_posts('post_title');">Name</span>
</div>

<div id="ajax_search_results_go_here">[+default_results+]</div>

</div>

</body>
</html>