<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
	<?php	$this->render_admin ('annoy'); ?>

	<?php screen_icon(); ?>
	
	<h2><?php _e ('Search Unleashed | Options', 'search-unleashed'); ?></h2>
	
	<?php $this->submenu (true); ?>
	
	<form action="<?php echo $this->url ($_SERVER['REQUEST_URI']) ?>" method="post" accept-charset="utf-8" style="clear: both">
		<?php wp_nonce_field ('searchunleashed-options'); ?>
		
		<h3><?php _e ('General Options', 'search-unleashed')?></h3>

		<table class="form-table">
		<tr>
			<th><label for="support"><?php _e ('Plugin Support', 'search-unleashed'); ?>:</label></th>
			<td>
				<input type="checkbox" name="support" id="support"<?php if ($options['support']) echo ' checked="checked"' ?>/>
				<span class="sub"><?php printf( __( 'Click this if you have <a href="%s">supported</a> the author', 'search-unleashed'), $this->base().'?page=search-unleashed.php&amp;sub=support'); ?></span>
			</td>
		</tr>
		<tr>
			<th><?php _e ('Log Expiry', 'search-unleashed'); ?>:</th>
			<td>
				<input type="text" name="expiry" value="<?php echo $options['expiry']; ?>" />
				<span class="sub"><?php _e ('Expiry value in days. Enter 0 for no expiry and -1 for no logs', 'search-unleashed'); ?></span>
			</td>
		</tr>
		<tr>
			<th><label for="replace_category"><?php _e ('Replace category archive', 'search-unleashed'); ?>:</label></th>
			<td>
				<input type="checkbox" name="replace_category" id="replace_category"<?php if ($options['replace_category']) echo ' checked="checked"' ?>/>
				<span class="sub"><?php _e( 'Replace category archive pages with a search based on the category name', 'search-unleashed'); ?></span>
			</td>
		</tr>
		<tr>
			<th><label for="replace_tag"><?php _e ('Replace tag archive', 'search-unleashed'); ?>:</label></th>
			<td>
				<input type="checkbox" name="replace_tag" id="replace_tag"<?php if ($options['replace_tag']) echo ' checked="checked"' ?>/>
				<span class="sub"><?php _e( 'Replace tag archive pages with a search based on the tag name', 'search-unleashed'); ?></span>
			</td>
		</tr>
		</table>

		<h3><?php _e ('Spider Options', 'search-unleashed')?></h3>

		<p><?php _e( 'Changing any option in this section will require rebuilding the search index.', 'search-unleashed')?></p>
			
			<table class="form-table">
				<tr>
					<th><label for="search_engine"><?php _e ('Search Engine', 'search-unleashed'); ?>:</label></th>
					<td>
						<select name="search_engine">
							<?php $this->select ($engines, $options['search_engine']); ?>
						</select>
					</td>
				</tr>
				<tr>
					<th><?php _e ('Pages to exclude', 'search-unleashed'); ?>:</th>
					<td><input size="40" type="text" name="exclude" value="<?php echo $options['exclude'] ?>"/>
						<span class="sub"><?php _e ('Comma-separated list of page/post IDs', 'search-unleashed'); ?></span></td>
				</tr>
				<tr>
					<th><?php _e ('Categories to exclude', 'search-unleashed'); ?>:</th>
					<td><input size="40" type="text" name="exclude_cat" value="<?php echo $options['exclude_cat'] ?>"/>
						<span class="sub"><?php _e ('Comma-separated list of category IDs', 'search-unleashed'); ?></span></td>
				</tr>
				<tr>
					<th><?php _e ('Include', 'search-unleashed'); ?>:</th>
					<td>
						<label><input type="checkbox" name="pages" id="pages"<?php if ($options['pages']) echo ' checked="checked"' ?>/> <?php _e ('pages', 'search-unleashed'); ?></label>
						<label><input type="checkbox" name="posts" id="posts"<?php if ($options['posts']) echo ' checked="checked"' ?>/> <?php _e ('posts', 'search-unleashed'); ?></label>
						
						(<label><input type="checkbox" name="protected" id="protected"<?php if ($options['protected']) echo ' checked="checked"' ?>/> <?php _e ('password-protected', 'search-unleashed'); ?></label>
						<label><input type="checkbox" name="private" id="private"<?php if ($options['private']) echo ' checked="checked"' ?>/> <?php _e ('private', 'search-unleashed'); ?></label>
						<label><input type="checkbox" name="draft" id="draft"<?php if ($options['draft']) echo ' checked="checked"' ?>/> <?php _e ('draft', 'search-unleashed'); ?></label>
						)
					</td>
				</tr>
			</table>
		
			<h3><?php _e ('Theme Options', 'search-unleashed')?></h3>
			<table class="form-table">
				<tr>
					<th><?php _e ('Force content display', 'search-unleashed'); ?>:</th>
					<td>
						<input type="checkbox" name="force_display"<?php echo $this->checked ($options['force_display']) ?>/>
						<span class="sub"><?php _e ('Some themes don\'t display any search result content.  Enable this option to force the theme to display results', 'search-unleashed'); ?></span>
					</td>
				</tr>
				<tr>
					<th><label for="page_title"><?php _e ('Search results page title', 'search-unleashed'); ?>:</label></th>
					<td><input type="checkbox" name="page_title" id="page_title"<?php if ($options['page_title']) echo ' checked="checked"' ?>/>
						<span class="sub"><?php _e ('Change page title on search results to reflect the search condition', 'search-unleashed'); ?></span>
						</td>
				</tr>

				<tr>
					<th><label for="incoming"><?php _e ('Highlight searches', 'search-unleashed'); ?>:</label></th>
					<td>
						<input type="checkbox" name="highlight_search"<?php echo $this->checked ($options['highlight_search']) ?>/>
						<span class="sub"><?php _e ('Highlight searches on search page or default to <code>the_excerpt</code>', 'search-unleashed'); ?></span>
						</td>
				</tr>
				<tr>
					<th><label for="incoming"><?php _e ('Highlight incoming searches', 'search-unleashed'); ?>:</label></th>
					<td>
						<select name="incoming">
							<?php echo $this->select (array ('none' => __ ('No highlighting', 'search-unleashed'), 'content' => __ ('Content, no titles', 'search-unleashed'), 'all' => __ ('Content &amp; titles', 'search-unleashed')), $options['incoming']); ?>
						</select>
						<span class="sub"><?php _e ('Highlighting phrases and display a help box on post pages after a search has been performed. Note this will not work with caching plugins.', 'search-unleashed'); ?></span>
						</td>
				</tr>
				<tr>
					<th valign="top"><label for="theme_title_position"><?php _e ('Theme title position', 'search-unleashed'); ?>:</label></th>
					<td>
						<select name="theme_title_position">
							<option value="0"<?php if ($options['theme_title_position'] == 0) echo ' selected="selected"' ?>>0</option>
							<option value="1"<?php if ($options['theme_title_position'] == 1) echo ' selected="selected"' ?>>1 <?php _e ('(includes default theme)', 'search-unleashed'); ?></option>
							<option value="2"<?php if ($options['theme_title_position'] == 2) echo ' selected="selected"' ?>>2</option>
						</select>
						<br/>
						<span class="sub"><?php _e ('Most themes require a position of 1, but if you have incorrect highlighting in titles set this to another value', 'search-unleashed'); ?></span>
						</td>
				</tr>

			</table>

			<h3><?php _e ('Search Style', 'search-unleashed'); ?></h3>
			<table class="form-table">
				<tr>
					<th><label for="include_css"><?php _e ('Include highlight CSS', 'search-unleashed'); ?>:</label></th>
					<td><input type="checkbox" name="include_css" id="include_css"<?php if ($options['include_css']) echo ' checked="checked"' ?>/></td>
				</tr>
				<?php for ($x = 0; $x < 5; $x++) : ?>
				<tr>
					<th><?php _e ('Highlight colour', 'search-unleashed'); ?> #<?php echo $x + 1 ?>:</th>
					<td>
						<input class="colorinput" size="6" type="text" name="highlight[<?php echo $x ?>]" value="<?php echo $options['highlight'][$x] ?>"/>
						<span style="background-color: #<?php echo $options['highlight'][$x] ?>" class="colour" id="colour_<?php echo $x ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
					
						<span class="sub"> <?php _e ('Specify colour as a hex value', 'search-unleashed'); ?></span>
					</td>
				</tr>
				<?php endfor; ?>
			</table>
			<br/>
			<p><input type="submit" class="button-primary" name="save" value="<?php _e ('Save Options', 'search-unleashed'); ?>"/></p>
	</form>
</div>

<div class="wrap">
	<h3><?php _e( 'Remove Search Unleashed', 'search-unleashed' )?></h3>
	
	<p><?php _e( 'This will delete all settings, database tables, and deactivate the plugin.', 'search-unleashed' )?></p>
	
	<form action="<?php echo $this->url ($_SERVER['REQUEST_URI']) ?>" method="post" accept-charset="utf-8">
		<?php wp_nonce_field( 'searchunleashed-delete_plugin' ); ?>
		
		<input class="button-secondary" type="submit" name="delete" value="<?php _e ('Delete Search Unleashed', 'search-unleashed'); ?>" onclick="return confirm ('Are you sure you want to remove Search Unleashed and all settings?')"/>
	</form>
</div>
