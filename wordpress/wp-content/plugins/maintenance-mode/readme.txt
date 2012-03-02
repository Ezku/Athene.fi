=== Maintenance Mode ===
Contributors: Michael_
Plugin Name: Maintenance Mode
Plugin URI: http://sw-guide.de/wordpress/plugins/maintenance-mode/
Author URI: http://sw-guide.de/
Donate link: http://sw-guide.de/donation/
Tags: maintenance, mode, admin, administration, unavailable, offline, construction
Requires at least: 2.7
Tested up to: 3.0.1
Stable tag: 5.4

Adds a splash page to your blog that lets visitors know your blog is down for maintenance.  

== Description ==

Adds a splash page to your blog that lets visitors know your blog is down for 
maintenance. Logged in administrators get full access to the blog including the front-end.

Visitors will see a message like "Maintenance Mode - SITE is currently undergoing scheduled maintenance. Please try back in 60 minutes. Sorry for the inconvenience."


Please visit [the official website](http://sw-guide.de/wordpress/plugins/maintenance-mode/ "Maintenance Mode") for further details and the latest information on this plugin.

== Installation ==

See on [the official website](http://sw-guide.de/wordpress/plugins/maintenance-mode/ "Maintenance Mode").


== Frequently Asked Questions ==

= Where can I get more information? =

Please visit [the official website](http://sw-guide.de/wordpress/plugins/maintenance-mode/ "Maintenance Mode") for the latest information on this plugin.

== Screenshots ==

1. Default theme
2. WordPress login theme
3. Plugin options, part 1
4. Plugin options, part 2

== Changelog ==

= 5.4 [2010-10-25] =
* Bug fix: Blank white screen when using Super Cache plugin
* Bug fix: Syntax error regarding add_action('admin_notices'...
* New feature: possibility to exclude the home page

= 5.3 [2010-10-19] =
* Bug fix: broke compatibility with the pluggable architecture, thanks to [radiok](http://radiok.info/blog/the-case-of-maintenance-mode/ "radiok").


= 5.2 [2010-06-03] =
* Bug fix: Syntax error regarding add_action('admin_notices'..
* Improved redirection from /wp-admin/ to wp-login.php
* No longer support of WordPress versions < 2.7

= 5.1 [2010-05-13] =
* New feature: Options to activate/deactivate feeds, trackbacks and XML-RPC publishing
* New feature: Redirect from /wp-admin/ to wp-login.php if the user is not logged in
* Bug fixes and improvements under the hood


= 5.0 [2010-05-09] =
* New feature: Countdown timer
* New feature: Theme support
* New feature: Backtime in days, hours and minutes
* New feature: Improved access management for front end and backend
* New feature: New option to keep feeds and trackbacks working
* ... and many more improvements, new features and bug fixes

= 4.4 [2009-12-31] =
* Bug fix with media uploader: With the “Deny non-administrators to access the blog’s back-end” option enabled, the Flash version of the Media Uploader returns the maintenance mode page when something is uploaded. This happened when uploading using an admin user.
* Bug fix: Included plugins directory so that currently enabled plugins work while in maintenance mode.

= 4.3 [2009-02-17] =
* Bug fix: is_maintenance() did not work
* Bug fix: Maintenance mode splash page was shown when maintenance mode deactivated but option “Deny non-administrators to access the blog’s back-end” activated

= 4.2 [2009-02-01] =
* New feature: Optionally use a ‘503.php’ from within the theme directory for the splash page
* New feature: Optionally deny non-administrators to access the blog’s back-end

= 4.1 [2009-01-28] =
* Bug fix: Minor bug fixes

= 4.0 [2009-01-13] =
* Bug fix: Several bug fixes.
* New feature: Added localization support.

= 3.2 [2007-08-28] =
* Bug fix: Replaced include('maintenance-mode_site.php') with include( dirname(__FILE__) . '/maintenance-mode_site.php')
* Improvement: Better HTTP header output
* New feature: Apply HTTP header ‘503 Service Unavailable’ to splash page

= 3.1 [2007-08-03] =
* Bug fix: Replaced “&lt;?” with “&lt;?php” in line 210
= 3.0 [2007-07-07] =
* New feature: User roles and capabilities are supported now.
* New feature: Message is displayed in administration panel when maintenance mode is activated.
* New feature: Template tag is_maintenance() is available to display a message in the theme when maintenance mode is activated.
* Bug fix: back time in HTML header was not applied.
* New feature: simplified options by removing section for second language, it can be entered in the normal message box.

= 2.3 [2007-03-04] =
* Bug fix: For the link to the administration menu, get_settings('home') was used instead of get_settings('siteurl').

= 2.2 [2007-01-01] =
* Bug fix: As long as the plugin was active the feeds didn’t work although the maintenance mode was not active.
* New feature: Plugin updated to work with Wordpress 2.1

= 2.1 [2006-11-26] =
* New feature: Maintenance Mode can be activated/deactivated in the plugin’s options, for details see comment #35.

= 2.0 [2006-06-20] =
* New feature: Added options page in the WordPress admin. Supports modification of the messages and many more.

= 1.1 [2006-04-30] =
* New feature: User levels are supported now.

= 1.0 [2006-04-29] =
* Initial release