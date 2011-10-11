<?php

/**
 * Framework class for all plugins. For WordPress 2.3+ only
 * Note: ...rename the prefix name of this class... 
 * @author Michael Wöhrer
 * @version 1.6, October 2010
 */
class MaintenanceMode_SWGPluginFramework {

	var $g_info;			# information about the plugin 
	var $g_opt;				# plugin options
	var $g_opt_default;		# plugin default options
	var $g_contentmain;		# content in the main area
	var $g_contentsidebar;	# content in the sidebar

	/**
	 * Set global variables & check version
     * 
     * @param array $pluginInfo array of plugin information
     * @param array $pluginDefaultOptions array of plugin default options
	 */
	function Initialize($pluginInfo, $pluginDefaultOptions) {
		// Set global variable
		$this->g_info = $pluginInfo;
		
		// Check WP version and display warning if not compatible
		$this->WarningIfPluginNotCompatible();
	
		// Initialize the base64 icons
		$this->IniBase64Icons();

		// Plugin default options
		$this->g_opt_default = $pluginDefaultOptions;

		// Initialize plugin options
		$this->IniOrUpdateOptions();

		// Language file
		// Doesn't work properly prior to WP2.7; Let's make it like in plugin 'Google Sitemap', thanks to Arne Brachhold :-)
		global $wp_version;
		if ( version_compare($wp_version, '2.7', '>=' ) ) {
			// >= WordPress 2.7
			load_plugin_textdomain($this->g_info['ShortName'], false, trailingslashit(dirname($this->GetPluginBasename())) . 'languages');
		} else {
			// < WordPress 2.7
			$currentLocale = get_locale();
			if(!empty($currentLocale)) {
				$moFile = dirname($this->g_info['PluginFile']) . '/languages/' . $this->g_info['ShortName'] . '-' . $currentLocale . '.mo';
				if(@file_exists($moFile) && is_readable($moFile)) load_textdomain($this->g_info['ShortName'], $moFile);
			}	
		}
		
		// Register plugin options page
		if ( method_exists($this, 'PluginOptionsPage') )
			$this->RegisterPluginOptionsPage();

	}




 	/**
	 * Register plugin options page
	 */
  	function RegisterPluginOptionsPage() {	// Add a menu item to the "Settings" area in the WP administration
		add_action('admin_menu', array(&$this, 'add_action_admin_menu_PluginMenuItem'));
	}
	function add_action_admin_menu_PluginMenuItem() {
		// Adding Options Page
#		add_options_page($this->g_info['Name'], $this->g_info['Name'], 9, basename($this->g_info['PluginFile']), array(&$this, 'PluginOptionsPage'));
		add_options_page($this->g_info['Name'], $this->g_info['Name'], 'manage_options', basename($this->g_info['PluginFile']), array(&$this, 'PluginOptionsPage'));
		
		// Add link "Settings" to the plugin in /wp-admin/plugins.php
		global $wp_version;
		if ( version_compare($wp_version, '2.7', '>=' ) ) {
			add_filter( 'plugin_action_links_' . plugin_basename($this->g_info['PluginFile']), array(&$this, 'add_filter_plugin_action_links') );
		}
	}
	function add_filter_plugin_action_links($links) {
		$settings_link = '<a href="'.$this->GetPluginOptionsURL().'">' . __('Settings') . '</a>';
		array_unshift($links, $settings_link);
		return $links;
	}

