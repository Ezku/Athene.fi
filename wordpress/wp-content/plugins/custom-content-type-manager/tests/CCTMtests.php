<?php
/*------------------------------------------------------------------------------
Make sure we're cleared to launch.  Test the following

When you add a dropdown custom field, does the JS fire to let you create options?
When you deselect the dropdown as the field type, do the options clear?
------------------------------------------------------------------------------*/
class CCTMtests
{
	public static $errors = array(); // Any errors thrown.
//	public static $warnings = array();
	
	//------------------------------------------------------------------------------
	/**
	 * @param	array	list of names of incompatible plugins
	 */
	public static function incompatible_plugins($incompatible_plugins) 
	{
		require_once(ABSPATH.'/wp-admin/includes/admin.php');
		$all_plugins = get_plugins();
		$active_plugins = get_option('active_plugins');	

		$plugin_list = array();
		
		foreach ( $incompatible_plugins as $bad_plugin ) {
			foreach ($active_plugins as $plugin) {
				if ( $all_plugins[$plugin]['Name'] == $bad_plugin ) {
					$plugin_list[] = $bad_plugin;
				}
			}
		}
		
		if (!empty($plugin_list)) {
			$exit_msg = sprintf( __( '%1$s has detected that there are some active plugins that may be incompatible with it. We recommend disabling the following plugins:', CCTM_TXTDOMAIN)
				, '<strong>'.CCTM::name.'</strong>' );
			$exit_msg .= '<ul>';
			foreach ($plugin_list as $p ) {
				$exit_msg .= '<li><strong>'. $p . '</strong></li>';
			}
			$exit_msg .= '</ul>';
			$exit_msg .= sprintf( __('Continued use of these plugins may cause erratic behavior and certain functionality on your site may break entirely. See the <a href="http://code.google.com/p/wordpress-custom-content-type-manager/wiki/IncompatiblePlugins">wiki</a> for more information.', CCTM_TXTDOMAIN) );
		}
		
		if ( !empty($exit_msg) ) {
			CCTM::$warnings[] = $exit_msg;
		}
	}
	
	//------------------------------------------------------------------------------
	 /**
	  * @param	string minimum req'd version of MySQL, e.g. 5.0.41
	  * @return none, but the $errors array is populated
	  */ 	
	public static function mysql_version_gt($ver)
	{
		global $wpdb;
		
		$result = $wpdb->get_results( 'SELECT VERSION() as ver' );

		if ( version_compare( $result[0]->ver, $ver, '<') ) 
		{	
			$exit_msg = sprintf( __( '%1$s requires MySQL %2$s or newer.', CCTM_TXTDOMAIN)
			, CCTM::name, $ver );
			$exit_msg .= ' ';
			$exit_msg .= __('Talk to your system administrator about upgrading.', CCTM_TXTDOMAIN);	

			self::$errors[] = $exit_msg;
		}
	}

	/*------------------------------------------------------------------------------
	SUMMARY: This relies on the output of the get_plugins() function and the 
		get_option('active_plugins') contents.
		
	INPUT:
		$required_plugins should be an associative array with the names of the plugins
		 and the required versions, e.g.
		 array( 'My Great Plugin' => '0.9', 'Some Other Plugin' => '1.0.1' )
		 
	OUTPUT: null if no errors. There are 2 errors that can be generated: one if the 
	plugin's version is too old, and another if it is missing altogether.
	------------------------------------------------------------------------------*/
	public static function wp_required_plugins($required_plugins)
	{
		require_once(ABSPATH.'/wp-admin/includes/admin.php');
		$all_plugins = get_plugins();
		$active_plugins = get_option('active_plugins');
		
		// Re-index the $all_plugins array for easier testing. 
		// We want to index it off of the name; it's not guaranteed to be unique, so this 
		// test could throw some illigitimate errors if 2 plugins shared the same name.
		$all_plugins_reindexed = array();
		foreach ( $all_plugins as $path => $data )
		{
			$new_index = $data['Name'];
			$all_plugins_reindexed[$new_index] = $data;
		}
		
		foreach ( $required_plugins as $name => $version )
		{
			if ( isset($all_plugins_reindexed[$name]) )
			{
				if ( !empty($all_plugins_reindexed[$name]['Version']) )
				{
					if (version_compare($all_plugins_reindexed[$name]['Version'],$version,'<'))
					{
						self::$errors[] = sprintf( __('%1$s requires version %$2% of the %3$s plugin.', CCTM_TXTDOMAIN )
							, CCTM::name
							, $version
							, $name );			
					}
				}
			}
			else
			{
				$msg = sprintf( __('%1$s requires version %$2% of the %3$s plugin.', CCTM_TXTDOMAIN )
							, CCTM::name
							, $version
							, $name );
							
				 $msg .= ' ';
				 $msg .=  sprintf( 
					__('The %1$s plugin is not installed.', CCTM_TXTDOMAIN)
					, $name
				);
				self::$errors[] = $msg;
			}
		}
	}
	
	//------------------------------------------------------------------------------
	public static function wp_version_gt($ver)
	{
		global $wp_version;
		
		if (version_compare($wp_version,$ver,'<'))
		{
			self::$errors[] = sprintf( __('%1$s requires WordPress %2$s or newer. <a href="http://codex.wordpress.org/Upgrading_WordPress">Please update!</a>', CCTM_TXTDOMAIN)
			, CCTM::name
			, $ver );
		}

	}

	//------------------------------------------------------------------------------
	public static function php_version_gt($ver)
	{
		
		if ( version_compare( phpversion(), $ver, '<') ) 
		{
			$exit_msg = sprintf( __('%1$s requires PHP %2$s or newer', CCTM_TXTDOMAIN )
				,  CCTM::name
				, $ver );
			$exit_msg .= __('Talk to your system administrator about upgrading.', CCTM_TXTDOMAIN);	
			self::$errors[] = $exit_msg;
		}
	}
	
	
	
	/*------------------------------------------------------------------------------
	PHP might have been compiled without some module that you require. Pass this 
	function an array of $required_extensions and it will throw return a message 
	about any missing modules.
	INPUT: 
		$required_extensions = array('pcre', 'posix', 'mysqli', 'mcrypt');
	OUTPUT: null, or an error message.
	------------------------------------------------------------------------------*/
	public static function php_extensions($required_extensions)
	{
		
		$loaded_extensions = get_loaded_extensions();

		foreach ( $required_extensions as $req )
		{
			if ( !in_array($req, $loaded ) )
			{
				$msg =  sprintf( __('%1$s requires the %2$s PHP extension.', CCTM_TXTDOMAIN)
					, CCTM::name
					, $req
				);
				
				$msg .= __('Talk to your system administrator about reconfiguring PHP.', CCTM_TXTDOMAIN);
				self::$errors[] = $msg;
			}
		}
	
	}
}
/*EOF*/