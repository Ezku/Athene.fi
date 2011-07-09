<script>
	jQuery(document).ready(function(){
		toggle_image_detail();
		toggle_div('[+supports_page-attributes.id+]', 'extended_page_attributes', '[+supports_page-attributes.value+]');
	});
	
	jQuery(function() {
		jQuery( "#tabs" ).tabs();
	});
	
	function toggle_image_detail()
	{

		if( jQuery('#[+use_default_menu_icon.id+]:checked').val() == '1' )
		{
            jQuery('#menu_icon_container').hide("slide");
        } 
        else 
        {
            jQuery('#menu_icon_container').show("slide");

        }

	}

	function toggle_div(checkbox_id, css_id, checked_value)
	{

		if( jQuery('#'+checkbox_id+':checked').val() == checked_value )
		{
            jQuery('#'+css_id).show("slide");
        } 
        else 
        {
            jQuery('#'+css_id).hide("slide");

        }

	}
	
	function send_to_menu_icon(src)
	{
		jQuery('#menu_icon').val(src);
	}
</script>

<a href="http://code.google.com/p/wordpress-custom-content-type-manager/wiki/CreatePostType" title="Creating a Content Type" target="_blank">
		<img src="[+CCTM_URL+]/images/question-mark.gif" width="16" height="16" />
</a>

