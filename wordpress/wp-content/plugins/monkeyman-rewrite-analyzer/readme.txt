=== Monkeyman Rewrite Analyzer ===
Contributors: janfabry
Tags: rewrite, mod_rewrite, permalinks, development, debug
Requires at least: 3.0
Tested up to: 3.2-beta1
Stable tag: 1.0

Making sense of the rewrite mess. Display and play with your rewrite rules.

== Description ==

This is a tool to understand your rewrite rules ("Pretty Permalinks"). It is indispensable if you are adding or modifying rules and want to understand how they work (or why they don't work).

It is only an analyzer, it does not change any rules for you. It parses the rules down to their components and shows the connection with the resulting query variables. It allows you to try out different URLs to see which rules will match and what the value of the different query variables will be (see screenshots).

This plugin was written as a tool to help answering questions about rewrite rules on [the WordPress Stack Exchange](http://wordpress.stackexchange.com/).

== Installation ==

Install via the normal way: either by uploading the unzipped file to your plugin  directory, or directly via the plugin installer.

An extra item will be added to the "Tools" menu, visible if you are an administrator.

== Frequently Asked Questions ==

= My new rules aren't displayed! =

Did you flush the rules? You can do this in your code, or by visiting the "Permalinks" settings page.

= Can you help me with my rewrite rules? =

Maybe. I'm active on [the WordPress Stack Exchange](http://wordpress.stackexchange.com/), where you can find many intelligent and friendly WordPress experts willing to answer your questions.

== Screenshots ==

1. Display all rewrite rules and highlight captured URL parts and ignored query variables
2. Test URLs and see matching rules with the resulting query variables

== Changelog ==

= 1.0 =
* First WP.org plugin repository release
* Further code cleanup

= 0.5 =
* Code cleanup
* Added URL checker

= 0.2 =
* Highlight non-public query vars
* Highlight regex repeating groups

= 0.1 =
* First version, mostly a test of my regex parsing skills