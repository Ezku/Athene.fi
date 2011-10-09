<p><?php _e('I am sorry that you are having trouble with this plugin, but thank you for taking the time to file a bug. It helps out everybody who uses this code. Your input is valuable! Click the link above to launch the bug tracker.', CCTM_TXTDOMAIN); ?></p>

<div style="border: 2px dotted green; width:70%; padding: 10px;">
	<img src="<?php print CCTM_URL; ?>/images/help-large.png" width="48" height="48" style="float:left; padding:10px;"/>
	<p><?php _e('If you require immediate help for this plugin, you can contract me at the following email address for a reasonable hourly rate:'); ?>
	<script type="text/javascript" src="<?php print CCTM_URL; ?>/js/contactemail.js"></script></p>
</div>
	
<h3><?php _e('System Info', CCTM_TXTDOMAIN); ?></h3>
<p><?php _e('Please paste the following text into your bug report so I can better diagnose the problem you are experiencing.', CCTM_TXTDOMAIN); ?></p>
	
<textarea rows="20" cols="60" class="sample_code_textarea" style="border: 1px solid black;">
*SYSTEM INFO* <?php print "\n"; ?>
------------------------ <?php print "\n"; ?>
Plugin Version: <?php print CCTM::version; print '-'; print CCTM::version_meta; print "\n"; ?>
WordPress Version: <?php global $wp_version; print $wp_version; print "\n";?>
PHP Version: <?php print phpversion(); print "\n"; ?>
MySQL Version: <?php 
global $wpdb;
$result = $wpdb->get_results( 'SELECT VERSION() as ver' );
print $result[0]->ver;
print "\n";
?>
Server OS: <?php print PHP_OS; print "\n"; ?>
------------------------ <?php print "\n"; ?>
Other Active plugins: <?php 
print "\n";
$active_plugins = get_option('active_plugins'); 
$all_plugins = get_plugins();
foreach ($active_plugins as $plugin) {
//	print_r($all_plugins[$plugin]);
	if ( $all_plugins[$plugin]['Name'] != 'Custom Content Type Manager' ) {
		printf (' * %s v.%s [%s]'
			, $all_plugins[$plugin]['Name']
			, $all_plugins[$plugin]['Version']
			, $all_plugins[$plugin]['PluginURI']
		);
		print "\n";
	}
}
?>
</textarea>

<p><?php _e('When reporting bugs, remember the following key points:', CCTM_TXTDOMAIN); ?></p>

<ol>
	<li><?php _e("<strong>If the bug can't be reproduced, it can't be fixed.</strong> Provide <em>detailed</em> instructions so that someone else can make the plugin fail for themselves.", CCTM_TXTDOMAIN); ?></li>
	<li><?php _e("<strong>Be ready to provide extra information if the programmer needs it.</strong> If they didn't need it, they wouldn't be asking for it.", CCTM_TXTDOMAIN); ?></li>
	<li><?php _e("<strong>Write clearly.</strong> Make sure what you write can't be misinterpreted. Avoid pronouns, and error on the side of providing too much information instead of too little.", CCTM_TXTDOMAIN); ?></li>
</ol>
<p><?php _e('Consider using <a href="http://www.techsmith.com/jing/free/">Jing</a> to do a screencast of the problem!', CCTM_TXTDOMAIN); ?></p>
<p><?php _e('The gist of this was inspired by <a href="http://www.chiark.greenend.org.uk/~sgtatham/bugs.html">How to Report Bugs Effectively</a> by Simon Tatham.', CCTM_TXTDOMAIN);?></p>