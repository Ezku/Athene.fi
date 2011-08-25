<?php
/**
 * @package Phuksiryhmat number
 * @version 1.0
 */
/*
Plugin Name: Phuksiryhmat number
Plugin URI: http://pkroger.org
Description: 
Author: Pyry Kröger
Version: 1.0
Author URI: http://pkroger.org/
*/

add_filter( 'generate_rewrite_rules', 'add_phuksiryhmat_rewrite' ); 
function add_phuksiryhmat_rewrite($wp_rewrite) {
  $options = get_option('phuksiryhmat_options');
  $new_rules = array( 
       'lista/phuksit/(.+)/(.+)' => 'index.php?post_type=toimijalista&p='.$options['page'].'&vuosi='.$wp_rewrite->preg_index(1).'&ryhma=' .
         $wp_rewrite->preg_index(2) );

  //​ Add the new rewrite rule into the top of the global rules array
  $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}

add_filter('query_vars', 'phuksiryhmat_query_vars');
function phuksiryhmat_query_vars($query_vars)
{
    $query_vars[] = 'ryhma';
    $query_vars[] = 'vuosi';
    return $query_vars;
}

// admin interface

add_action('admin_init', 'phuksiryhmat_options_init' );

function phuksiryhmat_options_init() {
  register_setting( 'phuksiryhmat_options', 'phuksiryhmat_options' );
  add_settings_section('phuksiryhmat_main', 'Main Settings', 'phuksiryhmat_plugin_section_text', 'phuksiryhmat');
  add_settings_field('phuksiryhmat_page', '', 'phuksiryhmat_plugin_setting_string', 'phuksiryhmat', 'phuksiryhmat_main');
}

function phuksiryhmat_plugin_section_text() {
}

function phuksiryhmat_plugin_setting_string() {
  $options = get_option('phuksiryhmat_options'); 
  $pages = get_posts(array('post_type' => 'toimijalista'));
  ?>
  <tr>
		<th scope="row">Page</th>
		<td>
		  <select id='phuksiryhmat_page' name='phuksiryhmat_options[page]'>
		    <?php foreach($pages as $pg) { ?>
		      <option value="<?php echo $pg->ID ?>" <?php echo $options['page'] == $pg->ID ? 'selected="selected"' : "" ?> ><?php echo $pg->post_title ?></option>
		    <?php } ?>
		    <?php if (sizeof($pages) == 0) { ?>
		      <p>No posts with category 'toimijalista' found</p>
		    <?php } ?>
		  </select>
		</td>
	</tr>
	<tr>
		<th scope="row">Year shown</th>
		<td>
		  <input type="text" id="phuksiryhmat_year" name="phuksiryhmat_options[year]" value="<?php echo $options['year'] ?>" id="" />
		  <p>(For links, all years are accessible with proper URL)</p>
		</td>
	</tr>
	<tr>
		<th scope="row">Number of groups shown</th>
		<td>
		  <input type="text" id="phuksiryhmat_groups" name="phuksiryhmat_options[groups]" value="<?php echo $options['groups'] ?>" id="" />
		  <p>(For links, all groups are accessible with proper URL)</p>
		</td>
	</tr>
	<?php
}


add_action('admin_menu', 'phuksiryhmat_menu');

function phuksiryhmat_menu() {
	add_options_page('Phuksiryhmat Options', 'Phuksiryhmat', 'manage_options', 'phuksiryhmat', 'phuksiryhmat_options');
}

function phuksiryhmat_options() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	?>
	<div class="wrap">
	  <div id="icon-options-general" class="icon32"><br /></div>

		<h2>Phuksiryhmat Options</h2>
	  <form method="post" action="options.php" id="test-form">
	    <?php settings_fields('phuksiryhmat_options'); ?>
	    <table class="form-table">
    		<?php do_settings_sections('phuksiryhmat'); ?>
    	</table>
    	<p><input type="submit" class="button-primary" value="Save" /></p>
	  </form>
	</div>
<?php
}

?>
