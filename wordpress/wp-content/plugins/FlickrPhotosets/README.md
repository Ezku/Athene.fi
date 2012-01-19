# Flickr Photosets Browser

A simple web app for showing photosets from a Flickr user chronologically.

## Usage

1. Get Flickr API key for your application from http://www.flickr.com/services/apps/create/apply/
2. Get your Flickr user ID by using e.g. http://idgettr.com/
3. Paste the contents of this app to your web app folder
4. Add the following snippet to a script element in your html.
5. Add `<div id="flickrphotos"></div>` to your html

### Snippet to initialize the app

    flickrphotosets.api_key = "your_flickr_api_key";
    flickrphotosets.user_id = "target_user_id";
    
    jQuery(function() {
        flickrphotosets.init('#flickrphotos');
    });

### Widget

This app also provides a simple widget for showing most recent photosets. It 
is suitable for narrower spaces such as sidebar. To use it, replace the snippet 
in step 4 with

      flickrphotosets.api_key = "your_flickr_api_key";
      flickrphotosets.user_id = "target_user_id";
      flickrphotosets.link_url = "link_to_your_proper_gallery_page";

      jQuery(function() {
        flickrphotosets.initWidget('#flickrphotos');
      });

### Wordpress plugin

For historical reasons, this repository also includes a Wordpress plugin. To 
use it, paste the directory to wp-content/plugins and edit plugin settings in
Wordpress settings -> Flickr photosets

### Localization

This app supports very simple localization. To change strings, change the
following options (default values in parentheses):

* flickrphotosets.texts.flickr_link ("This photoset in Flickr")
* flickrphotosets.texts.photos ("photos")
* flickrphotosets.texts.updated ("updated")

Example

    flickrphotosets.texts.flickr_link = "See this photoset in Flickr";


### Depedencies

* jQuery (tested with 1.7.1)
* jQuery scrollTo (1.4.2, http://flesler.blogspot.com/2007/10/jqueryscrollto.html)
* jQuery hashchange (1.3, http://benalman.com/projects/jquery-hashchange-plugin/)
* Fancybox (1.3.4, http://fancybox.net/)
    * jQuery easing (1.3)
    * jQuery mousewheel (3.0.4)

All these are bundled with the app.
 