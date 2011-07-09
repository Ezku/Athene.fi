<?php

if(!class_exists("Plugin")){

	class Plugin {
	
		public $directory;
		public $filename;
		
		public $panel = array();
		public $widget = array();
		public $meta = array();
		public $shortcode = array();
		public $dashboard_widget = array();
		
		
		
		
		
		/**
		
			__construct()
			
			Define a new plugin in the main plugin file.
			
			Usage:
			
				$my_plugin = new Plugin(__FILE__);
				
		
		*/
		public function __construct($file){
		
			$this->directory = basename(dirname($file));
			$this->filename = basename($file);
						
			register_activation_hook($file, array(&$this, "activate"));
			register_deactivation_hook($file, array(&$this, "deactivate"));
			
			add_action("admin_head", array(&$this, "admin_head"));
			add_action("wp_head", array(&$this, "head"));
			
			$this->run();
			
		}
		
		
		
		
		
		/**
		
			head()
			admin_head()
		
		*/
		public function head(){}
		public function admin_head(){}
		
		
		
		
		
		/**
		
			add_admin_page()
			
			Create admin page objects within the plugin. Extend the "admin_page" class 
			to customize.
		
		*/
		public function add_admin_page($parent, $slug, $name, $title, $capability, $callback = "admin_page", $icon = null, $position = null, $hide = false){
		
			if(empty($callback) or !class_exists($callback)){
				$callback = "admin_page";
			}
		
			if(empty($parent)){
				$this->panel[$slug] = new $callback(null, $name, $title, $capability, $slug, $this->directory, $icon, $position, $hide);
			} else {
				if(isset($this->panel[$parent])){
					$this->panel[$parent]->page[$slug] = new $callback($parent, $name, $title, $capability, $slug, $this->directory);
				}
			}
			
		}
		
		
		
		
		
		/**
		
			add_widget()
			
			Create widget objects within the plugin. See the WordPress Widgets API 
			for more information on how to build the widget.
		
		*/
		public function add_widget($class) {
			$this->widget[$class] = new widget($class);
		}
		
		
		
		
		
		/**
		
			add_dashboard_widget()
			
			Create dashboard widget objects within the plugin. See the WordPress 
			Dasboard Widgets API for more information on how to build the widget.
		
		*/
		public function add_dashboard_widget($id, $name, $class) {
			$this->dashboard_widget[$id] = new $class($id, $name);
		}
		
		
		
		
		
		/**
		
			add_meta_box()
			
			Create meta boxes on post/page edit pages. Extend the "meta_box" class
			to customize.
		
		*/
		public function add_meta_box($id, $title, $page, $callback = "meta_box", $context = "normal", $priority = "low"){
		
			if(empty($callback) or !class_exists($callback)){
				$callback = "meta_box";
			}
		
			$this->meta[$id] = new $callback($id, $title, $page, $context, $priority);
			
		}
		
		
		
		
		
		/**
		
			add_shortcode()
			
			Add a WordPress shortcode. Use a callback method defined within the
			main plugin class to customize.
		
		*/
		public function add_shortcode($code, $callback, $defaults = array()){
		
			$this->shortcode[$code] = $defaults;	
			add_shortcode($code, array(&$this, $callback));
		
		}
	
		
		
		
		
		/**
		
			activate()
			
			What to do when the plugin is activated. This is generally where
			table creation and general setup belongs.
		
		*/
		public function activate(){}
		
		
		
		
		
		/**
		
			deactivate()
			
			What to do when the plugin is deactivate.
		
		*/
		public function deactivate(){}
		
		
		
		
		
		/**
		
			run()
			
			What to do while the plugin is active.
		
		*/
		public function run(){}
		
	}

}

if(!class_exists("db_table")){

	class db_table {
	
		public $query;
	
		public function __construct($query){
		
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
					$charset_collate = " DEFAULT CHARACTER SET $wpdb->charset";
				}
				
				if(!empty($wpdb->collate)) {
					$charset_collate .= " COLLATE $wpdb->collate";
				}
				
			}
			
			$this->query = $query.$charset_collate;
			
			dbDelta($query);
		
		}
	
	}

}

if(!class_exists("dashboard_widget")){

	class dashboard_widget {
	
		public $id;
		public $name;
	
		public function __construct($id, $name){
		
			$this->id = $id;
			$this->name = $name;
		
			add_action("wp_dashboard_setup", array(&$this, "setup"));
			
		}
		
		public function setup(){
				
			wp_add_dashboard_widget($this->id, $this->name, array(&$this, "widget"), array(&$this, "control"));
			
		}
		
		public function widget(){}
		public function control(){}
	
	}

}

