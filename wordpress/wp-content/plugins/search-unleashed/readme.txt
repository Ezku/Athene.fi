=== Search Unleashed ===
Contributors: johnny5
Tags: search, post, page, comment, lucene, widget, highlight
Requires at least: 2.9
Tested up to: 3.2.1
Stable tag: 1.0.6

Advanced WordPress search with wildcards, highlighting, log, and ability to search all post data. Now with Lucene!

== Description ==

Extends the standard WordPress search to include data from posts, pages, comments, and meta-data, as well as the full content of data inserted by plugins. This last feature makes
it unique amongst search plugins.  For example, you may have a plugin that uses shortcodes to insert data:

[product 1]

The standard search will just see the shortcode. Search Unleashed will see the actual data inserted into your post.

Search phrases are highlighted, as are incoming searches from Google, Yahoo, and most other search engines. This makes it very easy to see exactly what phrase matched and why the post is being shown.

Searches can be restricted to specific categories, tags, or meta-data. For example, you can search for 'persian' in the 'cats' category.

* Transparently extends WordPress search with a full text search that supports wildcards and logical operations
* Search through posts, pages, comments, meta-data, urls, and titles
* Priority based searching - configure how much priority to give to the various parts of a post
* Extendable search engines. Comes with three search engines (standard, MySQL fulltext, and Lucene)
* Data from plugins is included in the search - this is very important as previously any plugins that inserted data into
posts were not included in searches
* Everything can be configured - select exactly what data you want to search
* Smart highlighting that shows a contextual snapshot of the search data
* Incoming search highlight.  Searches made through Google, Yahoo, Altavista, Baidu, Sogou, and MSN are all highlighted
* No changes are made to standard WordPress database tables
* Advanced search widget

The plugin is available in:
* English
* Deutsch by Frank Bueltge, Gerhard Lehnhoff, and Andre
* Swedish by Olle Hellgren
* Russian by Lecactus
* Italiano by Dario
* Dutch by Evert
* French by Vincent Granger
* Polish by Krzysztof Kowalik
* Spanish by Ivan Garcia
* Turkish by Mehmet Karac
* Japanese by Hiroaki Miyashita
* Chinese by Yunfang Shang
* Brazillian Portuguese by Joao Miguel
* Danish by Georg S. Adamsen
* Lithuanian by WordPress TVS - Audrius
* Belorussian by Marcis Gasuns
* Czech by Pavel Soukenik
* Slovenian by Rok

== Installation ==

The plugin is simple to install:

1. Download `search-unleashed.zip`
1. Unzip
1. Upload `search-unleashed` directory to your `/wp-content/plugins` directory
1. Go to the plugin management page and enable the plugin
1. Configure the plugin from `Manage/Search Unleashed`

You can find full details of installing a plugin on the [plugin installation page](http://urbangiraffe.com/articles/how-to-install-a-wordpress-plugin/).

== Frequently Asked Questions ==

= How does this work? =

An index is made of all appropriate data, including the output from any plugins.  Typically this does not occur as the standard
WordPress search is made against raw data, as opposed to data that has been processed by plugins.

Only relevant data is included in the index to keep the index as small and fast as possible.

The index is updated anytime a post is edited.

== Screenshots ==

1. Configure what data sources to search
2. Highlighted search results

== Documentation ==

Full documentation can be found on the [Search Unleashed](http://urbangiraffe.com/plugins/search-unleashed/) page.

== Changelog ==

= 0.2.0  = 
* Add search log
* Add search mode

= 0.2.1  = 
* Add Russian translation

= 0.2.2  = 
* Theme compatibility
* Add Yandex search engine
* Prevent empty search results

= 0.2.3  = 
* Ticket #3

= 0.2.4  = 
* Tag option to disable output
* Remove small words from incoming searches
* Add 'post author' module
* Restyle incoming search
* Improve compatibility with other plugins

= 0.2.5  = 
* Fix database problems
* Add Italian translation
* Remove site: from Google

= 0.2.6  = 
* Fix problem with XMLRPC posting not being captured

= 0.2.7  = 
* Fix simple/full setting

= 0.2.8  = 
* Fix bug introduced by 0.2.7

= 0.2.9  = 
* Update German translation
* Add missing localized text

= 0.2.10 = 
* Add Dutch & French translation
* Fix #10

= 0.2.11 = 
* Fix issues #42, #60

= 0.2.12 = 
* Fix issues #71
* Add new feature #2 and #4

= 0.2.13 = 
* Fix issues #87, #88, #91, #94

= 0.2.14 = 
* Fix #114

= 0.2.15 = 
* Add Polish translation
* Fix for #119

= 0.2.16 = 
* WordPress 2.5 fixes
* Fix #153
* Add #117, #99, #97

= 0.2.17 = 
* Updated German language files

= 0.2.18 = 
* WP 2.6

= 0.2.19 = 
* Prevent some JS errors in 2.6
* Update core library for custom config support

= 0.2.20 = 
* Spanish translation

= 0.2.21 = 
* Option to disable search highlighting

= 0.2.22 = 
* Turkish translation

= 0.2.23 = 
* Japanese translation

= 0.2.24 = 
* Chinese & Brazillian translation
* WP 2.7

= 0.2.25 = 
* Fix #350

= 0.2.26 = 
* Danish translation

= 0.2.27 = 
* Fix #477

= 0.2.28 = 
* WP 2.8 compat

= 1.0    = 
* Major rewrite
* Lucene module
* Numerous bug fixes

= 1.0.1 =
* Fix #646
* Updated Danish translation

= 1.0.2 =
* Prevent problem with farbtastic
* Message about no need for indexing with default search engine

= 1.0.4 =
* Remove error with some MySQLs
* Forcing content display bug
* Fix #661§

= 1.0.5 =
* Add Czech translation
* Fix #683
* Fix #692

= 1.0.6 =
* Prevent plugins interfering with reindex
* Misc 

= 1.0.7 =
* Fix 'delete search unleashed option'
* Update for WP 3.1.2
* Don't include files in trash
* Update French translation, thanks to justsev
* Category searching, thanks to Charles Verge