<div id="tabs">
	<ul>
		<li><a href="#basic-tab">Basic</a></li>
		<li><a href="#labels-tab">Labels</a></li>
		<li><a href="#fields-tab">Fields</a></li>
		<li><a href="#menu-tab">Menu</a></li>
		<li><a href="#urls-tab">URLs</a></li>
		<li><a href="#advanced-tab">Advanced</a></li>
	</ul>

	<div style="clear:both;"></div>	
	
	<div id="basic-tab">
		<!--!Post Type -->
		[+post_type+]
		
		<!-- menu_name_label -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_[+menu_name_label.id+]">
			<label for="[+menu_name_label.id+]" class="cctm_label cctm_text_label" id="cctm_label_[+menu_name_label.id+]">
				[+menu_name_label.label+] 
				<a rel="ungrouped" href="[+CCTM_URL+]/images/screenshots/menu-name.jpg" title="[+menu_name_label.label+]" class="thickbox">
					<img src="[+CCTM_URL+]/images/question-mark.gif" width="16" height="16" />
				</a>
			</label>
			<input type="text" name="[+menu_name_label.name+]" class="cctm_text" id="[+menu_name_label.id+]" value="[+menu_name_label.value+]"/>
			<span class="cctm_description">[+menu_name_label.description+]</span>
		</div>
		
		
		<!--!Description-->
		<!-- description -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_description">
					
			<label for="description" class="cctm_label cctm_textarea_label" id="cctm_label_[+description.id+]">[+description.label+]</label>
			<textarea name="[+description.name+]" class="cctm_textarea" id="[+description.id+]" rows="4" cols="60">[+description.value+]</textarea>
		</div>
		
		
		
		
		<!--!Show UI -->
		[+show_ui+]
		
		<!--!Public -->
		[+public+]
		
		
				<!--!Use Default Menu Icon -->
		<!-- use_default_menu_icon -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_use_default_menu_icon">
			<input type="checkbox" name="[+use_default_menu_icon.name+]" class="cctm_checkbox" id="use_default_menu_icon" value="[+use_default_menu_icon.checked_value+]" [+use_default_menu_icon.is_checked+] onclick="javascript:toggle_image_detail('menu_icon_container');"/> 
			<label for="use_default_menu_icon" class="cctm_label cctm_checkbox_label" id="cctm_label_use_default_menu_icon">[+use_default_menu_icon.label+]</label>
			<span class="cctm_description">[+use_default_menu_icon.description+]</span>
		</div>
		
		<div id="menu_icon_container" style="display: none;">
		
		<!--!Menu Icon -->
			<!-- menu_icon -->
			<div class="cctm_element_wrapper" id="custom_field_wrapper_menu_icon">		
				<label for="[+menu_icon.id+]" class="cctm_label cctm_text_label" id="cctm_label_menu_icon">[+menu_icon.label+]</label>
				<input type="text" name="[+menu_icon.name+]" class="cctm_text" id="[+menu_icon.id+]" value="[+menu_icon.value+]" size="100"/>
						<span class="cctm_description">[+menu_icon.description+]</span>
			</div>
		
			<div style="width:300px; margin-top:10px;">
			[+icons+]
				<br/>
				<p>Do you want more icons? Check the <a href="http://code.google.com/p/wordpress-custom-content-type-manager/wiki/CustomIcons">Wiki</a> for instructions.  You can help this project include a better default set -- see the related <a href="http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=30">issue</a> in the bug tracker. Thanks!</p>
			</div>
		</div>
		
		
	</div>

	<!-- ================================================================================================ -->	
	<!-- More advanced labels-->
	<!--!Labels -->
	<div id="labels-tab">	
	
		<!--singular_label -->
		[+singular_label+]
		
		<!--!Plural Label (Main Label)-->
		[+label+]	
		
		<!-- add_new_label -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_[+add_new_label.id+]_label">			
			<label for="[+add_new_label.id+]" class="cctm_label cctm_text_label" id="cctm_label_[+add_new_label.id+]">
				[+add_new_label.label+] 
				<a rel="label-screenshots" href="[+CCTM_URL+]/images/screenshots/add-new.jpg" title="[+add_new_label.label+]" class="thickbox">
					<img src="[+CCTM_URL+]/images/question-mark.gif" width="16" height="16" />
				</a>
			</label>
			<input type="text" name="[+add_new_label.name+]" class="cctm_text" id="[+add_new_label.id+]" value="[+add_new_label.value+]"/>
			<span class="cctm_description">[+add_new_label.description+]</span>
		</div>
		
		<!-- add_new_item_label -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_[+add_new_item_label.id+]_label">			
			<label for="[+add_new_item_label.id+]" class="cctm_label cctm_text_label" id="cctm_label_[+add_new_item_label.id+]">
				[+add_new_item_label.label+] 
				<a rel="label-screenshots" href="[+CCTM_URL+]/images/screenshots/add-new-item.jpg" title="[+add_new_item_label.label+]" class="thickbox">
					<img src="[+CCTM_URL+]/images/question-mark.gif" width="16" height="16" />
				</a>
			</label>
			<input type="text" name="[+add_new_item_label.name+]" class="cctm_text" id="[+add_new_item_label.id+]" value="[+add_new_item_label.value+]"/>
			<span class="cctm_description">[+add_new_item_label.description+]</span>
		</div>
		
		<!-- edit_item_label -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_[+edit_item_label.id+]_label">			
			<label for="[+edit_item_label.id+]" class="cctm_label cctm_text_label" id="cctm_label_[+edit_item_label.id+]">
				[+edit_item_label.label+] 
				<a rel="label-screenshots" href="[+CCTM_URL+]/images/screenshots/edit-item.jpg" title="[+edit_item_label.label+]" class="thickbox">
					<img src="[+CCTM_URL+]/images/question-mark.gif" width="16" height="16" />
				</a>
			</label>
			<input type="text" name="[+edit_item_label.name+]" class="cctm_text" id="[+edit_item_label.id+]" value="[+edit_item_label.value+]"/>
			<span class="cctm_description">[+edit_item_label.description+]</span>
		</div>	
		
		<!-- new_item_label -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_[+new_item_label.id+]_label">			
			<label for="[+new_item_label.id+]" class="cctm_label cctm_text_label" id="cctm_label_[+new_item_label.id+]">
				[+new_item_label.label+] 
				<a rel="label-screenshots" href="[+CCTM_URL+]/images/screenshots/new-item.jpg" title="[+new_item_label.label+]" class="thickbox">
					<img src="[+CCTM_URL+]/images/question-mark.gif" width="16" height="16" />
				</a>
			</label>
			<input type="text" name="[+new_item_label.name+]" class="cctm_text" id="[+new_item_label.id+]" value="[+new_item_label.value+]"/>
			<span class="cctm_description">[+new_item_label.description+]</span>
		</div>

		
		<!-- view_item_label -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_[+view_item_label.id+]">
			<label for="[+view_item_label.id+]" class="cctm_label cctm_text_label" id="cctm_label_[+view_item_label.id+]">
				[+view_item_label.label+] 
				<a rel="label-screenshots" href="[+CCTM_URL+]/images/screenshots/view-item.jpg" title="[+view_item_label.label+]" class="thickbox">
					<img src="[+CCTM_URL+]/images/question-mark.gif" width="16" height="16" />
				</a>
			</label>
			<input type="text" name="[+view_item_label.name+]" class="cctm_text" id="[+view_item_label.id+]" value="[+view_item_label.value+]"/>
			<span class="cctm_description">[+view_item_label.description+]</span>
		</div>

		
		<!-- search_items_label -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_[+search_items_label.id+]">
			<label for="[+search_items_label.id+]" class="cctm_label cctm_text_label" id="cctm_label_[+search_items_label.id+]">
				[+search_items_label.label+] 
				<a rel="label-screenshots" href="[+CCTM_URL+]/images/screenshots/search-items.jpg" title="[+search_items_label.label+]" class="thickbox">
					<img src="[+CCTM_URL+]/images/question-mark.gif" width="16" height="16" />
				</a>
			</label>
			<input type="text" name="[+search_items_label.name+]" class="cctm_text" id="[+search_items_label.id+]" value="[+search_items_label.value+]"/>
			<span class="cctm_description">[+search_items_label.description+]</span>
		</div>
		
		<!-- not_found_label -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_[+not_found_label.id+]">
			<label for="[+not_found_label.id+]" class="cctm_label cctm_text_label" id="cctm_label_[+not_found_label.id+]">
				[+not_found_label.label+] 
				<a rel="label-screenshots" href="[+CCTM_URL+]/images/screenshots/not-found.jpg" title="[+not_found_label.label+]" class="thickbox">
					<img src="[+CCTM_URL+]/images/question-mark.gif" width="16" height="16" />
				</a>
			</label>
			<input type="text" name="[+not_found_label.name+]" class="cctm_text" id="[+not_found_label.id+]" value="[+not_found_label.value+]"/>
			<span class="cctm_description">[+not_found_label.description+]</span>
		</div>

		
		<!-- not_found_in_trash_label -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_[+not_found_in_trash_label.id+]">
			<label for="[+not_found_in_trash_label.id+]" class="cctm_label cctm_text_label" id="cctm_label_[+not_found_in_trash_label.id+]">
				[+not_found_in_trash_label.label+] 
				<a rel="label-screenshots" href="[+CCTM_URL+]/images/screenshots/not-found-in-trash.jpg" title="[+not_found_in_trash_label.label+]" class="thickbox">
					<img src="[+CCTM_URL+]/images/question-mark.gif" width="16" height="16" />
				</a>
			</label>
			<input type="text" name="[+not_found_in_trash_label.name+]" class="cctm_text" id="[+not_found_in_trash_label.id+]" value="[+not_found_in_trash_label.value+]"/>
			<span class="cctm_description">[+not_found_in_trash_label.description+]</span>
		</div>

		
		<!-- parent_item_colon_label -->		
		[+parent_item_colon_label+]
		
	</div>
	
	<!-- ================================================================================================ -->	
	<div id="fields-tab">
		<p>Your post type must have <em>at least</em> the title or content boxes checked; otherwise WordPress will
		revert to the default behavior and include the title and the content fields.</p>
		
		<!--!Supports -->
		
		[+supports_title+]
			
		[+supports_editor+]
		
		[+supports_author+]
					
		[+supports_excerpt+]
		
		[+supports_custom-fields+]
		
		<!-- supports_page-attributes -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_[+supports_page-attributes.id+]">

			<input type="checkbox" 
				name="[+supports_page-attributes.name+]" 
				class="cctm_checkbox" 
				id="[+supports_page-attributes.id+]" 
				value="[+supports_page-attributes.checked_value+]" 
				onclick="javascript:toggle_div('[+supports_page-attributes.id+]', 'extended_page_attributes', '[+supports_page-attributes.value+]');" [+supports_page-attributes.is_checked+] />
			<label for="[+supports_page-attributes.id+]" class="cctm_label cctm_checkbox_label" id="cctm_label_[+supports_page-attributes.id+]">
				[+supports_page-attributes.label+]
			</label>
			<span class="cctm_description">[+supports_page-attributes.description+]</span>
		</div>
		
		<div id="extended_page_attributes" style="width:500px; padding-left:50px">
			<!-- supports_thumbnail -->
			[+supports_thumbnail+]
			
			<!-- hierarchical -->
			[+hierarchical+]
		</div>

	</div>
	<!-- ================================================================================================ -->
	<div id="menu-tab">
	
		<!--!Menu Position-->
		[+menu_position+]

	</div>

	<!-- ================================================================================================ -->
	
	<div id="urls-tab">
		<!--!Rewrite with Front -->
		[+rewrite_with_front+]
		
		[+rewrite+]
		
		<!--!Rewrite Slug -->
		[+rewrite_slug+]
		
		<!--!Permalink Action -->
		[+permalink_action+]
		
		<!--!Query Var -->
		[+query_var+]
	</div>
	
	<!-- ================================================================================================ -->
	<div id="advanced-tab">

		<!-- Capability Type -->
		[+capability_type+]
		
		<!--! Show in Nav Menus -->			
		[+show_in_nav_menus+]
		
		<!--!Can Export -->
		[+can_export+]
		
	
		[+supports_trackbacks+]
		
		[+supports_comments+]
		
		[+supports_revisions+]
	
		<hr />
		
		<h3>Taxonomies</h3>
		
		<p>Taxonomies offer ways to classify data as an aid to searching.</p>
		
		[+taxonomy_categories+]
		
		[+taxonomy_tags+]
		
		<p>Currently, this plugin only allows you to use default taxonomies with your content types. We recommend using momo360modena's <a href="http://wordpress.org/extend/plugins/simple-taxonomy/">Simple Taxonomy</a> plugin to create custom taxonomies.</p>
		
	</div>
</div>