if(!class_exists("meta_box")){

	class meta_box {
	
		public $id;
		public $title;
		public $page;
		public $context;
		public $priority;
		
		public function __construct($id, $title, $page, $context = "normal", $priority = "low"){
			
			add_action("admin_menu", array(&$this, "init"));
			add_action("save_post", array(&$this, "save"));
	
			$this->id = $id;
			$this->title = $title;
			$this->page = $page;
			$this->context = $context;
			$this->priority = $priority;
			
		}
		
		public function init(){
		
			if(function_exists("add_meta_box")){		
				add_meta_box($this->id, __($this->title), array(&$this, "output"), $this->page, $this->context, $this->priority);	
			}
			
		}
		
		public function output(){
		
			//wp_nonce_field($this->id);			
			$this->form();
					
		}
		
		public function save($post_id){
			//if (wp_verify_nonce($_POST["_wpnonce"], $this->id) and defined('DOING_AUTOSAVE') and DOING_AUTOSAVE and (current_user_can("edit_page", $post_id) or current_user_can("edit_post", $post_id))){
			
				$this->update($post_id);
				
			//}
		}
		
		public function form(){}
		public function update($post_id){}
	
	}

}

if(!class_exists("widget")){

	class widget {
	
		public $class;
	
		public function __construct($class){
		
			$this->class = $class;
			
			if(class_exists($this->class)){
				add_action("widgets_init", create_function("", "return register_widget('".$this->class."');"));
			}
					
		}
	
	}

}

if(!class_exists("admin_page")){

	class admin_page {
		
		public $parent;
		public $name;
		public $title;
		public $capability;
		public $slug;
		public $directory;
		public $icon;
		public $position;
		public $hide;
		
		public $page = array();
			
		public function __construct($parent, $name, $title, $capability, $slug, $directory, $icon = null, $position = null, $hide = false) {
		
			add_action("admin_menu", array(&$this, "admin_menu"));
			
			$this->parent = $parent;
			$this->name = $name;
			$this->title = $title;
			$this->capability = $capability;
			$this->slug = $slug;
			$this->directory = $directory;
			$this->icon = ($icon) ? plugins_url($this->directory)."/$icon" : null;
			$this->position = $position;
			$this->hide = $hide;
		
		}
			
		public function add_page($parent, $name, $title, $capability, $slug){
			
			$this->page[] = new admin_page($parent, $name, $title, $capability, $slug, $this->directory, null, null);
			
		}
			
		public function admin_menu(){
			
			global $menu;
			global $submenu;
			
			if(function_exists("add_menu_page") and empty($this->parent)) {
			
				if($this->hide){
				
					$this->position = 999;
					
					add_menu_page(__($this->title), __($this->name), $this->capability, "hidden/".$this->slug, null, $this->icon, $this->position);
					
					unset($menu[$this->position]);				
					
				} else {
				
					add_menu_page(__($this->title), __($this->name), $this->capability, $this->directory."/".$this->slug, null, $this->icon, $this->position);
				
				}
				
			}
			
			if(function_exists("add_submenu_page") and !empty($this->parent)) {
			
				add_submenu_page($this->directory."/".$this->parent, __($this->title), __($this->name), $this->capability, $this->directory."/".$this->slug, array(&$this, "output"));
				
			}
			
		}
		
		public function output(){ ?>
			
			<div id="<?=$this->parent;?>-<?=$this->slug;?>" class="wrap">
				<div id="icon-<?=$this->slug;?>" class="icon32"><br/></div>
				<h2><?=$this->title;?></h2>
				
				<form method="post">
            
            <?
				
				wp_nonce_field($this->parent.'-'.$this->slug);

				if(isset($_REQUEST["f"]) and method_exists($this, $_REQUEST["f"])){
					
					if(isset($_POST["_wpnonce"])){
						if(wp_verify_nonce($_POST["_wpnonce"], $this->parent.'-'.$this->slug)){
							call_user_func(array(&$this, $_REQUEST["f"]));
						} else {
							echo '<p>'.__("Security check failed.").'</p>';
						}
					} else {
						call_user_func(array(&$this, $_REQUEST["f"]));
					}
				
				} else {
				
					$this->page();
				
				}
				
				?>       
   			</form>
            
			</div>
			
		<? 
		}
		
		public function page(){}
			
	}

}



?>