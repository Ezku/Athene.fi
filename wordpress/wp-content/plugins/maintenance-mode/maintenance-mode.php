<?php
/*
Plugin Name: Maintenance Mode
Plugin URI: http://sw-guide.de/wordpress/plugins/maintenance-mode/
Description: Adds a splash page to your blog that lets visitors know your blog is down for maintenance. Logged in administrators get full access to the blog including the front-end. Navigate to <a href="options-general.php?page=maintenance-mode.php">Settings &rarr; Maintenance Mode</a> to get started.
Version: 5.4
Author: Michael Wöhrer
Author URI: http://sw-guide.de/
*/

/*
    ----------------------------------------------------------------------------
   	      ____________________________________________________
         |                                                    |
         |                 Maintenance Mode                   |
         |____________________________________________________|

	                  Copyright © Michael Wöhrer 
	                    <http://sw-guide.de>
                (michael dot woehrer at gmail dot com)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License <http://www.gnu.org/licenses/> for 
	more details.

	----------------------------------------------------------------------------
*/
require_once ( dirname(__FILE__) . '/inc.swg-plugin-framework.php');

# commented out since plugin version 5.3 -- http://radiok.info/blog/the-case-of-maintenance-mode/ 
#if (!function_exists('wp_get_current_user')) require (ABSPATH . WPINC . '/pluggable.php'); // For current_user_can() -> "wp_get_current_user() in wp-includes\capabilities.php on line 969". We need to include now since it is by default included AFTER plugins are being loaded.

class MaintenanceMode extends MaintenanceMode_SWGPluginFramework {

	/**
	 * Apply Maintenance Mode
	 */
	function ApplyMaintenanceMode() {

		if (substr($this->g_opt['mamo_activate'], 0, 2)=='on') {

			############
			# 1. Display information on login/logout screen when Maintenance Mode is activated.
			############
			$msg = '<div id="login_error"><p>'.__('The Maintenance Mode is active.',$this->g_info['ShortName']).'</p></div>';
			add_filter( 'login_message', create_function( '', 'return \'' . $msg . '\';' ));
			############
			# 2. Display information in administration when Maintenance Mode is activated.
			############
			if (is_admin() ) {
				$currbasename = (isset($_GET['page'])) ? $_GET['page'] : ''; 
				if ($currbasename != basename($this->g_info['PluginFile'])) {
					// Display link only if user is administrator / can manage options.
					$link_to_mamo_opt = '';
					if ( current_user_can('manage_options') ) {
						$link_to_mamo_opt = __("Please don't forget to", $this->g_info['ShortName']) . ' <a href="admin.php?page=' . basename($this->g_info['PluginFile']) . '">' . __('deactivate', $this->g_info['ShortName']) . '</a> ' . __('it as soon as you are done.', $this->g_info['ShortName']);
					}
					$msg = '<div class="error"><p>' . __("The Maintenance Mode is active.",$this->g_info['ShortName']) . ' ' . $link_to_mamo_opt . '</p></div>';
					add_action('admin_notices', $c = create_function('', 'echo "' . addcslashes($msg,'"') . '";')); // We use addcslashes otherwise it causes a parse error when the $msg contains a single quote
				}
			}

			############
			# 3. Never display maintenance mode in these cases, neither in frontend nor in backend
			############
			if( strstr($_SERVER['PHP_SELF'],    'wp-login.php') 
				|| strstr($_SERVER['PHP_SELF'], 'async-upload.php') // Otherwise media uploader does not work 
				|| strstr(htmlspecialchars($_SERVER['REQUEST_URI']), '/plugins/') 		// So that currently enabled plugins work while in maintenance mode.
				|| strstr($_SERVER['PHP_SELF'], 'upgrade.php')
				|| $this->is_excluded_url()
			){ 
				return; // exit function ApplyMaintenanceMode()
			}

			############
			# 4. Feeds
			############
			if( strstr(htmlspecialchars($_SERVER['REQUEST_URI']), '/feed/') || strstr(htmlspecialchars($_SERVER['REQUEST_URI']), 'feed=') ) {
				if ($this->g_opt['mamo_include_feeds'] == '1') {
					# Display feeds
					return; // exit function ApplyMaintenanceMode()
				} else {
					# Don't display feeds and apply HTTP header
					nocache_headers(); // Sets the headers to prevent caching for the different browsers
					$this->http_header_unavailable(); 
					exit();
				}
			}

			############
			# 5. Trackbacks
			############
			if( strstr(htmlspecialchars($_SERVER['REQUEST_URI']), '/trackback/') || strstr($_SERVER['PHP_SELF'], 'wp-trackback.php') ) {
				if ($this->g_opt['mamo_include_trackbacks'] == '1') { 				
					# Display trackbacks
					return; // exit function ApplyMaintenanceMode()
				} else {
					# Don't display trackbacks and apply HTTP header
					nocache_headers(); // Sets the headers to prevent caching for the different browsers
					$this->http_header_unavailable(); 
					exit();
				}
			}

			############
			# 5. xmlrpc.php
			############
			if( strstr($_SERVER['PHP_SELF'], 'xmlrpc.php') ) {
				if ($this->g_opt['mamo_include_xmlrpc'] == '1') { 				
					# Allow XML RPC publishing
					return; // exit function ApplyMaintenanceMode()
				} else {
					# Don't allow XML RPC publishing
					$this->http_header_unavailable(); 
					exit();
				}
			}

			############
			# 6. Display maintenance mode splash page
			############
			if ( is_admin() || strstr(htmlspecialchars($_SERVER['REQUEST_URI']), '/wp-admin/') ) {
				///////
				// Access to backend ?
				///////
				# Users in some blogs use <blog.dlt/wp-admin/> to login to WP, but this will cause to display
				# the splash page instead of wp-login.php. So if the user is not logged in to WP, we redirect
				# to wp-login.php
				if ( ! is_user_logged_in() ) {
					auth_redirect(); // from pluggable.php: Checks if a user is logged in, if not redirects them to the login page
					}

				if ( $this->current_user_can_access_on_maintenance('backend') ) {
					# Yes, access granted to backend
					return; // exit function ApplyMaintenanceMode()
				} else {
					# No access to backend, display splash page
					$status = 'noaccesstobackend';	// Some status to be used later in theme file
					$this->display_splash_page();
				}
			
			} else {
				///////
				// Access to front-end ?
				///////			
				if( $this->current_user_can_access_on_maintenance('frontend') ) {
					######
					# Yes, access granted to frontend
					######
					return; // exit function ApplyMaintenanceMode()
				} else {
					######
					# No access to frontend, display splash page
					######
					$this->display_splash_page();
				}
			}

		}	// if ($this->g_opt['mamo_activate'] == 'on')
	}

