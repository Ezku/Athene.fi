<?php
/*------------------------------------------------------------------------------
This is the massive page that is used to create and edit post_type definitions.

~INPUT: This page should be included with two variables in scope:
	$action	string	can be set to 'create' if this form is to be used to create a def.
	$post_type	string	the name of the post_type
	$def	mixed	The definition of the post_type which contains all the 
					information for the post_type in question.  The format 
					of this variable should be exactly the format accepted 
					by the register_post_type() function (yes, the input to 
					that function is unwieldy, but translating data structures 
					back and forth is probably worse).

Note that the $def array contains some extra keys for controlling UX and validation:
use_default_menu_icon	-- checkbox for controlling
permalink_action

I'm using some *probably* unnecessary instances of htmlspecialchars(), but I 
just want to make sure that the form is presented uncorrupted.
------------------------------------------------------------------------------*/
if ( !isset($cancel_target_url) ) {
	$cancel_target_url = '?page='.self::admin_menu_slug;
}

?>

<div class="wrap">

	<h2>
	<a href="?page=<?php print self::admin_menu_slug;?>" title="<?php _e('Back'); ?>"><img src="<?php print CCTM_URL; ?>/images/cctm-logo.jpg" alt="summarize-posts-logo" width="88" height="55" /></a> 
	<?php print $page_header ?> <a class="button" href="?page=<?php print self::admin_menu_slug;?>"><?php _e('Cancel'); ?></a> </h2>
	
	<?php print $msg; ?>

	<form id="custom_post_type_manager_basic_form" method="post">
	
	
	

