<form id="custom_post_type_manager_settings" method="post">

		<!--!Delete Posts -->		
		<div class="cctm_element_wrapper" id="custom_field_wrapper_delete_posts">
			<input type="checkbox" name="delete_posts" class="cctm_checkbox" id="delete_posts" value="1" <?php print $data['settings']['delete_posts']; ?>/>
			<label for="delete_posts" class="cctm_label cctm_checkbox_label" id="cctm_label_delete_posts">
				<?php _e('Delete Posts', CCTM_TXTDOMAIN); ?>
			</label>
			<span class="cctm_description"><?php _e('Check this option if you want to delete posts when you delete a content type.', CCTM_TXTDOMAIN); ?></span>			
		</div>

		<!--!Delete Custom Fields -->		
		<div class="cctm_element_wrapper" id="custom_field_wrapper_delete_custom_fields">
			<input type="checkbox" name="delete_custom_fields" class="cctm_checkbox" id="delete_custom_fields" value="1" <?php print $data['settings']['delete_custom_fields']; ?>/>
			<label for="delete_custom_fields" class="cctm_label cctm_checkbox_label" id="cctm_label_delete_custom_fields">
				<?php _e('Delete Custom Fields', CCTM_TXTDOMAIN); ?>
			</label>
			<span class="cctm_description"><?php _e('Check this option if you want to delete custom fields from the database when you delete a custom field definition.', CCTM_TXTDOMAIN); ?></span>			
		</div>
		
		
		<!--!Add Custom Fields when associated -->		
		<!-- div class="cctm_element_wrapper" id="custom_field_wrapper_add_custom_fields">
			<input type="checkbox" name="add_custom_fields" class="cctm_checkbox" id="add_custom_fields" value="1" <?php print $data['settings']['add_custom_fields']; ?>/>
			<label for="add_custom_fields" class="cctm_label cctm_checkbox_label" id="cctm_label_add_custom_fields">
				<?php _e('Add Custom Fields', CCTM_TXTDOMAIN); ?>
			</label>
			<span class="cctm_description"><?php _e('Check this option if you want to force custom fields to be created in the database when you associate custom field with a content type.', CCTM_TXTDOMAIN); ?></span>			
		</div -->




		<!--!Update Custom Field with new Default Values -->		
		<!-- div class="cctm_element_wrapper" id="custom_field_wrapper_update_custom_fields">
			<input type="checkbox" name="update_custom_fields" class="cctm_checkbox" id="update_custom_fields" value="1" <?php print $data['settings']['update_custom_fields']; ?>/>
			<label for="update_custom_fields" class="cctm_label cctm_checkbox_label" id="cctm_label_update_custom_fields">
				<?php _e('Update Default Values', CCTM_TXTDOMAIN); ?>
			</label>
			<span class="cctm_description"><?php _e('Check this option if you want custom fields containing the old default value to be updated when the default value is changed.', CCTM_TXTDOMAIN); ?></span>			
		</div -->
		
		<!--!Custom Fields Menu -->		
		<div class="cctm_element_wrapper" id="custom_field_wrapper_show_custom_fields_menu">
			<input type="checkbox" name="show_custom_fields_menu" class="cctm_checkbox" id="show_custom_fields_menu" value="1" <?php print $data['settings']['show_custom_fields_menu']; ?>/>
			<label for="show_custom_fields_menu" class="cctm_label cctm_checkbox_label" id="cctm_label_show_custom_fields_menu">
				<?php _e('Show Custom Fields Menu', CCTM_TXTDOMAIN); ?>
			</label>
			<span class="cctm_description"><?php _e('Check this option if you want a "Custom Fields" menu item to appear under each post type.', CCTM_TXTDOMAIN); ?></span>			
		</div>
		
		<!--!Settings Menu -->		
		<div class="cctm_element_wrapper" id="custom_field_wrapper_show_settings_menu">
			<input type="checkbox" name="show_settings_menu" class="cctm_checkbox" id="show_settings_menu" value="1" <?php print $data['settings']['show_settings_menu']; ?>/>
			<label for="show_settings_menu" class="cctm_label cctm_checkbox_label" id="cctm_label_show_settings_menu">
				<?php _e('Show Settings Menu', CCTM_TXTDOMAIN); ?>
			</label>
			<span class="cctm_description"><?php _e('Check this option if you want a "Settings" menu item to appear under each custom post type.', CCTM_TXTDOMAIN); ?></span>			
		</div>
	
		<!--!Show foreign post types -->		
		<div class="cctm_element_wrapper" id="custom_field_wrapper_show_foreign_post_types">
			<input type="checkbox" name="show_foreign_post_types" class="cctm_checkbox" id="show_foreign_post_types" value="1" <?php print $data['settings']['show_foreign_post_types']; ?>/>
			<label for="show_foreign_post_types" class="cctm_label cctm_checkbox_label" id="cctm_label_show_foreign_post_types">
				<?php _e('Display Foreign Post Types', CCTM_TXTDOMAIN); ?>
			</label>
			<span class="cctm_description"><?php _e("Check this box if you want to display any post-types registered with some other plugin. You won't be able to edit them, but you'll know they are there.", CCTM_TXTDOMAIN); ?></span>
		</div>
		
		<!--!Custom Field settings links -->
		<?php print $data['custom_fields_settings_links']; ?>
		
	<?php wp_nonce_field($data['action_name'], $data['nonce_name']); ?>
	<br/>
	<div class="custom_content_type_mgr_form_controls">
		<input type="submit" name="Submit" class="button-primary" value="<?php print $data['submit']; ?>" />
	</div>
</form>