	/**
	 * Displays the splash page
	 */	 
	function display_splash_page() {
		#########
		# Get path to splash page
		#########
		if ($this->g_opt['mamo_theme'] == '503') {
			$path503file = get_stylesheet_directory() . '/503.php'; // No longer use get_template_directory() to support child themes.
		} else {
			$path503file = dirname(__FILE__) . '/maintenance-mode_theme_' . $this->g_opt['mamo_theme'] . '.php';
		}
		if (file_exists($path503file)=== false) {
			$path503file = dirname(__FILE__) . '/maintenance-mode_theme_default.php';
		}

		#########
		# Consider the Super Cache plugin
		#########
		if( defined( 'WPCACHEHOME' ) ) {
			// Solves issue of white page output with Super Cache plugin version 0.9.9.6.
			// Did not occur when removing <html> and </html> tag in splash page source, so weird problem.
			ob_end_clean();
		}

		#########
		# Header
		#########
		nocache_headers(); // Sets the headers to prevent caching for the different browsers 
		if ($this->g_opt['mamo_503_splashpage'] == '1') $this->http_header_unavailable(); // Apply HTTP header

		#########
		# Output
		#########
		include($path503file); // Display splash page 

		#########
		# Bye bye
		#########
	    exit();
	}

	function is_excluded_url() {
		$urlarray = $this->g_opt['mamo_excludedpaths'];
		$urlarray = preg_replace("/\r|\n/s", ' ', $urlarray);	// needed, otherwise explode doesn't work here
		$urlarray = explode(' ', $urlarray);		
		$oururl = 'http://' . $_SERVER['HTTP_HOST'] . htmlspecialchars($_SERVER['REQUEST_URI']);
		foreach ($urlarray as $expath) {
			if (!empty($expath)) {
				// Strip whitespace
				$expath = str_replace(' ', '', $expath);
				// Check if it is a matching url
				if (strpos($oururl, $expath) !== false)	return true;
				// Check if we are on home. Note that is_home() or is_front_page() are not working here for some reasons.
				if ( (strtoupper($expath) == '[HOME]') && ( trailingslashit(get_bloginfo('url')) == trailingslashit($oururl) ) )	return true;  
			}
		}
		return false;
	}