 	/**
	 * Standard Sidebar 
	 */
	function PrepareStandardSidebar() {
		$this->AddContentSidebar(__('Plugin',$this->g_info['ShortName']), '
			<ul>
				<li><a class="lhome" href="'. $this->g_info['PluginURI'] .  '">'.__('Plugin\'s Homepage',$this->g_info['ShortName']).'</a></li>
				<li><a class="lwp" href="'. $this->g_info['SupportURI'] .  '">'.__('WordPress Support',$this->g_info['ShortName']).'</a></li>
				<li><a class="lhome" href="http://sw-guide.de/wordpress/plugins/">'.__('Other Plugins I\'ve Written',$this->g_info['ShortName']).'</a></li>
			</ul>			
			');
		$this->AddContentSidebar(__('Do you like this plugin?',$this->g_info['ShortName']), '
			<p style="font-size:8pt;">'.__('I spend a lot of time on the plugins I\'ve written for WordPress. Any donation would be highly appreciated.',$this->g_info['ShortName']).'</p>
			<ul>
				<li><a class="lpaypal" href="http://sw-guide.de/donation/paypal/">'.__('Donate via PayPal',$this->g_info['ShortName']).'</a></li>
				<li><a class="lamazon" href="http://sw-guide.de/donation/amazon/">'.__('My Amazon Wish List',$this->g_info['ShortName']).'</a></li>
			</ul>			
			');
	}
 
 	/**
	 * Display warning message in the administration if plugin is not compatible 
	 */
	function WarningIfPluginNotCompatible() {
		global $wp_version;
		if( is_admin() && ($this->g_info['MinWP'] != '' ) ) {
				if ( ! version_compare($wp_version, $this->g_info['MinWP'], '>=' ) ) {
					add_action('admin_notices', array(&$this, 'add_action_admin_notices_DisplayWarningThatPluginNotCompatible'));
				}
			}
	}
	function add_action_admin_notices_DisplayWarningThatPluginNotCompatible() { 
		global $wp_version;
		echo '<div class="error"><p>'.__('The activated plugin',$this->g_info['ShortName']).' &laquo;' . $this->g_info['Name'] . ' ' . $this->g_info['Version'] 
			. '&raquo; '.__('is not compatible with your installed WordPress',$this->g_info['ShortName']) . $wp_version . '. ' .
			__('WordPress version',$this->g_info['ShortName']) . ' ' .$this->g_info['MinWP'] . ' '.__('or higher is required when using this plugin, please deactivate it.',$this->g_info['ShortName']).'</p></div>'; 
	}

	/**
	 * Initialize and (if required) save/update  the options.
	 */
	function IniOrUpdateOptions() {

		##########################
		# Delete the options?
		##########################
		$isdeleted = false;
		if ( isset($_POST['delete-settings-'.$this->g_info['ShortName']]) ) {
			delete_option($this->g_info['OptionName']);
			$isdeleted = true;
		}

		##########################
		# Initialize options
		##########################
		$this->g_opt = get_option($this->g_info['OptionName']);

		##########################
		# We check if we should set the default options
		##########################				 
		$ResetOpt = false;
		// We reset options if default options not exist
		if ( (!$ResetOpt) && ( !is_array($this->g_opt) || empty($this->g_opt) || $this->g_opt == false ) ) {
			$ResetOpt = true;
		}
		// We don't have a pluginversion option?
		if ( (!$ResetOpt) && ( $this->g_opt['pluginversion'] == '') ) {
				$ResetOpt = true;
		}
		// Check if we have updated from an old plugin version and if the version is older than the version limit ($this->g_info['UseOldOpt'])...
		if ( (!$ResetOpt) && ($this->g_opt['pluginversion'] != '') ) {
			if ( version_compare($this->g_opt['pluginversion'], $this->g_info['UseOldOpt'], '<' ) ) {
				$ResetOpt = true;
			}
		}
		
		##########################
		# Reset to default options
		##########################
		if ($ResetOpt) {
			// Options do not exist or have not yet been loaded or are old; so we set the default options
			$this->g_opt = $this->g_opt_default;
		}

		##########################
		# Copy old option values into new option values to not loose old options
		# This is only used if the OptionName changed!
		##########################
		if ( (!$isdeleted) && ( isset($this->g_opt['pluginversion']) && ($this->g_opt['pluginversion'] != $this->g_info['Version']) ) ) { 
			if (is_array($this->g_info['DeleteOldOpt'])) {
				$savedoptnameArr = $this->g_info['DeleteOldOpt'];	// array of old option names we want to delete later
			} else {
				$savedoptnameArr = array();
			}
			$savedoptnameArr[] = $this->g_info['OptionName']; // append current option name to array
			$savedoptnameArr = array_reverse($savedoptnameArr); // newest option first
			foreach ($savedoptnameArr as $loopval) {
				if ( get_option($loopval) != false ) {
					$opttemp = get_option($loopval);
					if ( (is_array($opttemp)) && (!empty($opttemp)) ) {
						foreach ($opttemp as $lpOptionName => $lpOptionValue) {
							if ($lpOptionName != 'pluginversion') {
								$this->g_opt[$lpOptionName] = $lpOptionValue;
							}
						}
						// We save the options here.
						$this->g_opt['pluginversion'] = $this->g_info['Version'];
						add_option($this->g_info['OptionName'], $this->g_opt); 	// adds option to table if it does not exist.
						update_option($this->g_info['OptionName'], $this->g_opt);	// we save option since add_option does nothing if option already exists
						break;	
					}
				}
			}
		}

		##########################
		# If new options added or old ones removed: 
		# Remove option entries or add the new ones
		##########################
		$newarray = array();
		foreach ( $this->g_opt_default as $lpOptionName => $lpOptionValue ) {
			if ( array_key_exists($lpOptionName, $this->g_opt) ) {
				$newarray[$lpOptionName] = $this->g_opt[$lpOptionName];
			} else {
				$newarray[$lpOptionName] = $this->g_opt_default[$lpOptionName];
			}
		}
		$this->g_opt = $newarray;




		##########################
		# Set the current plugin version
		##########################
		$this->g_opt['pluginversion'] = $this->g_info['Version'];


		##########################
		# Save/update the options if required
		##########################
		if ( isset($_POST['update-options-'.$this->g_info['ShortName']]) ) {
			// Build array of options and add the $_POST values
			foreach ($this->g_opt as $lpOptionName => $lpOptionValue) {
				if (method_exists($this, 'COPTSave')) {
					$optionsToBeSaved[$lpOptionName] = $this->COPTSave($lpOptionName);
				} else {
					$optionsToBeSaved[$lpOptionName] = $_POST[$lpOptionName];
				}
				// for plugin version we don't have a $_POST so update it manually
				$optionsToBeSaved['pluginversion'] = $this->g_info['Version'];
			}
			// Update Options in the database
			add_option($this->g_info['OptionName'], $optionsToBeSaved); 	// adds option to table if it does not exist.
			update_option($this->g_info['OptionName'], $optionsToBeSaved);	// we save option since add_option does nothing if option already exists 	
			// Update Options in the class
			$this->g_opt = $optionsToBeSaved;		
		}
	}


	/**
	 * Returns the plugin directory path, e.g. webseiten/wordpressblog/wp-content/plugins/my-great-plugin/
	 * @return string The path to the plugin directory
	 */
	function GetPluginPath() {
		$path = dirname($this->g_info['PluginFile']);
		return trailingslashit(str_replace("\\", "/", $path));
	}

	/**
	 * Returns the plugin directory URL, e.g. http://domain.tld/blog/wp-content/plugins/my-great-plugin/
	 * @return string The URL to the plugin directory
	 */
	function GetPluginURL() {
		// function plugins_url() exists since WP 2.6.0
		if (function_exists('plugins_url')) {
			return trailingslashit(plugins_url(basename(dirname($this->g_info['PluginFile']))));
		} else {
			// We do it manually; will not work if wp-content is renamed or redirected
			$result = str_replace("\\", '/', dirname($this->g_info['PluginFile']));
			$result = trailingslashit(get_bloginfo('wpurl')) . trailingslashit(substr($result, strpos($result,'wp-content/')));
			return $result;
		}
	}

	/**
	 * Returns the basename of a plugin (extracts the name of a plugin from its filename).
	 * Example: If your plugin file is located at /home/www/wp-content/plugins/myplugin/myplugin.php, 
	 * it will return 'myplugin/myplugin.php'
	 * @return string The URL to the plugin directory
	 */
	function GetPluginBasename() {
		return trailingslashit(plugin_basename($this->g_info['PluginFile'])); 
	}

	/**
	 * Add content to the main area
     * @param string $header Header
     * @param string $content Content
	 */
	function AddContentMain($header, $content) {
		$res = $this->g_contentmain;
		$res .= "\n\n\t\t" . '<dl>';
		$res .= "\n\t\t\t" . '<dt><h3>' . $header . '</h3></dt>'; 
		$res .= "\n\t\t\t" . '<dd>' . $content . '</dd>';
		$res .= "\n\n\t\t" . '</dl>';
		$this->g_contentmain = $res;
	}

	/**
	 * Add content to the sidebar
     * @param string $header Header
     * @param string $content Content
	 */
	function AddContentSidebar($header, $content) {
		$res = $this->g_contentsidebar;
		$res .= "\n\n\t\t" . '<dl>';
		$res .= "\n\t\t\t" . '<dt><h4>' . $header . '</h4></dt>'; 
		$res .= "\n\t\t\t" . '<dd>' . $content . '</dd>';
		$res .= "\n\n\t\t" . '</dl>';
		$this->g_contentsidebar = $res;
	}


	/**
	 * Replace white space with new line for displaying in text area
     * @param string $input
	 */
	function WhitespaceToLinebreak($input) {
		$output = str_replace(' ', "\n", $input);
		return $output;
	}

	/**
	 * Converts textarea content (separated by line break) to space separated string
     * since we want to store it like this in the database
     * @param string $input
	 */
	function LinebreakToWhitespace($input) {

		// Remove white spaces
		$input = str_replace(' ', '', $input);
	
		// Replace linebreaks with white space, considering both \n and \r
		$input = preg_replace("/\r|\n/s", ' ', $input);
	
		// Create result. We create an array and loop thru it but do not consider empty values. 
		$sourceArray = explode(' ', $input);
		$loopcount = 0;
		$result = '';
		foreach ($sourceArray as $loopval) {
	
			if ($loopval <> '') {
	
				// Create separator
				$sep = '';
				if ($loopcount >= 1) $sep = ' ';
				
				// result
				$result .= $sep . $loopval;
			
				$loopcount++;				
			}
		}
		return $result;
	
	}

	/**
	 * Returns the options page 
	 * @return string The options page
	 */
	function GetGeneratedOptionsPage() {

		// Security 
		if ( function_exists('current_user_can') && (!current_user_can('manage_options')) ) {
			wp_die('<p>'.__('You do not have permission to modify the options', $this->g_info['ShortName']).'</p>');
		}
		if ( isset($_POST['delete-settings-'.$this->g_info['ShortName']]) || isset($_POST['update-options-'.$this->g_info['ShortName']]) ) {
			check_admin_referer($this->g_info['Name']);
		}

		// Delete old options if we have any. We perform the deletion here as we only want to do it
		// in the admin area and not on every page load.
		if ( is_array($this->g_info['DeleteOldOpt']) || !empty($this->g_info['DeleteOldOpt']) ) {
			foreach ($this->g_info['DeleteOldOpt'] as $loopval) {
				if ($loopval != '') {
					if ( get_option($loopval) != false ) {
						delete_option($loopval);
					}
				}
			}
		}

		// Display message
		// We generate output here and not in IniOrUpdateOptions() as there the __()
		// does not show translated values.
		if ( isset($_POST['delete-settings-'.$this->g_info['ShortName']]) ) {
			echo '<div class="updated"><strong><p>' . __('Settings deleted/reset.',$this->g_info['ShortName']) . '</p></strong></div>';
		} elseif ( isset($_POST['update-options-'.$this->g_info['ShortName']]) ) {
			echo '<div class="updated"><strong><p>' . __('Settings saved.',$this->g_info['ShortName']) . '</p></strong></div>';
		}

		?>
		<style type="text/css">
			table#outer { width: 100%; border: 0 none; padding:0; margin:0;  }
			table#outer fieldset { border: 0 none; padding:0; margin:0; }
			table#outer td.left, table#outer td.right { vertical-align:top; }
			table#outer td.left {  padding: 0 8px 0 0; }
			table#outer td.right { padding: 0 0 0 8px; width: 210px; }
			td.right ul, td.right ul li { list-style: none; padding:0; margin:0; }
			td.right a { text-decoration:none; background-position:0px 60%; background-repeat:no-repeat; padding: 4px 0px 4px 22px; border: 0 none; display:block;}
			td.right a.lhome { background-image:url(<?php echo $this->GetBase64IconURL('sw-guide.png'); ?>); }
			td.right a.lpaypal { background-image:url(<?php echo $this->GetBase64IconURL('paypal.png'); ?>); }
			td.right a.lamazon { background-image:url(<?php echo $this->GetBase64IconURL('amazon.png'); ?>); }
			td.right a.lwp { background-image:url(<?php echo $this->GetBase64IconURL('wp.png'); ?>); }
			td.right ul li { padding:0; margin:0; }
			table#outer td dl { padding:0; margin: 10px 0 20px 0; background-color: white; border: 1px solid #dfdfdf; }
			table#outer td dl { -moz-border-radius: 5px; -khtml-border-radius: 5px; -webkit-border-radius: 5px; border-radius: 5px; }
			table#outer dl h3, table#outer td.right dl h4 { font-size: 10pt; font-weight: bold; margin:0; padding: 4px 10px 4px 10px; background: #dfdfdf url(<?php echo $this->GetBase64IconURL('bg-header-gray.png'); ?>) repeat-x left top; }
			table#outer td.left dl h4 { font-size: 10pt; font-weight: bold; margin:0; padding: 4px 0 4px 0;  }
			table#outer td.left dd { margin:0; padding: 10px 20px 10px 20px; }
			table#outer td.right dd { margin:0; padding: 5px 10px 5px 10px; }
			table#outer .info { color: #555; font-size: .85em; }
			table#outer p { padding:5px 0 5px 0; margin:0;}
			input.swg_warning:hover { background: #ce0000; color: #fff; }
			table#outer .swgfooter {text-align: center; font-size: .85em;}
			table#outer .swgfooter a, table#outer .swgfooter a:link { text-decoration:none; }
			table#outer td small { color: #555; font-size: .85em; }
			table#outer hr { border: none 0; border-top: 1px solid #BBBBBB; height: 1px; }
			table#outer ul { list-style:none; }
			table#outer ul.mybullet { list-style-type:disc; padding-left: 20px; }
			.swginfo { font-size:85%; line-height: 115%; }
		</style>

		<div class="wrap">

		<h2><?php echo __('Plugin Settings', $this->g_info['ShortName']) . ': ' . $this->g_info['Name'] . ' ' . $this->g_info['Version']; ?></h2>

		<table id="outer"><tr><td class="left">
		<!-- *********************** BEGIN: Main Content ******************* -->
		<form name="form1" method="post" action="<?php echo $this->GetPluginOptionsURL() ?>">
		<?php wp_nonce_field($this->g_info['Name']); ?>

		<fieldset class="options">

		<?php echo $this->g_contentmain; ?>

		<div class="submit">
			<?php wp_nonce_field($this->g_info['Name']) ?>
			<input type="submit" name="update-options-<?php echo $this->g_info['ShortName']; ?>" class="button-primary" value="<?php _e('Save Changes',$this->g_info['ShortName']) ?>" />
			<input type="submit" name="delete-settings-<?php echo $this->g_info['ShortName']; ?>" onclick='return confirm("<?php _e('Do you really want to delete/reset the plugin settings?',$this->g_info['ShortName']); ?>");' class="swg_warning" value="<?php _e('Delete/Reset Settings',$this->g_info['ShortName']) ?>" />
		</div>

		</fieldset>
		</form>
		<!-- *********************** END: Main Content ********************* -->
		<p class="swgfooter"><a style="" href="<?php echo $this->g_info['PluginURI']; ?>"><?php echo $this->g_info['Name'] . ' ' . $this->g_info['Version'] . '</a> &copy; Copyright ' . $this->g_info['CopyrightYear']; ?> <a href="<?php echo $this->g_info['AuthorURI']; ?>"><?php echo $this->g_info['Author']; ?></a></p>
		</td> <!-- [left] -->

		<td class="right">
		<!-- *********************** BEGIN: Sidebar ************************ -->		

		<?php echo $this->g_contentsidebar; ?>

		<!-- *********************** END: Sidebar ************************ -->
		</td> <!-- [right] -->

		
		</tr></table> <!-- [outer] -->


		</div> <!-- [wrap] -->

	<?php
	}


	/**
	 * Returns the plugin information. Uses the WP API to get the meta data from the top of the plugin file (comment)
     * @param string $info 'Name', 'Title', 'PluginURI', 'Description', 'Author', 'AuthorURI', 'Version', 'TextDomain', 'DomainPath'
	 * @return string array; to get for example the version use $returnvalue['Version'] 
	 */
	function GetPluginData($info) {
		if (empty($this->g_data)) {
			if(!function_exists('get_plugin_data')) {
				if(file_exists(ABSPATH . 'wp-admin/includes/plugin.php')) 
					require_once(ABSPATH . 'wp-admin/includes/plugin.php');
				else return "0.ERROR";
			}
			$this->g_data = get_plugin_data($this->g_info['PluginFile']);
		}
		return $this->g_data[$info];
	}

	/**
	 * Returns the option URL of the plugin, e.g. http://testblog.com/wp-admin/options-general.php?page=myplugin.php
	 * @return string 
	 */
	function GetPluginOptionsURL() {
		if (function_exists('admin_url')) {	// since WP 2.6.0
			$adminurl = trailingslashit(admin_url());			
		} else {
			$adminurl = trailingslashit(get_settings('siteurl')).'wp-admin/';
		}
			return $adminurl.'options-general.php'.'?page=' . basename($this->g_info['PluginFile']);		
	}

	/**
	 * Get Icon URL 
	 */
	function GetBase64IconURL($resourceID) {
		return trailingslashit(get_bloginfo('siteurl')) . '?resource=' . $resourceID;
	}

	/**
	 * Initialize our Base64 Icons 
	 */
	function IniBase64Icons() {
		if( isset($_GET['resource']) && !empty($_GET['resource'])) {
			# base64 encoding performed by base64img.php from http://php.holtsmark.no 
			$resources = array(
				'paypal.png' =>
					'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAFfKj/FAAAAB3RJTUUH1wYQEhELx'.
					'x+pjgAAAAlwSFlzAAALEgAACxIB0t1+/AAAAARnQU1BAACxjwv8YQUAAAAnUExURZ'.
					'wMDOfv787W3tbe55y1xgAxY/f39////73O1oSctXOUrZSlva29zmehiRYAAAABdFJ'.
					'OUwBA5thmAAAAdElEQVR42m1O0RLAIAgyG1Gr///eYbXrbjceFAkxM4GzwAyse5qg'.
					'qEcB5gyhB+kESwi8cYfgnu2DMEcfFDDNwCakR06T4uq5cK0n9xOQPXByE3JEpYG2h'.
					'KYgHdnxZgUeglxjCV1vihx4N1BluM6JC+8v//EAp9gC4zRZsZgAAAAASUVORK5CYI'.
					'I=',
				'amazon.png' => 
					'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAFfKj/FAAAAB3RJTUUH1wYQESUI5'.
					'3q1mgAAAAlwSFlzAAALEgAACxIB0t1+/AAAAARnQU1BAACxjwv8YQUAAABgUExURe'.
					'rBhcOLOqB1OX1gOE5DNjc1NYKBgfGnPNqZO4hnOEM8NWZSN86SO1pKNnFZN7eDOuW'.
					'gPJRuOVBOTpuamo+NjURCQubm5v///9rZ2WloaKinp11bW3Z0dPPy8srKyrSzs09b'.
					'naIAAACiSURBVHjaTY3ZFoMgDAUDchuruFIN1qX//5eNYJc85EyG5EIBBNACEibsi'.
					'mi5UaUURJtI5wm+KwgSJflVkOFscBUTM1vgrmacThfomGVLO9MhIYFsF8wyx6Jnl8'.
					'8HUxEay+wYmlM6oNKcNYrIC58iHMcIyQlZRNmf/2LRQUX8bYwh3PCYWmOGrueargd'.
					'XGO5d6UGm5FSmBqzXEzK2cN9PcXsD9XsKTHawijcAAAAASUVORK5CYII=',
				'sw-guide.png' => 
					'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAFfKj/FAAAAB3RJTUUH1wYQEhckO'.
					'pQzUQAAAAlwSFlzAAALEgAACxIB0t1+/AAAAARnQU1BAACxjwv8YQUAAABFUExURZ'.
					'wMDN7e3tbW1oSEhOfn54yMjDk5OTExMWtra7W1te/v72NjY0pKSs7OzpycnHNzc8b'.
					'Gxr29vff3962trVJSUqWlpUJCQkXEfukAAAABdFJOUwBA5thmAAAAlUlEQVR42k2O'.
					'WxLDIAwD5QfQEEKDob3/UevAtM1+LRoNFsDgCGbEAE7ZwBoe/maCndaRyylQTQK2S'.
					'XPpXjTvq2osRUCyAPEEaKvM6LWFKcFGnCI1Hc+WXVRFk07ROGVBoNpvVAJ3Pzjee5'.
					'7fdh9dfcUItO5UD8T6aVs69jheJlegFyFmPlj/wZZC3ssKSH+wB9/9C8IH45EIdeu'.
					'A/YIAAAAASUVORK5CYII=',
				'wp.png' => 
					'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAFfKj/FAAAAB3RJTUUH1wYQEiwG0'.
					'0adjQAAAAlwSFlzAAALEgAACxIB0t1+/AAAAARnQU1BAACxjwv8YQUAAABOUExURZ'.
					'wMDN7n93ut1kKExjFjnHul1tbn75S93jFrnP///1qUxnOl1sbe71KMxjFrpWOUzjl'.
					'7tYy13q3G5+fv95y93muczu/39zl7vff3//f//9Se9dEAAAABdFJOUwBA5thmAAAA'.
					's0lEQVR42iWPUZLDIAxDRZFNTMCllJD0/hddktWPRp6x5QcQmyIA1qG1GuBUIArwj'.
					'SRITkiylXNxHjtweqfRFHJ86MIBrBuW0nIIo96+H/SSAb5Zm14KnZTm7cQVc1XSMT'.
					'jr7IdAVPm+G5GS6YZHaUv6M132RBF1PopTXiuPYplcmxzWk2C72CfZTNaU09GCM3T'.
					'Ww9porieUwZt9yP6tHm5K5L2Uun6xsuf/WoTXwo7yQPwBXo8H/8TEoKYAAAAASUVO'.
					'RK5CYII=',
				'bg-header-gray.png' =>
					'iVBORw0KGgoAAAANSUhEUgAAAAUAAAAfCAIAAACgQJBPAAAAA3NCSVQICAjb4U/gA'.
					'AAACXBIWXMAAAsSAAALEgHS3X78AAAAIXRFWHRTb2Z0d2FyZQBNYWNyb21lZGlhIE'.
					'ZpcmV3b3JrcyA0LjDqJid1AAAAFnRFWHRDcmVhdGlvbiBUaW1lADEwLzI0LzA4KQ6'.
					'r+wAAAClJREFUeJxjfPv2LQMSYPn//z8yn4kBFaDzqa0eXZ5U9QMtT6l5tFYPADsX'.
					'LPcJwrwLAAAAAElFTkSuQmCC',
			); // $resources = array

			if(array_key_exists($_GET['resource'],$resources)) {
		
				$content = base64_decode($resources[ $_GET['resource'] ]);
		
				$lastMod = filemtime(__FILE__);
				$client = ( isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false );
				// Checking if the client is validating his cache and if it is current.
				if (isset($client) && (strtotime($client) == $lastMod)) {
					// Client's cache IS current, so we just respond '304 Not Modified'.
					header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastMod).' GMT', true, 304);
					exit;
				} else {
					// Image not cached or cache outdated, we respond '200 OK' and output the image.
					header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastMod).' GMT', true, 200);
					header('Content-Length: '.strlen($content));
					header('Content-Type: image/' . substr(strrchr($_GET['resource'], '.'), 1) );
					echo $content;
					exit;
				}	
			}
		}
	} // function IniBase64Icons




} // class PluginOptions


?>