<script>
	/* Hide some of the divs by default */
	jQuery(document).ready(function(){
		toggle_image_detail();
		toggle_div('supports_page-attributes', 'extended_page_attributes', 'page-attributes');
		toggle_div('cctm_hierarchical_custom', 'custom_field_wrapper_custom_hierarchy', '1');
	});
	
	/* Drives the tab layout for this page. */
	jQuery(function() {
		jQuery( "#tabs" ).tabs();
	});
	
	/* Used to show additional menu icons if the "use default" is deselected. */
	function toggle_image_detail()
	{

		if( jQuery('#use_default_menu_icon:checked').val() == '1' )
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
	
	/* Used to send a full img path to the id="menu_icon" field */
	function send_to_menu_icon(src)
	{
		jQuery('#menu_icon').val(src);
	}
</script>

<a href="http://code.google.com/p/wordpress-custom-content-type-manager/wiki/CreatePostType" title="Creating a Content Type" target="_blank">
		<img src="<?php print CCTM_URL; ?>/images/question-mark.gif" width="16" height="16" />
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
		<div class="cctm_element_wrapper" id="custom_field_wrapper_post_type">
					
			
			<?php if ( isset($action) && $action == 'create' ): ?>
				<label for="post_type" class="cctm_label cctm_text_label" id="cctm_label_post_type">
				post_type* </label>
				<input type="text" name="post_type" class="cctm_text" id="post_type" value="<?php print htmlspecialchars($post_type); ?>"/>
			<?php else: ?>
				<p><strong>post_type:</strong> <?php print $post_type; ?></p>
				<input type="hidden" name="post_type" class="cctm_readonly" id="post_type" value="<?php print $post_type; ?>"/>			
			<?php endif; ?>
			<span class="cctm_description">This name may show up in your URLs, e.g. ?<?php print $post_type; ?>=my-<?php print $post_type; ?>. This will also make a new theme file available, starting with prefix named "single-", e.g. <code>single-<?php print htmlspecialchars($post_type); ?>.php</code>.</span>
		</div>
		
		<!-- menu_name_label -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_menu_name_label">
			<label for="menu_name_label" class="cctm_label cctm_text_label" id="cctm_label_menu_name_label">
				Menu Name* 
				<a rel="ungrouped" href="<?php print CCTM_URL; ?>/images/screenshots/menu-name.jpg" title="Menu Name*" class="thickbox">
					<img src="<?php print CCTM_URL; ?>/images/question-mark.gif" width="16" height="16" />
				</a>
			</label>

			<input type="text" name="labels[menu_name]" class="cctm_text" id="menu_name_label" value="<?php print htmlspecialchars($def['labels']['menu_name']);?>"/>
			<span class="cctm_description">The menu name text. This string is the name to give menu items. Defaults to value of name</span>
		</div>
		
		
		<!--!Description-->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_description">
					
			<label for="description" class="cctm_label cctm_textarea_label" id="cctm_label_description">Description</label>
			<textarea name="description" class="cctm_textarea" id="description" rows="4" cols="60"><?php print htmlentities($def['description']); ?></textarea>
		</div>
		
		<!--!Show UI -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_show_ui">
			<input type="checkbox" name="show_ui" class="cctm_checkbox" id="show_ui" value="1" <?php print CCTM::is_checked($def['show_ui']); ?>/> 
			<label for="show_ui" class="cctm_label cctm_checkbox_label" id="cctm_label_show_ui">Show Admin User Interface</label>
			<span class="cctm_description">Should this post type be visible on the back-end?</span>
		</div>
		
		<!--!Public -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_public">		
			<input type="checkbox" name="public" class="cctm_checkbox" id="public" value="1" <?php print CCTM::is_checked($def['public']); ?>/> 
			<label for="public" class="cctm_label cctm_checkbox_label" id="cctm_label_public">Public</label>
			<span class="cctm_description">Should these posts be visible on the front-end?</span>
		</div>
		
		<!--!Use Default Menu Icon -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_use_default_menu_icon">
			<input type="checkbox" name="use_default_menu_icon" class="cctm_checkbox" id="use_default_menu_icon" value="1"  onclick="javascript:toggle_image_detail('menu_icon_container');" <?php print CCTM::is_checked($def['use_default_menu_icon']); ?>/> 
			<label for="use_default_menu_icon" class="cctm_label cctm_checkbox_label" id="cctm_label_use_default_menu_icon">Use Default Menu Icon</label>
			<span class="cctm_description">If checked, your post type will use the posts icon</span>
		</div>
		
		<div id="menu_icon_container" style="display: none;">		
			<!--!Menu Icon -->
			<div class="cctm_element_wrapper" id="custom_field_wrapper_menu_icon">		
				<label for="menu_icon" class="cctm_label cctm_text_label" id="cctm_label_menu_icon">Menu Icon</label>
				<input type="text" name="menu_icon" class="cctm_text" id="menu_icon" value="<?php print htmlspecialchars($def['menu_icon']); ?>" size="80"/>
						<span class="cctm_description">Menu icon URL.</span>
			</div>
		
			<div style="width:300px; margin-top:10px;">
				<?php print CCTM::_get_post_type_icons(); ?>
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
		<div class="cctm_element_wrapper" id="custom_field_wrapper_singular_label">			
			<label for="labels[singular_name]" class="cctm_label cctm_text_label" id="cctm_label_labels[singular_name]">Singular</label>
			<input type="text" name="labels[singular_name]" class="cctm_text" id="labels[singular_name]" value="<?php print htmlspecialchars($def['labels']['singular_name']); ?>"/>
					<span class="cctm_description">Human readable single instance of this content type, e.g. "Post"</span>
		</div>
		
		<!--!Plural Label (Main Label)-->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_label">					
			<label for="label" class="cctm_label cctm_text_label" id="cctm_label_label">Main Menu Label (Plural)</label>
			<input type="text" name="label" class="cctm_text" id="label" value="<?php print htmlspecialchars($def['label']); ?>"/>
					<span class="cctm_description">Plural name used in the admin menu, e.g. "Posts"</span>
		</div>

		<!-- add_new_label -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_add_new_label_label">			
			<label for="add_new_label" class="cctm_label cctm_text_label" id="cctm_label_add_new_label">
				Add New 
				<a rel="label-screenshots" href="<?php print CCTM_URL; ?>/images/screenshots/add-new.jpg" title="Add New" class="thickbox">
					<img src="<?php print CCTM_URL; ?>/images/question-mark.gif" width="16" height="16" />
				</a>
			</label>
			<input type="text" name="labels[add_new]" class="cctm_text" id="add_new_label" value="<?php print htmlspecialchars($def['labels']['add_new']); ?>"/>
			<span class="cctm_description">The add new text. The default is Add New for both hierarchical and non-hierarchical types.</span>
		</div>
		
		<!-- add_new_item_label -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_add_new_item_label_label">			
			<label for="add_new_item_label" class="cctm_label cctm_text_label" id="cctm_label_add_new_item_label">
				Add New Item 
				<a rel="label-screenshots" href="<?php print CCTM_URL; ?>/images/screenshots/add-new-item.jpg" title="Add New Item" class="thickbox">
					<img src="<?php print CCTM_URL; ?>/images/question-mark.gif" width="16" height="16" />
				</a>
			</label>
			<input type="text" name="labels[add_new_item]" class="cctm_text" id="add_new_item_label" value="<?php print htmlspecialchars($def['labels']['add_new_item']); ?>"/>
			<span class="cctm_description">The add new item text. Default is Add New Post/Add New Page</span>
		</div>
		
		<!-- edit_item_label -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_edit_item_label_label">			
			<label for="edit_item_label" class="cctm_label cctm_text_label" id="cctm_label_edit_item_label">
				Edit Item 
				<a rel="label-screenshots" href="<?php print CCTM_URL; ?>/images/screenshots/edit-item.jpg" title="Edit Item" class="thickbox">
					<img src="<?php print CCTM_URL; ?>/images/question-mark.gif" width="16" height="16" />
				</a>
			</label>
			<input type="text" name="labels[edit_item]" class="cctm_text" id="edit_item_label" value="<?php print htmlspecialchars($def['labels']['edit_item']); ?>"/>
			<span class="cctm_description">The edit item text. Default is Edit Post/Edit Page</span>
		</div>	
		
		<!-- new_item_label -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_new_item_label_label">			
			<label for="new_item_label" class="cctm_label cctm_text_label" id="cctm_label_new_item_label">
				New Item 
				<a rel="label-screenshots" href="<?php print CCTM_URL; ?>/images/screenshots/new-item.jpg" title="New Item" class="thickbox">
					<img src="<?php print CCTM_URL; ?>/images/question-mark.gif" width="16" height="16" />
				</a>
			</label>
			<input type="text" name="labels[new_item]" class="cctm_text" id="new_item_label" value="<?php print htmlspecialchars($def['labels']['new_item']); ?>"/>
			<span class="cctm_description">The new item text. Default is New Post/New Page</span>

		</div>

		
		<!-- view_item_label -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_view_item_label">
			<label for="view_item_label" class="cctm_label cctm_text_label" id="cctm_label_view_item_label">
				View Item 
				<a rel="label-screenshots" href="<?php print CCTM_URL; ?>/images/screenshots/view-item.jpg" title="View Item" class="thickbox">
					<img src="<?php print CCTM_URL; ?>/images/question-mark.gif" width="16" height="16" />
				</a>
			</label>
			<input type="text" name="labels[view_item]" class="cctm_text" id="view_item_label" value="<?php print htmlspecialchars($def['labels']['edit_item']); ?>"/>
			<span class="cctm_description">The view item text. Default is View Post/View Page</span>
		</div>

		
		<!-- search_items_label -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_search_items_label">
			<label for="search_items_label" class="cctm_label cctm_text_label" id="cctm_label_search_items_label">
				Search Items 
				<a rel="label-screenshots" href="<?php print CCTM_URL; ?>/images/screenshots/search-items.jpg" title="Search Items" class="thickbox">

					<img src="<?php print CCTM_URL; ?>/images/question-mark.gif" width="16" height="16" />
				</a>
			</label>
			<input type="text" name="labels[search_items]" class="cctm_text" id="search_items_label" value="<?php print htmlspecialchars($def['labels']['search_items']); ?>"/>
			<span class="cctm_description">The search items text. Default is Search Posts/Search Pages</span>
		</div>
		
		<!-- not_found_label -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_not_found_label">
			<label for="not_found_label" class="cctm_label cctm_text_label" id="cctm_label_not_found_label">
				Not Found 
				<a rel="label-screenshots" href="<?php print CCTM_URL; ?>/images/screenshots/not-found.jpg" title="Not Found" class="thickbox">
					<img src="<?php print CCTM_URL; ?>/images/question-mark.gif" width="16" height="16" />
				</a>
			</label>
			<input type="text" name="labels[not_found]" class="cctm_text" id="not_found_label" value="<?php print htmlspecialchars($def['labels']['not_found']); ?>"/>
			<span class="cctm_description">The not found text. Default is No posts found/No pages found</span>
		</div>

		
		<!-- not_found_in_trash_label -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_not_found_in_trash_label">
			<label for="not_found_in_trash_label" class="cctm_label cctm_text_label" id="cctm_label_not_found_in_trash_label">
				Not Found in Trash 
				<a rel="label-screenshots" href="<?php print CCTM_URL; ?>/images/screenshots/not-found-in-trash.jpg" title="Not Found in Trash" class="thickbox">
					<img src="<?php print CCTM_URL; ?>/images/question-mark.gif" width="16" height="16" />
				</a>
			</label>
			<input type="text" name="labels[not_found_in_trash]" class="cctm_text" id="not_found_in_trash_label" value="<?php print htmlspecialchars($def['labels']['not_found_in_trash']); ?>"/>
			<span class="cctm_description">The not found in trash text. Default is No posts found in Trash/No pages found in Trash</span>
		</div>

		
		<!-- parent_item_colon_label -->		
		<div class="cctm_element_wrapper" id="custom_field_wrapper_parent_item_colon_label">			
			<label for="labels[parent_item_colon]" class="cctm_label cctm_text_label" id="cctm_label_labels[parent_item_colon]">Parent Item Colon</label>
			<input type="text" name="labels[parent_item_colon]" class="cctm_text" id="labels[parent_item_colon]" value="<?php print htmlspecialchars($def['labels']['parent_item_colon']); ?>"/>
					<span class="cctm_description">The parent text (used only on hierarchical types). Default is <em>Parent Page</em></span>
		</div>
		
	</div>
	
	<!-- ================================================================================================ -->	
	<div id="fields-tab">
		<p>Your post type must have either the title or content boxes checked; otherwise WordPress will revert to the default behavior and include the title and the content fields.</p>
		
		<!--!Supports -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_supports_title">			
			<input type="checkbox" name="supports[]" class="cctm_checkbox" id="supports_title" value="title" <?php print CCTM::is_checked($def['supports'], 'title'); ?> /> 
			<label for="supports_title" class="cctm_label cctm_checkbox_label" id="cctm_label_supports_title_label">Title</label>
			<span class="cctm_description">Post Title</span>
		</div>
			
		
		<div class="cctm_element_wrapper" id="custom_field_wrapper_supports_editor">		
			<input type="checkbox" name="supports[]" class="cctm_checkbox" id="supports_editor" value="editor" <?php print CCTM::is_checked($def['supports'], 'editor'); ?> /> 
			<label for="supports_editor" class="cctm_label cctm_checkbox_label" id="cctm_label_supports_content_label">Content</label>
					<span class="cctm_description">Main content block.</span>
		</div>
		
		<div class="cctm_element_wrapper" id="custom_field_wrapper_supports_author">			
			<input type="checkbox" name="supports[]" class="cctm_checkbox" id="supports_author" value="author"  /<?php print CCTM::is_checked($def['supports'], 'author'); ?> > 
			<label for="supports_author" class="cctm_label cctm_checkbox_label" id="cctm_label_supports_author_label">Author</label>
			<span class="cctm_description">Track the author.</span>
		</div>
					
		<div class="cctm_element_wrapper" id="custom_field_wrapper_supports_excerpt">			
			<input type="checkbox" name="supports[]" class="cctm_checkbox" id="supports_excerpt" value="excerpt"  <?php print CCTM::is_checked($def['supports'], 'excerpt'); ?>/> 
			<label for="supports_excerpt" class="cctm_label cctm_checkbox_label" id="cctm_label_supports_excerpt_label">Excerpt</label>
			<span class="cctm_description">Small summary field.</span>
		</div>
		
		
		<div class="cctm_element_wrapper" id="custom_field_wrapper_supports_custom-fields">			
			<input type="checkbox" name="supports[]" class="cctm_checkbox" id="supports_custom-fields" value="custom-fields" <?php print CCTM::is_checked($def['supports'], 'custom-fields'); ?> /> 
			<label for="supports_custom-fields" class="cctm_label cctm_checkbox_label" id="cctm_label_supports_custom_fields_label">Supports Custom Fields</label>
			<span class="cctm_description">Currently, this functionality is overridden by any custom fields you have defined for this content type.</span>
		</div>
		
		<div class="cctm_element_wrapper" id="custom_field_wrapper_supports_post-formats">			
			<input type="checkbox" name="supports[]" class="cctm_checkbox" id="supports_post-formats" value="post-formats" <?php print CCTM::is_checked($def['supports'], 'post-formats'); ?> /> 
			<label for="supports_post-formats" class="cctm_label cctm_checkbox_label" id="cctm_label_supports_post_formats_label">Post Formats</label>
			<span class="cctm_description">A Post Format is a piece of meta information that can be used by a theme to customize its presentation of a post.</span>
		</div>
		
		<!-- supports_page-attributes -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_supports_page-attributes">
			<input type="checkbox" 
				name="supports[]" 
				class="cctm_checkbox" 
				id="supports_page-attributes" 
				value="page-attributes" 
				onclick="javascript:toggle_div('supports_page-attributes', 'extended_page_attributes', 'page-attributes');" <?php print CCTM::is_checked($def['supports'], 'page-attributes'); ?> />
			<label for="supports_page-attributes" class="cctm_label cctm_checkbox_label" id="cctm_label_supports_page-attributes">
				Page Attributes
			</label>
			<span class="cctm_description">This opens up a meta box for menu position and other page attributes.</span>
		</div>
		
		<div id="extended_page_attributes" style="width:500px; padding-left:50px">
		
			<!-- supports_thumbnail -->
			<div class="cctm_element_wrapper" id="custom_field_wrapper_supports_thumbnail">
				<input type="checkbox" name="supports[]" class="cctm_checkbox" id="supports_thumbnail" value="thumbnail" <?php print CCTM::is_checked($def['supports'], 'thumbnail'); ?> /> 
				<label for="supports_thumbnail" class="cctm_label cctm_checkbox_label" id="cctm_label_supports_thumbnail_label">Thumbnail</label>
				<span class="cctm_description">Featured image. The active theme must also support post-thumbnails. Include the following line in your theme's functions.php file: <br/><code>add_theme_support( 'post-thumbnails', array( '<?php print $post_type; ?>' ) );</code></span>
			</div>
			
			<!-- hierarchical -->
			<div class="cctm_element_wrapper" id="custom_field_wrapper_hierarchical">
				<input type="checkbox" name="hierarchical" class="cctm_checkbox" id="hierarchical" value="1" <?php print CCTM::is_checked($def['hierarchical']); ?>/> 
				<label for="hierarchical" class="cctm_label cctm_checkbox_label" id="cctm_label_hierarchical">Hierarchical</label>
				<span class="cctm_description">Allows parent to be specified.</span>
			</div>

			<div class="cctm_element_wrapper" id="custom_field_wrapper_hierarchical">
				<input type="checkbox" name="cctm_hierarchical_custom" class="cctm_checkbox" id="cctm_hierarchical_custom" value="1" <?php print CCTM::is_checked($def['cctm_hierarchical_custom']); ?> 
					onclick="javascript:toggle_div('cctm_hierarchical_custom', 'custom_field_wrapper_custom_hierarchy', '1');"/> 
				<label for="cctm_hierarchical_custom" class="cctm_label cctm_checkbox_label" id="cctm_label_hierarchical">Use Custom Hierarchy</label>
				<span class="cctm_description">Allows custom hierarchies to be specified.</span>

				
			<!-- Working : Custom hierarchy-->
				<div id="custom_field_wrapper_custom_hierarchy" style="border: 1px solid black; background-color:#C0C0C0; padding: 10px;">
					<h3>Custom Hierarchies</h3>
					<p>Warning: this feature is experimental. See <a href="http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=9" target="_blank">Issue 9</a> in the bugtracker.</p>
				
					<div class="cctm_element_wrapper" id="custom_field_wrapper_include_drafts">
						<input type="checkbox" name="cctm_hierarchical_includes_drafts" class="cctm_checkbox" id="cctm_hierarchical_includes_drafts" value="1" <?php print CCTM::is_checked($def['cctm_hierarchical_includes_drafts'], '1'); ?> /> 
						<label for="cctm_hierarchical_includes_drafts" class="cctm_label cctm_checkbox_label" id="cctm_label_cctm_hierarchical_includes_drafts">Include Drafts?</label>
						<span class="cctm_description">By default, WordPress only allows you to use published pages in your hierarchy. Select this option to override that behavior.</span>
					</div>

					<h3>Parent Post Types</h3>
					<span class="cctm_description">By default, WordPress only allows you to use posts of the same post-type in your hierarchy. Select which post types should be available as parents.</span>
<?php
				// checkbox_id, css_id, checked_value
				/* Handle custom hierarchical stuff */
				$i = 0;
				$args = array('public' => true );
				$post_types = get_post_types($args);
				//print_r($post_types); exit;
				foreach ( $post_types as $pt => $v ) {

					$is_checked = '';
					if ( is_array($def['cctm_hierarchical_post_types']) && in_array( $pt, $def['cctm_hierarchical_post_types']) ) {
						$is_checked = 'checked="checked"';
					}
					//  <input type="checkbox" name="vehicle" value="Car" checked="checked" />
					print '<span style="margin-left:20px;"><input type="checkbox" name="cctm_hierarchical_post_types[]" class="cctm_multiselect" id="cctm_hierarchical_post_types'.$i.'" value="'.$pt.'" '.$is_checked.'> <label class="cctm_muticheckbox" for="cctm_hierarchical_post_types'.$i.'">'.htmlspecialchars($pt).'</label></span><br/>';
					$i = $i + 1;					
				}
?>
				</div><!-- end custom hierarchical options -->
				
			</div>
			
			
			
		</div>
		
	</div>
	
	<!-- ================================================================================================ -->
	<div id="menu-tab">

		<!--!Menu Position-->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_menu_position">
			<label for="menu_position" class="cctm_label cctm_text_label" id="cctm_label_menu_position">Menu Position</label>
			<input type="text" name="menu_position" class="cctm_text" id="menu_position" value="<?php print htmlspecialchars($def['menu_position']); ?>"/>
			<span class="cctm_description">This setting determines where this post type should appear in the left-hand admin menu. Default: null (below Comments). E.g. "21" would cause this content type to display below Pages and above Comments. 
				<ul style="margin-left:40px;">
					<li><strong>5</strong> - below Posts</li>
					<li><strong>10</strong> - below Media</li>
					<li><strong>15</strong> - below Links</li>
					<li><strong>20</strong> - below Pages</li>
					<li><strong>25</strong> - below Comments</li>
					<li><strong>60</strong> - below first separator</li>
					<li><strong>65</strong> - below Plugins</li>
					<li><strong>70</strong> - below Users</li>
					<li><strong>75</strong> - below Tools</li>
					<li><strong>80</strong> - below Settings</li>
					<li><strong>100</strong> - below second separator</li>
				</ul>
			</span>
		</div>

	</div>

	<!-- ================================================================================================ -->
	<div id="urls-tab">
	
		<!--!Rewrite with Front -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_rewrite_with_front">			
			<input type="checkbox" name="rewrite_with_front" class="cctm_checkbox" id="rewrite_with_front" value="1"  <?php print CCTM::is_checked($def['rewrite_with_front']); ?>/> 
			<label for="rewrite_with_front" class="cctm_label cctm_checkbox_label" id="cctm_label_rewrite_with_front">Rewrite with Permalink Front</label>
			<span class="cctm_description">Allow permalinks to be prepended with front base - defaults to checked</span>
		</div>
		
		
		<div class="cctm_element_wrapper" id="custom_field_wrapper_rewrite">			
			<label for="permalink_action" class="cctm_label" id="cctm_label_permalink_action">Permalink Action</label>
			<select name="permalink_action" class="cctm_dropdown cctm_dropdown_label" id="permalink_action">
				<option value="Off" <?php print CCTM::is_selected('Off', $def['permalink_action']); ?>>Off</option>
				<option value="/%postname%/" <?php print CCTM::is_selected('/%postname%/', $def['permalink_action']); ?>>/%postname%/</option>
				<option value="Custom" <?php print CCTM::is_selected('Custom', $def['permalink_action']); ?>>Custom</option>
			</select>
				
			<span class="cctm_description">Use permalink rewrites for this post_type? Default: Off
				<ul style="margin-left:20px;">
					<li><strong>Off</strong> - URLs for custom post_types will always look like: http://site.com/?post_type=book&p=39 even if the rest of the site is using a different permalink structure.</li>
					<li><strong>/%postname%/</strong> - Currently, you must use this custom permalink structure. Other formats are <strong>not</strong> supported.  Your URLs will look like http://site.com/<?php print htmlentities($post_type); ?>/your-title/</li>
					<li><strong>Custom</strong> - Evaluate the contents of slug</li>
				<ul>
			</span>
		</div>
		
		<!--!Rewrite Slug -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_rewrite_slug">			
			<label for="rewrite_slug" class="cctm_label cctm_text_label" id="cctm_label_rewrite_slug">Rewrite Slug</label>
			<input type="text" name="rewrite_slug" class="cctm_text" id="rewrite_slug" value="<?php print htmlspecialchars($def['rewrite_slug']); ?>"/>
			<span class="cctm_description">Prepend posts with this slug - defaults to post type's name</span>
		</div>
		
		<!--!Permalink Action -->
		
		
		<!--!Query Var -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_query_var">
			<label for="query_var" class="cctm_label cctm_text_label" id="cctm_label_query_var">Query Variable</label>
			<input type="text" name="query_var" class="cctm_text" id="query_var" value="<?php print htmlspecialchars($def['query_var']); ?>"/>
			<span class="cctm_description">(optional) Name of the query var to use for this post type. E.g. "<?php print $post_type; ?>" would make for URLs like http://site.com/?<?php print $post_type; ?>=your-title. If blank, the default structure is http://site.com/?post_type=<?php print $post_type; ?>&p=18</span>

		</div>
	</div>
	
	<!-- ================================================================================================ -->
	<div id="advanced-tab">
	
		<!-- Capability Type -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_capability_type">			
			<label for="capability_type" class="cctm_label cctm_text_label" id="cctm_label_capability_type">Capability Type</label>
			<input type="text" name="capability_type" class="cctm_text" id="capability_type" value="<?php print htmlspecialchars($def['capability_type']); ?>"/>
			<span class="cctm_description">The post type to use for checking read, edit, and delete capabilities. Default: "post"</span>
		</div>
		
		<!--! Show in Nav Menus -->			
		<div class="cctm_element_wrapper" id="custom_field_wrapper_show_in_nav_menus">		
			<input type="checkbox" name="show_in_nav_menus" class="cctm_checkbox" id="show_in_nav_menus" value="1" <?php print CCTM::is_checked($def['show_in_nav_menus']); ?>/> 
			<label for="show_in_nav_menus" class="cctm_label cctm_checkbox_label" id="cctm_label_show_in_nav_menus">Show in Nav Menus</label>
			<span class="cctm_description">Whether post_type is available for selection in navigation menus. Default: value of public argument</span>
		</div>

		
		<!--!Can Export -->
		<div class="cctm_element_wrapper" id="custom_field_wrapper_can_export">
			<input type="checkbox" name="can_export" class="cctm_checkbox" id="can_export" value="1" <?php print CCTM::is_checked($def['can_export']); ?> /> 
			<label for="can_export" class="cctm_label cctm_checkbox_label" id="cctm_label_can_export">Can Export</label>
			<span class="cctm_description">Can this post_type be exported.</span>
		</div>
		
	
		
		<div class="cctm_element_wrapper" id="custom_field_wrapper_supports_trackbacks">
					
			<input type="checkbox" name="supports[]" class="cctm_checkbox" id="supports_trackbacks" value="trackbacks" <?php print CCTM::is_checked($def['supports'], 'trackbacks'); ?> /> 
			<label for="supports_trackbacks" class="cctm_label cctm_checkbox_label" id="cctm_label_supports_trackbacks_label">Trackbacks</label>
		</div>
		
		
		<div class="cctm_element_wrapper" id="custom_field_wrapper_supports_comments">			
			<input type="checkbox" name="supports[]" class="cctm_checkbox" id="supports_comments" value="comments"  <?php print CCTM::is_checked($def['supports'], 'comments'); ?>/> 
			<label for="supports_comments" class="cctm_label cctm_checkbox_label" id="cctm_label_supports_comments_label">Enable Comments</label>
		</div>
		
		
		<div class="cctm_element_wrapper" id="custom_field_wrapper_supports_revisions">			
			<input type="checkbox" name="supports[]" class="cctm_checkbox" id="supports_revisions" value="revisions" <?php print CCTM::is_checked($def['supports'], 'revisions'); ?> /> 
			<label for="supports_revisions" class="cctm_label cctm_checkbox_label" id="cctm_label_supports_revisions_label">Store Revisions</label>
					<span class="cctm_description">Revisions are useful if you ever need to go back to an older version of a document.</span>
		</div>

		<div class="cctm_element_wrapper" id="custom_field_wrapper_has_archive">
			<input type="checkbox" name="has_archive" class="cctm_checkbox" id="has_archive" value="1" <?php print CCTM::is_checked($def['has_archive']); ?>/>
			<label for="has_archive" class="cctm_label cctm_checkbox_label" id="cctm_label_has_archive_label">
				Enable Archives
			</label>
			<span class="cctm_description">If enabled, posts will be listed in archive lists (e.g. by month).</span>
		</div>
	
		<hr />
		
		<h3>Taxonomies</h3>
		
		<p>Taxonomies offer ways to classify data as an aid to searching.</p>
		
		<div class="cctm_element_wrapper" id="custom_field_wrapper_taxonomy_categories">			
			<input type="checkbox" name="taxonomies[]" class="cctm_checkbox" id="taxonomy_categories" value="category" <?php print CCTM::is_checked($def['taxonomies'], 'category'); ?> /> 
			<label for="taxonomy_categories" class="cctm_label cctm_checkbox_label" id="cctm_label_taxonomies[]">Enable Categories</label>
			<span class="cctm_description">Hierarchical based classification.</span>
		</div>
		
		
		<div class="cctm_element_wrapper" id="custom_field_wrapper_taxonomy_tags">			
			<input type="checkbox" name="taxonomies[]" class="cctm_checkbox" id="taxonomy_tags" value="post_tag"  <?php print CCTM::is_checked($def['taxonomies'], 'post_tag'); ?>/> 
			<label for="taxonomy_tags" class="cctm_label cctm_checkbox_label" id="cctm_label_taxonomies[]">Enable Tags</label>
			<span class="cctm_description">Simple word associations.</span>
		</div>
		
		<p>Currently, this plugin only allows you to use default taxonomies with your content types. We recommend using momo360modena's <a href="http://wordpress.org/extend/plugins/simple-taxonomy/">Simple Taxonomy</a> plugin to create custom taxonomies.</p>
	</div>
</div>







		<?php wp_nonce_field($action_name, $nonce_name); ?>
	<br/>
		<div class="custom_content_type_mgr_form_controls">
			<input type="submit" name="Submit" class="button-primary" value="<?php print $submit; ?>" />
			<a class="button" href="<?php print $cancel_target_url; ?>"><?php _e('Cancel'); ?></a> 
		</div>
	
	</form>
</div>