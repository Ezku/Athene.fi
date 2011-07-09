<?php
/*
Plugin Name: Custom Content Types
Plugin URI: http://www.ooeygui.net
Description: Create and manage custom content types and custom fields for posts.
Version: 1.0.2
Author: Ian Whitcomb
Author URI: http://www.ooeygui.net
*/

include_once("_include/class.wp-plugins.php");
include_once("_include/functions.php");


/**

	Custom Content Types Class
	
	This is the custom content types plugin main class.

*/

class CCT extends Plugin {




	/**
	
		activate()
		
		What to do when we activate the plugin.
	
	*/
	public function activate(){
	
		// Setup the database.
		global $wpdb;
		
		if(@is_file(ABSPATH."/wp-admin/upgrade-functions.php")) {
			include_once(ABSPATH."/wp-admin/upgrade-functions.php");
		} elseif(@is_file(ABSPATH."/wp-admin/includes/upgrade.php")) {
			include_once(ABSPATH."/wp-admin/includes/upgrade.php");
		} else {
			die("Unable to locate \"wp-admin/upgrade-functions.php\" and/or \"wp-admin/includes/upgrade.php\"");
		}
			
		$charset_collate = "";
		if($wpdb->supports_collation()) {
		
			if(!empty($wpdb->charset)) {
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}
			
			if(!empty($wpdb->collate)) {
				$charset_collate .= " COLLATE $wpdb->collate";
			}
			
		}
						
		if($wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."content_types'") != $wpdb->prefix."content_types") {
			
			$tbl["content_types"] = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."content_types (
				name varchar(20) NOT NULL,
				label varchar(128) NOT NULL,
				label_plural varchar(128) NOT NULL,
				description TEXT NULL DEFAULT NULL,
				PRIMARY KEY (name)
				) $charset_collate;";
				
