=== Custom Content Type Manager ===
Contributors: fireproofsocks
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=355ULXBFXYL8N
Tags: cms, content management, custom post types, custom content types, custom fields, images, image fields, ecommerce, modx
Requires at least: 3.0.1
Tested up to: 3.2.1
Stable tag: 0.9.4.3
Version: 0.9.4.3-pl

Create custom content types, standardize custom fields for each type, including dropdowns and images. Gives WP CMS functionality.

== Description ==

http://www.youtube.com/watch?v=xmr1bOndP1g

The Custom Content Type Manager plugin allows users to create custom content types (also known as post types) and standardize custom fields for each, including dropdowns, checkboxes, and images. This gives WordPress CMS functionality making it easier to use WP for eCommerce or content-driven sites.

This plugin also lets you export and import those content definitions, making it easy to ensure a similar structure between multiple sites.

If you are upgrading to version 0.9.4, please see the Wiki page on [Upgrading to version 0.9.4](http://code.google.com/p/wordpress-custom-content-type-manager/wiki/Upgrading094)!

Check the site for a [full list of features](http://code.google.com/p/wordpress-custom-content-type-manager/wiki/Features).

This plugin was written in part for the book [WordPress 3 Plugin Development Essentials](http://www.packtpub.com/wordpress-3-plugin-development-essentials/book), published by Packt.

= Links =

Please use the following links for support and discussion:

* Please sign up for the CCTM [Mailing List](http://eepurl.com/dlfHg)
* Participate in the [Forum](http://wordpress.org/tags/custom-content-type-manager?forum_id=10)
* File [Bug reports](http://code.google.com/p/wordpress-custom-content-type-manager/issues/list) or make feature requests.
* Read the [Official documentation](http://code.google.com/p/wordpress-custom-content-type-manager/)

== Installation ==

This plugin uses the standard installation procedure: install the plugin's folder inside of `wp-content/plugins` (make sure the folder is named *custom-content-type-manager*).

Here is a typical use-case verbosely for the record:

1. If you are upgrading from version 0.8.7 or before, you must *completely* uninstall and remove the previous version! This will not delete any of your content, but you should take some notes about the exact names of your content types before doing this.  Sorry, I know it's a pain, but I had to correct for limitations in the data structure.  See [this Wiki page](http://code.google.com/p/wordpress-custom-content-type-manager/wiki/DeletePostType) for more information.
1. Install this plugin using the traditional WordPress plugin installation, or upload this plugin's folder to the `/wp-content/plugins/` directory (ensure that the directory is named *custom-content-type-manager*).
1. Activate the plugin through the 'Plugins' menu in the WordPress manager.
1. Upon activation you can adjust the plugin settings by clicking the newly created "Custom Content Types" menu item, or click this plugin's "Settings" link on the Plugins page.
1. After clicking the Settings link, you will see a list of content types -- there are two built-in types listed: post and page. To test this plugin, try adding a new content type named "movie" by clicking the "Add Custom Content Type" button at the top of the page.
1. There are a *lot* of options when setting up a new content type, but all the necessary ones are shown on the first page.  Pay attention to the "Name", "Show Admin User Interface", and "Public" settings. "Show Admin User Interface" *must* be checked in order for you to be able to create or edit new instances of your custom content type. 
1. Save the new content by clicking the "Create New Content Type" button.
1. Your content type should now be listed under on the main Custom Content Types Manager settings page. Activate your new content type by clicking the blue "Activate" link.
1. Once you have activated the content type, you should see a new menu item in the left-hand admin menu. E.g. "Movies" in our example.
1. Try adding some custom fields to your new content type by clicking on the "Manage Custom Fields" link on the settings page.
1. You can add as many custom fields as you want by clicking the "Add Custom Field" button at the top of the page, e.g. try adding a "plot_summary" field using a "textarea" input type, and try adding a "rating" dropdown. 
1. When you are finished configuring your custom fields, click the "Save Changes" button.
1. Now try adding a new instance of your content type ("Movies" in this example). Click the link in the left-hand admin menu to add a movie.
1. Your new "Movie" post will have the custom fields you defined.
1. If you have added any media custom fields, be sure to upload some images using the WordPress "Media" menu in the left-hand menu.


== Frequently Asked Questions ==

Please see the online [FAQ](http://code.google.com/p/wordpress-custom-content-type-manager/wiki/FAQ) for the most current information. 


== Screenshots ==

1. After activating this plugin, you can create custom content types (post types) by using the configuration page for this plugin. Click the "Custom Content Types" link under the Settings menu or click this plugin's "Settings" shortcut link in on the Plugin administration page.
2. You can list all defined content types (a.k.a. post-types) by clicking on the "Custom Content Types" menu item.
3. There are a lot of options available when you create a new content type, only some of them are pictured.
4. You can define many different types of custom fields by clicking on the "Custom Content Types --> Custom Fields" link.
5. Clicking the "activate" link for any content type will cause its fields to be standardized and it will show up in the administration menus.
6. Once you have defined custom fields and associated them with one or more content types, those custom fields will show up when you create or edit a new post. 

== Changelog ==

You can always checkout the most recent version of the code at 

	http://wpcctm.com/cctm-dev.zip 
	
or by going to your wp-content/plugins directory and executing the following command from the command-line:

	svn checkout http://plugins.svn.wordpress.org/custom-content-type-manager/trunk custom-content-type-manager 

= 0.9.4.3 =

* Fixed glitch with custom hierarchies.
* Adjusted default size of text fields.

= 0.9.4.2 =

* Added support for "Right Now" dashboard widget. [Issue 200](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=200)
* Fixed issues with Categories and Archives: [Issue 202](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=202)
	
= 0.9.4.1 =

* Fixed [Issue 196](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=196): Wrong link to plugin's settings from the Plugin's page.
* Fixed [Issue 197](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=197): I had forgotten to uncomment lines that cleaned up field definitions after merging fields.
* Added support for duplication of custom fields: [Issue 174](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=174)
* Added support for duplication of post types: [Issue 173](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=173)

= 0.9.4 =

This is a major release:

* Fixed [Issue 187](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=187) that affected dropdowns and multi-selects: values were not trimmed before comparison, causing the current value to be unselected.
* Fixed [Issue 88](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=88) that prevented certain UTF8 characters (e.g. Polish) from being saved correctly in multi-select fields.
* Updated Output Filter functionality. See [Output Filters](http://code.google.com/p/wordpress-custom-content-type-manager/wiki/OutputFilters) in the wiki or [Issue 162](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=162) and [Issue 183](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=183) in the bug tracker.
* Added "Wrapper" and "Default" output filters. See [Wrapper](http://code.google.com/p/wordpress-custom-content-type-manager/wiki/wrapper_OutputFilter) and [Default](http://code.google.com/p/wordpress-custom-content-type-manager/wiki/default_OutputFilter) in the wiki.
* Custom Fields can now implement their own settings page. [Issue 161](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=161)
* Improved help pages on the [project Wiki](http://code.google.com/p/wordpress-custom-content-type-manager/w/list).
* Improved import/export of CCTM definitions.
* Settings page added to give control to the users. See [Issue 72](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=72).
* New menu icons! See [Issue 30](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=30).
* Large menu icon now displayed when you edit or create a post type [Issue 136](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=136).
* Fixed 2 issues [Issue 52](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=52) and [Issue 180](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=180) that caused wp_insert_post() to fail in different circumstances.
* Implemented [Feature 178](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=178): submenus added to the post_type menus.
* Fixed [Issue 168](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=168) that was preventing the admin user from unchecking "Rewrite with Permalink Front"
* Now CCTM is compatible with the Gravity Forms plugin per [Issue 155](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=155)
* General cleanup of the plugin's directory structure; general move towards MVC organization of all files.
* Finally added some appropriate images to the footer of the admin pages.
* Now HTML/JavaScript values can be stored correctly in text fields. See [Issue 152](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=152).
* Implemented more advanced dropdown and multi-select options where users can now optionally store distinct values and labels for each option. See [Issue 150](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=150).
* Revisited [Issue 146](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=146), discovered WordPress is adding slashes to $_POST, $_GET, $_COOKIE, and $_SERVER arrays (see [this article](http://kovshenin.com/archives/wordpress-and-magic-quotes/).  WHY IS WORDPRESS DOING THIS???
* Fixed some lingering PHP notices in date.php (whoops).
* Added method for Custom Field PHP classes to let each field add JS/CSS to the manager via the "admin_init" function. See [Issue 71](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=71).
* Cleaned up PHP notices when creating a "Date" custom field.
* Added checks for incompatible plugins [Issue 122](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=122)
* Fixed [Issue 145](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=145) so you can now include HTML in default values for text and checkbox fields.
* Added support for clean custom field un-installation:[Issue 145](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=145).
* Fixed [Issue 144](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=144) so now you can define content types (i.e. post_types) that use numerical names.
* Fixed [Issue 143](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=143) that prevented proper searching on the front-end.
* Fixed [Issue 142](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=142) custom post types can now be ordered correctly using the "Order" attribute.
* Fixed [Issue 139](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=139) that affected editing the names of custom fields -- a new field was created if the name was changed.
* Fixed [Issue 138](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=138) that affected WYSIWYG custom fields that failed if a content type's main Content block was not active.
* Implemented [Feature 126](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=126) so that bug reporting is easier: system info is generated for you to cut and paste into the bug report.
* Fixed [Issue 114](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=114) where custom hierarchies were limited to only 5 items.
* Fixed [Issue 115](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=115) where the "View Item" label was receiving incorrect values.
* Fixed [Issue 121](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=121) where Array output filters returned empty string if no options were checked.
* Fixed [Issue 123](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=123) so that names of custom fields are no longer limited to 20 characters: they now are limited to 255, the maximum possible length given the database column definition (VARCHAR 255).
	
= 0.9.3.3 =
* Fixed [Issue 112](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=112) custom page templates were being ignored.
	
= 0.9.3.2 =
* Fixed [Issue 111](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=111) with static pages not loading.

= 0.9.3.1 =

* Fixed the archive support -- supporting archives caused pages to 404.  This should also correct categories for custom post types.

= 0.9.3 =

* Sigh... this is the "correct" release of 0.9.2... !@%# WP repo has thwarted me again.
* Archive support added.

= 0.9.2 =

* Bad release with 0.9.1: re-release.
* Typos in editing post_type definition corrected.
* Bugs with media fields addressed, including adding output filters.
* Fixed issue 80 with error-warnings while auto-saving.

= 0.9.1 =

* Handled nebulous case with default value [Issue 45](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=45)
	
= 0.9.0 =

* Added custom field: Multi-select
* Added custom field: Color selector
* Added support for customized hierarchical post-types [Issue 9](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=9)
* Fixed mistake in escaping quotes in post_type definitions and custom field definitions.
* Fixed bug in sorting custom fields [Issue 70](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=70&can=1).
	
= 0.8.9 =

* Permalink functionality fixed by flushing rewrite rules after register_post_type()
* Flash messages are now stored in the $_COOKIE array instead of $_SESSION to be in keeping with WordPress' simplistic "stateless" parlance.
* Date field added, including support for PHP eval of default value.
* CSS classes and ids updated (all instances of "formgenerator" were replaced with "cctm").
* Import/Export functionality added.
* Checking of valid image icons was disabled due to problems with segfault in some server configurations [see Issue 60](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=60) 
* Default values for dropdowns now settable via Javascript to reduce typos
* Bug with page-attributes fixed: you can now correctly enable page attributes for custom content types.
* Support for Post Formats added.
* Support for [Output Filters](http://code.google.com/p/wordpress-custom-content-type-manager/wiki/OutputFilters) was added.
* Template functions `get_custom_image()` and `print_custom_image` were removed in favor of using Output Filters.
* The FormGenerator class removed in favor of simpler PHP pages.

= 0.8.8 =

* More CRUD-like interface for creating and editing custom fields one at a time.
* Object-Oriented class structure implemented for custom fields in accordance with future plans for more field types
* Drag and Drop interface added for Custom Fields to change sort order
* Added support for built-in taxonomies (Categories and Tags).
* Fixed unreported bugs affecting custom tpls. 
* Fixed bug causing popup thickbox to load incorrectly: [Issue 17](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=17&can=1)
* Styling for the manager updated to match what's used by WordPress 3.1.
* Greatly improved administration interface including support for icons and a series of tabs for dividing the multi-part form for creating/editing content types.
* Reduced MySQL requirements to version 4.1.2 (same as WordPress 3.1) after feedback that the plugin is working fine in MySQL 4: [issue 28](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=28&can=1)
* Fixed typos in CCTMTests.php re the CCTM_TXTDOMAIN: [issue 29](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=29&can=1).
* Added optional prefix for template *function get_all_fields_of_type()*: [Issue 26](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=26&can=1)
* Three new template functions added per [Issue 25](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=25&can=1)
** *get_custom_field_meta()*
** *print_custom_field_meta()*
** *get_custom_field_def()*
See [Template Functions](http://code.google.com/p/wordpress-custom-content-type-manager/wiki/TemplateFunctions) in the wiki.


= 0.8.7 =
* Adds HTML head and body tags back to the tpls/post_selector/main.tpl to correct issue 17 (http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=17&can=1).

= 0.8.6 =
* Fixes bad CSS declaration: [issue 1](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=1)
* Fixed omission in sample template placeholders.

= 0.8.5 =
* Resubmitting to placate WP's repository.

= 0.8.4 =

* Resubmitting to placate WP's repository.

= 0.8.3 =

* Customized manager templates. This is useful if you need to customize the manager interface for the custom fields.
* Updated sample templates under the "View Sample Template" link for each content type.
* Added image/media uploading functionality directly from the custom fields.
* Fixed glitch in Javascript element counter that screwed up dropdown menu creation: due to this bug, you could only add a dropbox to the first element because the wrapper div's id was fixed statically as the same id, so adding a dropdown menu always wrote the new HTML to that same div (inside the first custom field).
* Control buttons now at top and bottom of manage custom fields.
* Links to bug tracking / wiki for this project.
* Some basic HTML cleanup.
* Moved some files around for better organization.

=0.8.2=
* WordPress 3.0.4 fixed some bugs that affected the functionality of this plugin: now you CAN add custom content posts to WordPress menus.
* WordPress has not recognized the updates to this plugin (apparently due to a glitch), so currently the only way to get the most recent version of this is to check it out via SVN.

= 0.8.1 =
* Fixes problem saving posts. The problem had to do with wp-nonces and the admin referrer was being checked, but not generated, so the check failed. Oops.

= 0.8.0 =
* Initial public release. Collaborators can check out code at http://code.google.com/p/wordpress-custom-content-type-manager/

== Requirements ==

* WordPress 3.0.1 or greater
* PHP 5.2.6 or greater
* MySQL 4.1.2 or greater

These requirements are tested during WordPress initialization; the plugin will not load if these requirements are not met. Error messaging will fail if the user is using a version of WordPress older than version 2.0.11. 


== About ==

This plugin was written in part for the book [WordPress 3 Plugin Development Essentials](http://www.packtpub.com/wordpress-3-plugin-development-essentials/book) published by Packt. It was inspired by the [Custom-Post Type UI](http://wordpress.org/extend/plugins/custom-post-type-ui/) plugin written by Brad Williams. The Custom-Post Type UI plugin offers some of the same features, but I felt that its architecture was flawed: it stores data as taxonomical terms, which is conceptually awkward at a development level, and more importantly, it limits the each custom field to 200 characters of data, making it impossible to store certain types of custom content.

On the surface, this plugin is similar, but this plugin "correctly" stores custom field data as post meta data, which allows allows for different input types (e.g. checkboxes, dropdowns, and images) and the custom fields offered by this plugin can support data of virtually unlimited size. For example, you could define a WYSIWYG custom field for your custom content type and it could hold many pages of data.

The architecture for this plugin was also inspired by [MODx](http://modx.com/). WordPress is making progress as a viable content management system, but even after the thousands of lines of code in this plugin, it still does not support all the features in a system like MODx. WordPress templates are particularly limited by comparison. WordPress is great system for many scenarios, but if you're feeling that WordPress is starting to tear apart at the seams when it comes to custom content, it may be worth a look at another plugin or some of the other available systems.

== Future TO-DO == 

Please see the [Issues page](http://code.google.com/p/wordpress-custom-content-type-manager/issues/list) for the most current list of on-going feature development.

If you are eager to see a particular feature implemented in a future release, please share your feedback at the official [Issues page](http://code.google.com/p/wordpress-custom-content-type-manager/issues/list)

And if you REALLY want some of these features implemented, you can hire me to complete portions of your project or make a [donation](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=355ULXBFXYL8N).  Many of the surges in development in this plugin were instigated by projects that required this plugin's use.

== Upgrade Notice ==

= 0.9.4.1 =

Fixes minor bugs with the 0.9.4 release: cleanup after merging custom fields. Also a couple new features: duplication of defs, radio buttons!

= 0.9.4 =
Major release! Please see the upgrade notes: [Upgrading to version 0.9.4](http://code.google.com/p/wordpress-custom-content-type-manager/wiki/Upgrading094)

= 0.9.3.3 =
Corrects yet another glitch where custom page templates were being ignored.

= 0.9.3.2 =
Corrects another glitch with archive support that caused static pages to roll over to the blog index.

= 0.9.3.1 =
Fixed glitch in the archive support that caused pages to 404.

= 0.9.3 =
Adds archive support and fixes various bugs.

= 0.9.0 =
Fixed bug in custom-fields and post-type forms that caused errors with foreign characters and quotes. Implements color-pickers and multi-select custom fields.

= 0.8.9 =
Includes import/export functionality, support for date fields, and various bug fixes. If you are upgrading to 0.8.9 from version 0.8.7 or older, you must *completely uninstall* the plugin, then *re-install* [See bug](http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=62&can=1). 

= 0.8.8 =
Improved administration interface, new template functions, lots bug fixes; this is a big release.  If you are upgrading to 0.8.8 from a previous version, you should *completely uninstall* the plugin, then *re-install*.  This will ensure that the data-structure in the database is updated appropriately.

= 0.8.7 =
Adds HTML head and body tags back to the tpls/post_selector/main.tpl to correct issue 17 (http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=17&can=1).

= 0.8.6 =
Fixes CSS declaration that conflicted with Posts->Categories. Corrects examples in sample templates.

= 0.8.5 = 
Fixes some major bugs in managing custom fields. Now allows direct image uploading. Allows customized manager templates. (re-submit of 0.8.3)

= 0.8.4 = 
Was not picked up correctly by WordPress' repo

= 0.8.3 = 
Was not picked up correctly by WordPress' repo

= 0.8.2 =
Fixes a couple other glitches: apostrophes in media custom fields, editing content types.
Resubmitting this to get the updates to show up in WordPress' repository.  Sorry folks... seems that the WP captain has jumped ship, so I have no working instructions on how to get my updates to percolate down to the users.  

= 0.8.1 =
Fixing glitch in saving posts and pages.

= 0.8.0 =
Initial public release.

== See also and References ==
* http://kovshenin.com/archives/extending-custom-post-types-in-wordpress-3-0/
* http://kovshenin.com/archives/custom-post-types-in-wordpress-3-0/
* http://axcoto.com/blog/article/307
* Attachments in Custom Post Types:
http://xplus3.net/2010/08/08/archives-for-custom-post-types-in-wordpress/
* Taxonomies:
http://net.tutsplus.com/tutorials/wordpress/introducing-wordpress-3-custom-taxonomies/
* Editing Attachments
http://xplus3.net/2008/11/17/custom-thumbnails-wordpress-plugin/

