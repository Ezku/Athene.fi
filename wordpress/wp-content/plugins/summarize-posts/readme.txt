=== Summarize Posts ===
Contributors: fireproofsocks
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=355ULXBFXYL8N
Tags: 
Requires at least: 3.0.1
Tested up to: 3.1.4
Stable tag: 0.6

Offers highly flexible alternatives to WordPress' built-in get_posts() function, including the ability to paginate results.

== Description ==

Summarize posts offers an improved alternative to the built-in WordPress `get_posts()`, `query_posts()`, and `WP_Query` methods for retrieving posts. The new functions are exposed both to your theme files and to your posts via shortcode tags. You can search by taxonomy terms, post title, status, or just about any other criteria you can think of. You can also paginate the results and format them in a flexible and tokenized matter. These functions are loop-agnostic: they can be used inside or outside of the loop.

You can easily search by taxonomy term, you can easily sort results by custom fields, and you can paginate results.

`// Example Usage	
$Q = new GetPostsQuery();
$Q->set_output_type(ARRAY_A);
$results = $Q->get_posts();
foreach ($results as $r):
?>
	<li><a href="<?php print $r['permalink']; ?>"><?php print $r['post_title']; ?></a></li>
<?php
endforeach;
?>`


`<?php
// Example Usage 
$args = array('author'=>'fireproofsocks');
SummarizePosts::summarize($args);
?>`

This plugin is still in development! If you download, please be willing to file bug reports at http://code.google.com/p/wordpress-summarize-posts/issues/list

== Installation ==

1. Upload this plugin's folder to the `/wp-content/plugins/` directory or install it using the traditional WordPress plugin installation.
1. Activate the plugin through the 'Plugins' menu in the WordPress manager.
1. Now you can use the shortcode to list all types of posts, or you can instantiate the GetPostsQuery() object in your theme files.

== Frequently Asked Questions ==

= What search parameters are available? =

The main function is documented on the project wiki: [get_posts()](http://code.google.com/p/wordpress-summarize-posts/wiki/get_posts)

There are lots of options there, many of them derived from WordPress' built in get_posts() function.

= How do I paginate results? =

If your query might return LOTS of results, it makes sense to paginate the results.  This can be done by setting the *paginate* option, then by printing the results of the *get_pagination_links()* function. 

`$Q = new GetPostsQuery();
$Q->limit = 5; // determines the results per page displayed 
$Q->paginate = true;
$results = $Q->get_posts();
// ... format results
print $Q->get_pagination_links(); // print the pagination links`

Using *paginate="true"* inside of a shortcode will cause the pagination links to appear _after_ the posts, wrapped inside of a div: `<div class="summarize-posts-pagination-links">`

See the _wp-content/plugins/summarize-posts/includes/PostPagination.conf.php_ for more information about customizing the format of the pagination.

= I'm Having trouble getting the search results I want. What should I do? =

There are some debugging options available.  If you are using the GetPostsQuery object directly, you can print the object for a list of helpful information:

`$Q = new GetPostsQuery();
$Q->post_title = 'My Title';
$results = $Q->get_posts();

print $Q; // <-- this prints debugging information`

You can also trigger this same information from a shortcode by using the 'help' attribute, e.g.
`[summarize_posts help="1"]`

If you're using the object method, you can also rely on some of the component methods, e.g.

`print $Q->format_errors(); // returns a <ul> of any errors
print $Q->format_args(); // returns a <ul> of all sanitized args`

= How do I file bugs? = 

Thank you for your interest in this plugin! I want it to be as good as possible, so thank you for taking the time to file a bug! You can file bugs on the [project page](http://code.google.com/p/wordpress-summarize-posts/issues/list)

= How can I use this to produce a list of posts? =

This plugin can be used inside theme files or via shortcodes inside of your post's main content block, e.g. paste the following to show posts by a certain author.

`[summarize-posts author="yourname"]`

Or place code directly in your theme files:

`$args = array('search_term'=>'Something');
$Q = new GetPostsQuery();
$Q->set_output_type(OBJECT);
$results = $Q->get_posts($args);
foreach ($results as $r):
?>
	<li><a href="<?php print $r->permalink; ?>"><?php print $r->post_title; ?></a></li>
<?php
endforeach;
?>`


= How can I use this inside a theme file? =

Accessing the classes directly offers much greater flexibility. 

`$Q = new GetPostsQuery();
$Q->output_type = OBJECT;
$args = array('date_min'=>'2011-01-01');
$results = $Q->get_posts($args);
foreach ($results as $r) {
	print $r->post_title;
}`


== Screenshots ==

1. This plugin has an administration page where you can change some of the settings that affect how it works.
1. The object interface is thoroughly implemented!
1. If you enable pagination, you can easily flip through large result sets.

== Changelog ==

= 0.6 =

* Fixed issue with specifying a taxonomy.

= 0.5 =

* Pagination support added.
* Added support for sorting by custom fields
* Improved error messaging.

= 0.4 =

Initial public release.


== Requirements ==

* WordPress 3.0.1 or greater
* PHP 5.2.6 or greater
* MySQL 5.0.41 or greater

These requirements are tested during WordPress initialization; the plugin will not load if these requirements are not met. Error messaging will fail if the user is using a version of WordPress older than version 2.0.11. 


== About ==

This plugin was written to help offer a simpler way to summarize posts of all kinds. There are other similar plugins available, but none of them offered the control in selecting posts or in formatting the results that I wanted.


== Future TO-DO == 

* Add help links to wiki.


== Upgrade Notice ==

= 0.5 =

More better.  Sorting on custom fields, improved error messaging, pagination support added.

= 0.4 =

Initial public release.

== See also and References ==
* See the project homepage: http://code.google.com/p/wordpress-summarize-posts/