	/**
	 * Checks if current user can access to front-end or back-end on maintenance
	 * @param $where strg	either 'frontend' or 'backend'
	 * returns FALSE or TRUE	 	 
	 */
	function current_user_can_access_on_maintenance($where) {
		if ($where == 'frontend') {
			$optval = $this->g_opt['mamo_role_frontend'];
		} elseif ($where == 'backend') {
			$optval = $this->g_opt['mamo_role_backend'];
		} else {
			return false;
		}

		if ($optval == 'no-access') { return false; }
		elseif ( $optval == 'manage_options' && current_user_can('manage_options') ) { return true; }
		elseif ( $optval == 'manage_categories' && current_user_can('manage_categories') ) { return true; }
		elseif ( $optval == 'publish_posts' && current_user_can('publish_posts') ) { return true;	}
		elseif ( $optval == 'edit_posts' && current_user_can('edit_posts') ) { return true; }
		elseif ( $optval == 'read' && current_user_can('read') ) { return true; }
		else { return false; }
	}


	/**
	 * Plugin Options
	 */
	function PluginOptionsPage() {

		//Add options
		if (substr($this->g_opt['mamo_activate'], 0, 2)=='on') {
			$calctimes_arr = $this->calculate_times();
			$backtime_days = $calctimes_arr['calc_days'];
			$backtime_hours = $calctimes_arr['calc_hours'];
			$backtime_mins = $calctimes_arr['calc_mins'];
		} else {
			$backtime_days = $this->COPTHTML('mamo_backtime_days');
			$backtime_hours = $this->COPTHTML('mamo_backtime_hours');
			$backtime_mins = $this->COPTHTML('mamo_backtime_mins');
		}
		$this->AddContentMain(__('Activate/Deactivate Maintenance Mode',$this->g_info['ShortName']), "
			<table border='0'><tr>
				<td width='130'>
					<p style='font-weight: bold; line-height: 2em;'>
						<input id='radioa1' type='radio' name='mamo_activate' value='on_".current_time('timestamp')."' " . (substr($this->COPTHTML('mamo_activate'), 0, 2)=='on'?'checked="checked"':'') . " />
						<label for='radioa1'>".__('Activated',$this->g_info['ShortName'])."</label>
						<br />	
						<input id='radioa2' type='radio' name='mamo_activate' value='off' " . (substr($this->COPTHTML('mamo_activate'), 0, 2)!='on'?'checked="checked"':'') . " />
						<label for='radioa2'>".__('Deactivated',$this->g_info['ShortName'])."</label>
					</p>				
				</td>
				<td>
					<div class='submit' style='text-align: left;'>
						<input type='submit' name='update-options-".$this->g_info['ShortName']. "' class='button-primary' value='" . __('Save',$this->g_info['ShortName']) . "' />
					</div>			
				</td>
				<td style='padding-left: 20px'>
					".__('Backtime',$this->g_info['ShortName']).": 
						<input style='text-align:right;' name='mamo_backtime_days' type='text' id='mamo_backtime_days' value='" . $backtime_days . "' size='4' maxlength='5' /> <label for='mamo_backtime_days'>" . __('days',$this->g_info['ShortName']) . "</label>, 
						<input style='text-align:right;' name='mamo_backtime_hours' type='text' id='mamo_backtime_hours' value='" . $backtime_hours . "' size='2' maxlength='2'  /> <label for='mamo_backtime_hours'>" . __('hours',$this->g_info['ShortName']) . "</label>, 
						<input style='text-align:right;' name='mamo_backtime_mins' type='text' id='mamo_backtime_mins' value='" . $backtime_mins . "' size='2' maxlength='2' /> <label for='mamo_backtime_mins'>" . __('mins',$this->g_info['ShortName']) . "</label>
					<br /><div class='swginfo'>".__('Please enter the estimated time you will need for the maintenance. The placeholders <strong>[date]</strong>, <strong>[time]</strong>, <strong>[days]</strong>, <strong>[hours]</strong>, and <strong>[minutes]</strong> are calculated based on these fields.',$this->g_info['ShortName'])
					. '<br />' . __('Please note that the maintenance mode will never be deactivated automatically.',$this->g_info['ShortName'])
					. "</div>			 
				</td>
			</tr></table>
			");
	
		$this->AddContentMain(__('Message',$this->g_info['ShortName']), "
			<table width='100%' cellspacing='2' cellpadding='5' class='editform'> 
			<tr valign='center'> 
				<th align=left width='150px' scope='row'><label for='mamo_pagetitle'>".__('Title',$this->g_info['ShortName']).':</label></th>
				<td width="100%"><input style="font-weight:bold;" name="mamo_pagetitle" type="text" id="mamo_pagetitle" value="' . htmlspecialchars(stripslashes($this->g_opt['mamo_pagetitle'])) . '" size="60" /></td>
			</tr>
			<tr valign="top">'." 
				<th align=left width='150px' scope='row'><label for='mamo_pagemsg'>".__('Message',$this->g_info['ShortName']).":</label></th> 
				<td width='100%'><textarea style='font-size: 90%; width:95%;' name='mamo_pagemsg' id='mamo_pagemsg' rows='9' >" . $this->COPTHTML('mamo_pagemsg') . "</textarea>
				<p class='swginfo'>".__('Use HTML only, no PHP allowed. You can use the following placeholders: <strong>[blogurl]</strong> (your blog URL), <strong>[blogtitle]</strong> (title of your blog)',$this->g_info['ShortName']).'</p>
				<p class="swginfo">'
					. __('Furthermore, the following placeholders are calculated based on the value you entered or changed above in the field «Backtime».',$this->g_info['ShortName'])
					. '<br />&nbsp;&nbsp;-'
					. __('<strong>[date]</strong> and <strong>[time]</strong> (when maintenance is supposed to be finished, e.g. «05/12/2010» / «3:45PM»)',$this->g_info['ShortName'])
					. '<br />&nbsp;&nbsp;-' 
					. __('<strong>[days]</strong>, <strong>[hours]</strong>, and <strong>[minutes]</strong>: (number of days/hours/mins until the maintenance is supposed to be finished)',$this->g_info['ShortName'])
					. '<br /><br />'
					. __('Placeholder <strong>[until]</strong>: You can setup this placeholder in the following fields. The reason for these fields is to display a different message when the «Backtime» is exceeded to not confuse your visitors.',$this->g_info['ShortName'])
				.'<p style="margin-left: 10px;">
					<label for="mamo_placeholder_until">'.__('Placeholder [until]',$this->g_info['ShortName']).':</label>
					<input name="mamo_placeholder_until" type="text" id="mamo_placeholder_until" value="' . htmlspecialchars(stripslashes($this->g_opt['mamo_placeholder_until'])) . '" size="70" />
					<br />
					<label for="mamo_placeholder_until_exc">'.__('Placeholder [until] if time is exceeded',$this->g_info['ShortName']).':</label>
					<input name="mamo_placeholder_until_exc" type="text" id="mamo_placeholder_until_exc" value="' . htmlspecialchars(stripslashes($this->g_opt['mamo_placeholder_until_exc'])) . '" size="50" />
				</p>
				</td>
			</tr>
			</table>
			');
	
		$this->AddContentMain(__('Splash Page Theme',$this->g_info['ShortName']), "
			<p>
				<select name='mamo_theme'>
					<option value='default'" . ($this->COPTHTML('mamo_theme') == 'default' ? ' selected="selected"' : '') . ">" . __('Default Theme',$this->g_info['ShortName']) . "</option>
					<option value='wordpress'" . ($this->COPTHTML('mamo_theme') == 'wordpress' ? ' selected="selected"' : '') . ">" . __('WordPress Login Theme',$this->g_info['ShortName']) . "</option>
					<option value='503'" . ($this->COPTHTML('mamo_theme') == '503' ? ' selected="selected"' : '') . ">" . __('Use 503.php from theme folder',$this->g_info['ShortName']) . "</option>

				</select>
			<p class='swginfo'>".__('Select the theme for the maintenance mode splash page (check out the screenshots in the plugin directory for a preview).',$this->g_info['ShortName']).'<br />'.
			__('If you select &laquo;<em>Use 503.php from theme folder</em>&raquo;, the plugin will use the file \'503.php\' from the current theme directory for the splash page. If there is no \'503.php\', it will use the default theme.',$this->g_info['ShortName']).' '.
			__('So this option will help using a customized splash page without the fear of losing this page when updating the plugin.',$this->g_info['ShortName']).' '.
			__('Please note that you can\'t use WordPress theme functions (e.g. <em>get_sidebar()</em>, etc.) in the \'503.php\'.',$this->g_info['ShortName'])
			.'<br /><br />'
			. __('You have designed a beautiful splash page theme you want to share? Please go ahead and <a href="http://sw-guide.de/kontakt/">drop me a line</a>.',$this->g_info['ShortName'])
			."</p>
			");

		$this->AddContentMain(__('Access to blog front-end and administration (back-end)',$this->g_info['ShortName']),
			'<p class="swginfo">'
				. __('By default, only logged in administrators (more exactly: users with the',$this->g_info['ShortName'])
				.' <a href="http://codex.wordpress.org/Roles_and_Capabilities">' . __('capability',$this->g_info['ShortName']) . '</a> '
				. __('«manage_options») do have full access to the blog\'s front-end and will not see any splash page when the maintenance mode is activated. You can change this here:',$this->g_info['ShortName'])
			.'</p>

			<strong>'.__('Access to blog front-end with capability (role)',$this->g_info['ShortName']).":</strong>
				<select name='mamo_role_frontend'>
					<option value='no-access'" . ($this->COPTHTML('mamo_role_frontend') == 'no-access' ? ' selected="selected"' : '') . ">" . __('- No Access - (always display splash page)',$this->g_info['ShortName']) . "</option>
					<option value='manage_options'" . ($this->COPTHTML('mamo_role_frontend') == 'manage_options' ? ' selected="selected"' : '') . ">" . __('«manage_options» (Administrator)',$this->g_info['ShortName']) . "</option>
					<option value='manage_categories'" . ($this->COPTHTML('mamo_role_frontend') == 'manage_categories' ? ' selected="selected"' : '') . ">" . __('«manage_categories» (Editor)',$this->g_info['ShortName']) . "</option>
					<option value='publish_posts'" . ($this->COPTHTML('mamo_role_frontend') == 'publish_posts' ? ' selected="selected"' : '') . ">" . __('«publish_posts» (Author)',$this->g_info['ShortName']) . "</option>
					<option value='edit_posts'" . ($this->COPTHTML('mamo_role_frontend') == 'edit_posts' ? ' selected="selected"' : '') . ">" . __('«edit_posts» (Contributor)',$this->g_info['ShortName']) . "</option>
					<option value='read'" . ($this->COPTHTML('mamo_role_frontend') == 'read' ? ' selected="selected"' : '') . ">" . __('«read» (Subscriber)',$this->g_info['ShortName']) . "</option>
				</select>" . '
			<hr style="margin-top: 15px; margin-bottom: 15px;" />
			<p class="swginfo">'
				. __('By default, anyone who is logged in to the blog (more exactly: users with the',$this->g_info['ShortName'])
				.' <a href="http://codex.wordpress.org/Roles_and_Capabilities">' . __('capability',$this->g_info['ShortName']) . '</a> '
				. __('«read») does have access to the WordPress administration (back-end) when the maintenance mode is activated. You can restrict this here:',$this->g_info['ShortName'])
			.'</p>

			<strong>'.__('Access to administration/back-end with capability (role)',$this->g_info['ShortName']).":</strong>
				<select name='mamo_role_backend'>
					<option value='manage_options'" . ($this->COPTHTML('mamo_role_backend') == 'manage_options' ? ' selected="selected"' : '') . ">" . __('«manage_options» (Administrator)',$this->g_info['ShortName']) . "</option>
					<option value='manage_categories'" . ($this->COPTHTML('mamo_role_backend') == 'manage_categories' ? ' selected="selected"' : '') . ">" . __('«manage_categories» (Editor)',$this->g_info['ShortName']) . "</option>
					<option value='publish_posts'" . ($this->COPTHTML('mamo_role_backend') == 'publish_posts' ? ' selected="selected"' : '') . ">" . __('«publish_posts» (Author)',$this->g_info['ShortName']) . "</option>
					<option value='edit_posts'" . ($this->COPTHTML('mamo_role_backend') == 'edit_posts' ? ' selected="selected"' : '') . ">" . __('«edit_posts» (Contributor)',$this->g_info['ShortName']) . "</option>
					<option value='read'" . ($this->COPTHTML('mamo_role_backend') == 'read' ? ' selected="selected"' : '') . ">" . __('«read» (Subscriber)',$this->g_info['ShortName']) . '</option>
				</select>
				');

		$this->AddContentMain(__('Paths to be still accessable',$this->g_info['ShortName']), "
			<p class='swginfo'>
				".__('Enter paths that shall be excluded and still be accessable. Separate multiple paths with line breaks.<br />Example: If you want to exclude <em>http://site.com/about/</em>, then enter <em>/about/</em>.<br />Hint: If you want to exclude the home page, enter <em>[HOME]</em>.',$this->g_info['ShortName'])."
			</p>
			<textarea style='width:95%;' name='mamo_excludedpaths' id='mamo_excludedpaths' rows='2' >" . $this->COPTHTML('mamo_excludedpaths') . "</textarea>
			<hr />
			<p>
				<input name='mamo_include_feeds' type='checkbox' id='mamo_include_feeds' value='1' " . ($this->COPTHTML('mamo_include_feeds')=='1'?'checked="checked"':'') . " /> 
				<label for='mamo_include_feeds'>".__('Enable feeds',$this->g_info['ShortName'])."</label>
			</p>
			<p>
				<input name='mamo_include_trackbacks' type='checkbox' id='mamo_include_trackbacks' value='1' " . ($this->COPTHTML('mamo_include_trackbacks')=='1'?'checked="checked"':'') . " /> 
				<label for='mamo_include_trackbacks'>".__('Enable trackbacks',$this->g_info['ShortName'])."</label>
			</p>
			<p>
				<input name='mamo_include_xmlrpc' type='checkbox' id='mamo_include_xmlrpc' value='1' " . ($this->COPTHTML('mamo_include_xmlrpc')=='1'?'checked="checked"':'') . " /> 
				<label for='mamo_include_xmlrpc'>".__('Enable XML-RPC publishing',$this->g_info['ShortName'])."</label>
			</p>
			");


		$this->AddContentMain(__('Miscellaneous',$this->g_info['ShortName']), "
			<p>
				<input name='mamo_503_splashpage' type='checkbox' id='mamo_503_splashpage' value='1' " . ($this->COPTHTML('mamo_503_splashpage')=='1'?'checked="checked"':'') . " /> 
				<label for='mamo_503_splashpage'>".__('Apply HTTP header \'503 Service Unavailable\' and \'Retry-After &lt;backtime&gt;\' to Maintenance Mode splash page',$this->g_info['ShortName'])."</label>
			</p>
			");

		// Sidebar, we can also add individual items...
		$this->PrepareStandardSidebar();
		
		$this->GetGeneratedOptionsPage();
	
	
	}
	
	/**
	 * Apply HTTP header
	 */
	function http_header_unavailable() {
	
	   	header('HTTP/1.0 503 Service Unavailable');
	
		$calctimes_arr = $this->calculate_times();
		$backtime = intval($calctimes_arr['minutes_total']);
		if ( $backtime > 1 ) {
	    	# Apply return-after only if value > 0. Also, intval returns 0 on failure; empty arrays and objects return 0, non-empty arrays and objects return 1
			header('Retry-After: ' . $backtime * 60 );
		}
	}

	/**
	 * Convert option prior to save ("COPTSave"). 
	 * !!!! This function is used by the framework class !!!!
	 */
	function COPTSave($optname) {

		switch ($optname) {
			case 'mamo_excludedpaths':
				return $this->LinebreakToWhitespace($_POST[$optname]);

 			case ($optname=='mamo_backtime_days' || $optname=='mamo_backtime_hours' || $optname=='mamo_backtime_mins'):
				// ***********************************
				// Convert input days/hours/minutes to avoid values like: 10 days, 28 hours, 800 seconds
				// ***********************************
				# build array
				$times_post = array();
				$times_post['mamo_backtime_days'] = $_POST['mamo_backtime_days'];
				$times_post['mamo_backtime_hours'] = $_POST['mamo_backtime_hours'];
				$times_post['mamo_backtime_mins'] = $_POST['mamo_backtime_mins'];
				# clean values
				foreach ($times_post as $loop_name => $loop_val) {
					$result = $loop_val;
					$result = preg_replace('/[^0-9]/','',$result); // Strip all chars except numbers 
					if (($result == '') or (intval($result) === false)) $result = '0';
					$times_post[$loop_name] = $result;
				}
				# calculate number of minutes
				$res_minutes = 0;
				$res_minutes = $res_minutes + $times_post['mamo_backtime_days'] * (24 * 60);
				$res_minutes = $res_minutes + $times_post['mamo_backtime_hours'] * 60;
				$res_minutes = $res_minutes + $times_post['mamo_backtime_mins'];
				# calculate real days/hours/minutes to avoid values like: 10 days, 28 hours, 800 seconds
				$duration_arr = $this->duration($res_minutes);
				$times_post['mamo_backtime_days'] = $duration_arr['days'];
				$times_post['mamo_backtime_hours'] = $duration_arr['hours'];
				$times_post['mamo_backtime_mins'] = $duration_arr['mins'];
				return $times_post[$optname];
				
			default:
				if (isset($_POST[$optname])) {			
					return $_POST[$optname];
				} else {
					return;
				}
		} // switch
	}


	/**
	 * Convert option before HTML output ("COPTHTML"). 
	 * *NOT* used by the framework class
	 */
	function COPTHTML($optname) {
		$optval = $this->g_opt[$optname];
		switch ($optname) {
			case 'mamo_excludedpaths':
				return $this->WhitespaceToLinebreak($optval);
			case 'mamo_pagetitle':
				return htmlspecialchars(stripslashes($optval));
			case 'mamo_pagemsg':
				return htmlspecialchars(stripslashes($optval));
			default:
				return $optval;
		} // switch
	}

	/**
	 *  Calculates date, time and number of minutes until the maintenance mode is supposed to be finished 
	 */	 
	function calculate_times() {
		$delay = 0;
		$delay = $delay + ( intval($this->g_opt['mamo_backtime_days']) * 24 * 60 );
		$delay = $delay + ( intval($this->g_opt['mamo_backtime_hours']) * 60 );
		$delay = $delay + ( intval($this->g_opt['mamo_backtime_mins']) );
		$intTimeActivated = intval(substr($this->g_opt['mamo_activate'], 3, 99)); // get the activation timestamp from the plugin option
		$intTimeFinished = $intTimeActivated + ($delay*60); // calculate the time when maintenance is supposed to be finished.  
		$intCurrentTime = current_time('timestamp'); // get the current time, it considers GMT WordPress option settings
		$strTryBackDate = date_i18n( get_option('date_format'), $intTimeFinished ); // convert date into string format according to option settings
		$strTryBackTime = date_i18n( get_option('time_format'), $intTimeFinished ); // convert date into string format according to option settings
		$intTimeDelta_Seconds = $intTimeFinished - $intCurrentTime; // number of seconds until maintenance is supposed to be finished.  
		$intTimeDelta_Minutes = round(($intTimeDelta_Seconds/(60)), 0);	// convert into minutes
		$intTimeDelta_Hours = round(($intTimeDelta_Seconds/(60*60)), 1);	// convert into hours
		if ( $intTimeDelta_Minutes < 0 ) {	// if time exceeded, we display "0".
			$intTimeDelta_Minutes = 0;
			$intTimeDelta_Hours = 0;
		}


		$arrDuration = $this->duration($intTimeDelta_Minutes); // Converts number of minutes to days, hours and minutes
	
		// Output in array
		return array(
			'date'=> $strTryBackDate,
			'time'=> $strTryBackTime,
			'minutes_total' => $intTimeDelta_Minutes,
			'hours_total' => $intTimeDelta_Hours,
			'calc_days' => $arrDuration['days'],
			'calc_hours' => $arrDuration['hours'],
			'calc_mins' => $arrDuration['mins'],
		  );
	}


	/**
	 * Converts number of minutes to days, hours and minutes. For example '35390' results in '24 days, 13 hours, 50 minutes'
	 */			 			 
    function duration($minutes) {
    	$minutes = intval($minutes);
        $vals_arr = array(	'days' => (int) ($minutes / (24*60) ), 
                      		'hours' => $minutes / 60 % 24, 
                      		'mins' => $minutes % 60); 
		$return_arr = array(); 
		$is_added = false; 
		foreach ($vals_arr as $unit => $amount) { 
			$return_arr[$unit] = 0;
			if ( ($amount > 0) || $is_added ) { 
				$is_added = true;
				$return_arr[$unit] = $amount;
			}
		}
		return $return_arr;
    } 




	/**
	 *  Provides the the maintenance mode message for the theme incl. replacement of placeholders. 
	 */
	function mamo_template_tag_message() {
			$mamo_msg = stripslashes($this->g_opt['mamo_pagemsg']);
			$mamo_msg = str_replace('[blogurl]', get_option('home'), $mamo_msg);
			$mamo_msg = str_replace('[blogtitle]', get_bloginfo('name'), $mamo_msg);

			$calctimes_arr = $this->calculate_times();
			if ($calctimes_arr['minutes_total'] == 0) {
				$mamo_msg = str_replace('[until]', $this->g_opt['mamo_placeholder_until_exc'], $mamo_msg);
			} else {
				$mamo_msg = str_replace('[until]', $this->g_opt['mamo_placeholder_until'], $mamo_msg);			
			}
			$mamo_msg = str_replace('[date]', $calctimes_arr['date'], $mamo_msg);
			$mamo_msg = str_replace('[time]', $calctimes_arr['time'], $mamo_msg);
			$mamo_msg = str_replace('[days]', $calctimes_arr['calc_days'], $mamo_msg);
			$mamo_msg = str_replace('[hours]', $calctimes_arr['calc_hours'], $mamo_msg);
			$mamo_msg = str_replace('[minutes]', $calctimes_arr['calc_mins'], $mamo_msg);
			return $mamo_msg;
	}

	/**
	 *  Provides the the login/logout menu for the theme  
	 */
	function mamo_template_tag_login_logout() {

		global $user_ID, $wp_version, $status;
		get_currentuserinfo();
		$returnval = '';
		// Get URLs for login/logout
		// wp_logout_url() does not work here for some unknown reason...
		$loginurl = site_url('wp-login.php', 'login');
		$logouturl = wp_nonce_url( site_url('wp-login.php?action=logout', 'login'), 'log-out' );
		$adminurl = admin_url();
		if ($user_ID) {
			if ($status == 'noaccesstobackend' ) {
				$returnval .= __('(Access to administration denied by administrator)',$this->g_info['ShortName']);
			} else {
				$returnval .= '<a rel="nofollow" href="' . $adminurl . '">' . __('Administration',$this->g_info['ShortName']) . '</a>';
			}
			$returnval .= ' | <a rel="nofollow" href="'. $logouturl .  '">' . __('Log Out',$this->g_info['ShortName']) . '</a>';
		} else {
			$returnval .= '<a rel="nofollow" href="' . $loginurl . '">' . __('Log In',$this->g_info['ShortName']) . '</a>';
		}
		return $returnval;
	}


} // class


if( !isset($myMaMo)  ) {
	// Create a new instance of your plugin that utilizes the WordpressPluginFramework and initialize the instance.
	$myMaMo = new MaintenanceMode();

	$myMaMo->Initialize( 
		// 1. We define the plugin information now and do not use get_plugin_data() due to performance.
		array(	 
			# Plugin name
				'Name' => 			'Maintenance Mode',
			# Author of the plugin
				'Author' => 		'Michael W&ouml;hrer',
			# Authot URI
				'AuthorURI' => 		'http://sw-guide.de/',
			# Plugin URI
				'PluginURI' => 		'http://sw-guide.de/wordpress/plugins/maintenance-mode/',
			# Support URI: E.g. WP or plugin forum, wordpress.org tags, etc.
				'SupportURI' => 	'http://wordpress.org/tags/maintenance-mode',
			# Name of the options for the options database table
				'OptionName' => 	'plugin_maintenance-mode',
			# Old option names to delete from the options table; newest last please
				'DeleteOldOpt' =>	array('plugin_maintenancemode', 'plugin_maintenancemode2','plugin_maintenance-mode_5'),
			# Plugin version
				'Version' => 		'5.4',
			# First plugin version of which we do not reset the plugin options to default;
			# Normally we reset the plugin's options after an update; but if we for example
			# update the plugin from version 2.3 to 2.4 und did only do minor changes and
			# not any option modifications, we should enter '2.3' here. In this example
			# options are being reset to default only if the old plugin version was < 2.3.
				'UseOldOpt' => 		'5.0',
			# Copyright year(s)
				'CopyrightYear' => 	'2006-2010',
			# Minimum WordPress version
				'MinWP' => 			'2.7',				
			# Do not change; full path and filename of the plugin
				'PluginFile' => 	__FILE__,
			# Used for language file, nonce field security, etc.				
				'ShortName' =>		'maintenance-mode',
			),

		// 2. We define the plugin option names and the initial options
		array(
			'mamo_activate' => 			'off',
			'mamo_excludedpaths' => 	'',
			'mamo_include_feeds' => 	'',
			'mamo_include_trackbacks' =>'',
			'mamo_include_xmlrpc'	 => '',
			'mamo_backtime_days' =>		'0',
			'mamo_backtime_hours' =>	'1',
			'mamo_backtime_mins' =>		'0',
			'mamo_pagetitle' => 		'Maintenance Mode',
			'mamo_pagemsg' => 			'<h1>Maintenance Mode</h1>' . "\n\n" . '<p><a title="[blogtitle]" href="[blogurl]">[blogtitle]</a> is currently undergoing scheduled maintenance.<br />' . "\n<br />\n" . 'Please try back [until].</p>' . "\n\n" . '<p>Sorry for the inconvenience.</p>',
			'mamo_placeholder_until' => '<strong>in [days] days, [hours] hours, and [minutes] minutes</strong><br />(on [date] at [time])',
			'mamo_placeholder_until_exc' => 'again soon',
			'mamo_503_splashpage' => 	'',
			'mamo_theme' => 			'default',
			'mamo_role_frontend' => 	'manage_options',
			'mamo_role_backend' => 		'read',
		));


#	$myMaMo->ApplyMaintenanceMode();	// commented out and added the line below since plugin version 5.3 -- http://radiok.info/blog/the-case-of-maintenance-mode/
	add_action('plugins_loaded', array($myMaMo, 'ApplyMaintenanceMode'));


	############################################################################
	# Template Tags for using in themes
	############################################################################
	/**
	 * You can display a warning message in the front-end if you are logged in and the Maintenance Mode is activated
	 * to remember you to deactivate the Maintenance Mode.
	 */	 
	function is_maintenance() {
		global $myMaMo; 
		if ( substr($myMaMo->g_opt['mamo_activate'], 0, 2) == 'on' ) {
			return true;
		} else {
			return false;
		}
	}


} // if( !$myMaMo


?>