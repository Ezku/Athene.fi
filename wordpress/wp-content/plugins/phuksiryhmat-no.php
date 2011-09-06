<?php
/**
 * @package Toimijalistat query vars
 * @version 1.0
 */
/*
Plugin Name: Toimijalistat query vars
Plugin URI: http://pkroger.org
Description: 
Author: Pyry Kröger
Version: 1.0
Author URI: http://pkroger.org/
*/

add_filter( 'generate_rewrite_rules', 'add_toimijalistat_rewrite' ); 
function add_toimijalistat_rewrite($wp_rewrite) {
  $options = get_option('toimijalistat_options');
  $new_rules = array( 
       'lista/phuksit/(.+)/(.+)' => 'index.php?post_type=toimijalista&p='.$options['phuksiryhmat']['page'].'&vuosi='.$wp_rewrite->preg_index(1).'&ryhma=' . $wp_rewrite->preg_index(2),
       'lista/hallitus/(.+)' => 'index.php?post_type=toimijalista&p='.$options['toimijat']['hallituspage'].'&vuosi='.$wp_rewrite->preg_index(1),
       'lista/toimihenkilot/(.+)' => 'index.php?post_type=toimijalista&p='.$options['toimijat']['toimihenkilotpage'].'&vuosi='.$wp_rewrite->preg_index(1)
  );

  //​ Add the new rewrite rule into the top of the global rules array
  $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}

add_filter('query_vars', 'toimijalistat_query_vars');
function toimijalistat_query_vars($query_vars)
{
    $query_vars[] = 'ryhma';
    $query_vars[] = 'vuosi';
    return $query_vars;
}

// admin interface

add_action('admin_init', 'toimijalistat_options_init' );

function toimijalistat_options_init() {
  register_setting( 'toimijalistat_options', 'toimijalistat_options' );
  add_settings_section('toimijalistat_main', 'Main Settings', 'toimijalistat_plugin_section_text', 'toimijalistat');
  add_settings_field('toimijalistat_page', '', 'toimijalistat_plugin_setting_string', 'toimijalistat', 'toimijalistat_main');
}

function toimijalistat_plugin_section_text() {
}

function toimijalistat_plugin_setting_string() {
  $options = get_option('toimijalistat_options'); 
  $pages = get_posts(array('post_type' => 'toimijalista'));
  ?>
  <tr><th colspan="2"><b>Phuksiryhmät</b></th></tr>
  <tr>
		<th scope="row">Page</th>
		<td>
		  <select id='toimijalistat_page' name='toimijalistat_options[phuksiryhmat][page]'>
		    <?php foreach($pages as $pg) { ?>
		      <option value="<?php echo $pg->ID ?>" <?php echo $options['phuksiryhmat']['page'] == $pg->ID ? 'selected="selected"' : "" ?> ><?php echo $pg->post_title ?></option>
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
		  <input type="text" id="toimijalistat_year" name="toimijalistat_options[phuksiryhmat][year]" value="<?php echo $options['phuksiryhmat']['year'] ?>" id="" />
		  <p>(For links, all years are accessible with proper URL)</p>
		</td>
	</tr>
	<tr>
		<th scope="row">Number of groups shown</th>
		<td>
		  <input type="text" id="toimijalistat_groups" name="toimijalistat_options[phuksiryhmat][groups]" value="<?php echo $options['phuksiryhmat']['groups'] ?>" id="" />
		  <p>(For links, all groups are accessible with proper URL)</p>
		</td>
	</tr>
	<tr><th colspan="2"><b>Toimijat</b></th></tr>
	<tr>
		<th scope="row">Hallitus</th>
		<td>
		  <select id='toimijalistat_page' name='toimijalistat_options[toimijat][hallituspage]'>
		    <?php foreach($pages as $pg) { ?>
		      <option value="<?php echo $pg->ID ?>" <?php echo $options['toimijat']['hallituspage'] == $pg->ID ? 'selected="selected"' : "" ?> ><?php echo $pg->post_title ?></option>
		    <?php } ?>
		    <?php if (sizeof($pages) == 0) { ?>
		      <p>No posts with category 'toimijalista' found</p>
		    <?php } ?>
		  </select>
		</td>
	</tr>
	<tr>
		<th scope="row">Toimihenkilöt</th>
		<td>
		  <select id='toimijalistat_page' name='toimijalistat_options[toimijat][toimihenkilotpage]'>
		    <?php foreach($pages as $pg) { ?>
		      <option value="<?php echo $pg->ID ?>" <?php echo $options['toimijat']['toimihenkilotpage'] == $pg->ID ? 'selected="selected"' : "" ?> ><?php echo $pg->post_title ?></option>
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
		  <input type="text" id="toimijalistat_year" name="toimijalistat_options[toimijat][year]" value="<?php echo $options['toimijat']['year'] ?>" id="" />
		  <p>(For links, all years are accessible with proper URL)</p>
		</td>
	</tr>
	<?php
}


add_action('admin_menu', 'toimijalistat_menu');

function toimijalistat_menu() {
	add_options_page('Toimijalistat Query Vars Options', 'Toimijalistat Query Vars', 'manage_options', 'toimijalistat', 'toimijalistat_options');
}

function toimijalistat_options() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	?>
	<div class="wrap">
	  <div id="icon-options-general" class="icon32"><br /></div>

		<h2>toimijalistat Options</h2>
	  <form method="post" action="options.php" id="test-form">
	    <?php settings_fields('toimijalistat_options'); ?>
	    <table class="form-table">
    		<?php do_settings_sections('toimijalistat'); ?>
    	</table>
    	<p><input type="submit" class="button-primary" value="Save" /></p>
	  </form>
	</div>
<?php
}

?>
