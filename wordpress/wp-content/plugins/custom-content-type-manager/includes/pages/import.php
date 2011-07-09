<?php
/*------------------------------------------------------------------------------
This page lists the contents of the definition library 
(wp-content/uploads/cctm/defs)
And it previews the definition inside $settings['candidate'].
------------------------------------------------------------------------------*/
$settings = get_option(self::db_key_settings, array() );
$candidate = self::_get_value($settings, 'candidate');
$info = self::_get_value($candidate, 'export_info');

?>
<div class="wrap">
	<h2>
		<img src="<?php print CCTM_URL; ?>/images/cctm-logo.jpg" alt="summarize-posts-logo" width="88" height="55" class="polaroid"/> CCTM : Import
			<a href="http://code.google.com/p/wordpress-custom-content-type-manager/wiki/Import" title="Importing your CCTM Definition" target="_blank">
			<img src="<?php print CCTM_URL; ?>/images/question-mark.gif" width="16" height="16" />
		</a>
	</h2>

	<?php print $msg; ?>

	<table>
		<tr>
			<td width="400">
	<!-- Column 1 -->
	<h2>Manage Library</h2>
	

	<p><?php printf( __('You can import an existing %s definition file from your computer or choose one from your uploads directory: %s. You probably will only use this when you are first setting up your site.', CCTM_TXTDOMAIN)
		, '<code>.cctm.json</code>'
		, '<code>wp-content/'.self::base_storage_dir .'/'.self::def_dir.'</code>'
		); ?></p>
	
	<h3>Definitions on File</h3>
	<div id="cctm_library">
		<table>
			<thead>
				<tr>
					<th>Name</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
<?php
//------------------------------------------------------------------------------
// Loop over definitions
//------------------------------------------------------------------------------
		if (!CCTM::create_verify_storage_directories()) {
			print '<tr><td class="cctm_msg" colspan="2">'.__('Library directory does not exist.', CCTM_TXTDOMAIN) . '</td></tr>';
		}
		else
		{		
			
			$upload_dir = wp_upload_dir();
			$dir = $upload_dir['basedir'] .'/'.self::base_storage_dir . '/' . self::def_dir .'/';
			
			$i = 0;
			$class = '';
			if ($handle = opendir($dir)) {
				while (false !== ($file = readdir($handle))) {
					// Some files look like "your_def.cctm (1).json"
					if ( !preg_match('/^\./', $file) && preg_match('/.json$/i', $file) ) {
						if ( $i & 1) {
							$class = 'cctm_evenrow';	
						}
						else {
							$class = 'cctm_oddrow';						
						}
						printf('<tr class="%s"><td>%s</td><td><a href="%s" class="button">%s</a></td></tr>
						'
							, $class
							, $file
							, self::_link_preview_def().'&file='.$file
							, __('Preview')
						);
						$i = $i + 1;
					}
				}
				closedir($handle);
			}
			// Library empty.
			if (!$i) {
				print '<tr><td class="cctm_msg" colspan="2">'.__('Library empty.', CCTM_TXTDOMAIN) . '</td></tr>';
			}
		}
//------------------------------------------------------------------------------		
?>
		</table>
	</div>
	
	<form id="cctm_import_form"  method="post" enctype="multipart/form-data">
    	<!-- MAX_FILE_SIZE must precede the file input field -->
	    <input type="hidden" name="MAX_FILE_SIZE" value="<?php print CCTM::max_def_file_size; ?>" />
	    
		<?php wp_nonce_field($action_name, $nonce_name); ?>

		<label for="cctm_settings_file" class="cctm_file_label"><?php _e('Upload New File', CCTM_TXTDOMAIN); ?></label><br/>
		<input type="file" id="cctm_settings_file" name="cctm_settings_file" />
		
		<br/><br/>
		<input type="submit" name="submit" class="button" value="<?php _e('Upload'); ?>"/>
	</form>


			</td>
			<td width="300">
			
	<!-- Column 2 -->
	<h2>Preview</h2>
	<div class="cctm_def_preview">
<?php 
//------------------------------------------------------------------------------
if ( !empty($info) ): 
//------------------------------------------------------------------------------
?>
		<h3><?php print self::_get_value($info, 'title'); ?></h3>
		<table>
			<tr><td>Author:</td><td><?php print self::_get_value($info, 'author'); ?></td></tr>
			<tr><td>URL:</td><td><a href="<?php print self::_get_value($info, 'url'); ?>"><?php print self::_get_value($info, 'url'); ?></a></td></tr>
			<tr><td>Description:</td><td><p><?php print self::_get_value($info, 'description'); ?></p></td></tr>
		</table>
<?php 
//------------------------------------------------------------------------------
endif; 
//------------------------------------------------------------------------------
// Check whether the candidate is equivalent to the currently loaded def.
if ( ImportExport::defs_are_equal($candidate['payload'], CCTM::$data) ):
//------------------------------------------------------------------------------
?>
		<div class="cctm_def_active"><?php _e('Definition Active', CCTM_TXTDOMAIN); ?></div>
<?php
else:
?>		
		<a href="<?php print self::_link_activate_imported_def(); ?>" class="button"><?php _e('Activate', CCTM_TXTDOMAIN); ?></a>
<?php 
//------------------------------------------------------------------------------
endif; 
//------------------------------------------------------------------------------
?>
	</div>		
			</td>
		</tr>
	</table>

	<br/>
	
	<?php include('components/footer.php'); ?>
	
</div>