			dbDelta($tbl["content_types"]);
			
		}
				
		// Set the default initial plugin settings.
		if(!get_option("cct_user_capability")){
			update_option("cct_user_capability", "edit_others_pages");
		}
		
				
	}
	
		
	
	
	/**
	
		deactivate()
		
		What to do when we deactivate the plugin.
	
	*/
	public function deactivate(){
		
		
		
	}
	
	
	
	
	/**
	
		admin_head()
		
	*/
	public function admin_head(){
	
		echo '
		<link rel="stylesheet" href="'.plugins_url().'/'.$this->directory.'/style.css" />
		<script language="javascript" type="text/javascript" src="'.plugins_url().'/'.$this->directory.'/js/scripts.js"></script>
		
		<script>
			cct_url = "'.plugins_url().'/'.$this->directory.'";
			blog_url = "'.get_bloginfo("url").'";
			cct_directory = "'.$this->directory.'";
		</script>';
	
	}
		
	
	
	
	/**
	
		run()
		
		What to do while the plugin is active.
	
	*/
	public function run(){
	
		global $wpdb;
		
		$this->add_admin_page(null, "cct", "Content Types", "Content Types", get_option("cct_user_capability"), null, "images/icon-sm.png", null, false);
		$this->add_admin_page("cct", "cct", "Content Types", "Content Types", get_option("cct_user_capability"), "cct_edit_page");
		$this->add_admin_page("cct", "about", "About", "About Custom Content Types", get_option("cct_user_capability"), "cct_about_page");
		
		add_action("init", array(&$this, "init_content_types"));

	}
	
	public function init_content_types(){
	
		$content_types = $this->get_content_types();
				
		if(!empty($content_types)){

			foreach($content_types as $content_type){
			
				register_post_type($content_type["name"], array(
					"labels" => array(
						"name" => _x($content_type["label_plural"], 'post type general name'),
						"singular_name" => _x($content_type["label"], 'post type singular name'),
						"add_new" => _x('Add New', $content_type["name"]),
						"add_new_item" => __('Add New '.$content_type["label"]),
						"edit_item" => __('Edit '.$content_type["label"]),
						"new_item" => __('New '.$content_type["label"]),
						"view_item" => __('View '.$content_type["label"]),
						"search_items" => __('Search '.$content_type["label_plural"]),
						"not_found" =>  __('No '.$content_type["label_plural"].' found'),
						"not_found_in_trash" => __('No '.$content_type["label_plural"].' found in Trash'), 
						"parent_item_colon" => '',
						"supports" => array('title','editor','author','thumbnail','excerpt','comments')
					),
					"public" => true,
					"publicly_queryable" => true,
					"show_ui" => true, 
					"query_var" => true,
					"rewrite" => true,
					"capability_type" => 'post',
					"hierarchical" => false,
					"menu_position" => null,
					"supports" => array('title','editor','author','thumbnail','excerpt','comments','trackbacks','custom-fields','revisions','page-attributes')
				));
				
				if(class_exists("reus_meta")){
					$this->add_meta_box("reusables", "Reusable Regions", $content_type["name"], "reus_meta");
				}
								
			}
			
		}
		
	}
	
	
	
	
	/*
	
		add_content_type()
		
		Add a new content type with associated fields.
	
	*/
	public function add_content_type($name, $label, $plural, $description = null){
	
		global $wpdb;
		
		if($wpdb->insert($wpdb->prefix."content_types", array("name" => $this->format_name($name), "label" => $label, "label_plural" => $plural, "description" => $description), array("%s", "%s", "%s", "%s"))){
			return true;
		} else {
			return false;
		}
	
	}
	
	
	
	
	/*
	
		get_content_type()
		
		Return content type data.
	
	*/
	public function get_content_type($name){
		global $wpdb;
		$content_type = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."content_types WHERE name = '$name'");
		return $content_type;
	}
	
	
	
	
	/*
	
		update_content_type()
		
		Update a content type or create a new one if it doesnt exist.
	
	*/
	public function update_content_type($name, $label, $plural, $description){
		global $wpdb;
		
		$content_type = $this->get_content_type($name);
		
		if(!empty($content_type) and $wpdb->update($wpdb->prefix."content_types", array("label" => $label, "label_plural" => $plural, "description" => $description), array("name" => $name), array("%s", "%s", "%s"), array("%s"))){
			return true;
		} 
		elseif($this->add_content_type($this->format_name($name), $label, $plural)){
			return true;
		}
		else {
			return false;
		}
	}
	
	
	
	
	/*
	
		delete_content_type()
		
		Delete a content type.
	
	*/
	public function delete_content_type($name){
		
		global $wpdb;
		
		if($wpdb->query("DELETE FROM ".$wpdb->prefix."content_types WHERE name = '$name'")){
			return true;
		} else {
			return false;
		}
		
	}
	
	
	
	
	/*
	
		get_content_types()
		
		Returns an array of ALL content types
	
	*/
	public function get_content_types(){
		
		global $wpdb;
		
		$wp_content_types = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."content_types");		
		
		if(!empty($wp_content_types)){
			foreach($wp_content_types as $content_type){
				$content_types[$content_type->name]["name"] = $content_type->name;
				$content_types[$content_type->name]["label"] = $content_type->label;
				$content_types[$content_type->name]["label_plural"] = $content_type->label_plural;
			}
		}
		
		return $content_types;	
		
	}
	
	
	
	
	/*
	
		format_name()
		
		Replace all non-alpha-numeric characters with a dash.
	
	*/
	public function format_name($name){
		$name = preg_replace('/[^a-zA-Z0-9]/','-',strtolower($name));
		$name = preg_replace('/[\-]+/', '-', $name);
		return trim($name, "-");
	}


}




/* Individual Admin Page Code */
class cct_edit_page extends admin_page {

	public function page(){
		global $wpdb;
		global $content_types;
		
		include_once("screens/page-edit.php");
				
	}
	
	public function single(){
		global $wpdb;
		global $content_types;
		
		include_once("screens/page-edit.php");
	}

}
class cct_about_page extends admin_page {

	public function page(){
		global $wpdb;
		global $content_types;
		include_once("screens/page-about.php");
	}

}




$content_types = new CCT(__FILE__);

?>