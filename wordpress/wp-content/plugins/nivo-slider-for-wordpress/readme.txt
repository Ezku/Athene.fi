=== Nivo Slider for WordPress ===
Donate link: http://www.marcelotorresweb.com/nivo-slider-for-wordpress/
Tags: Nivo Slider, jquery, slide, javascript, animation, banner
Requires at least: 3.0
Tested up to: 3.2.1
Stable tag: 0.3.3
Contributors: marcelotorres

Plugin to manage and generate a nice image sliding effect in your site.

== Description ==

Nivo Slider for WordPress plugin is based on S3Slider developed by Vinicius Massuchetto, adapted for their use <a href="http://nivo.dev7studios.com/">JQuery Nivo Slider</a>.

1. Upload and crop images, define captions and links;
2. Set the size, order, timout of the slider, transition effect, slide transition speed, Pause time between the transitions, background color caption, text color caption, opacity background color caption;
3. Enable or disable navigation arrows, use left & right on keyboard, Show only the navigation arrows when mouse is on the slide, Stop animation while the mouse is on the slide, Force manual transitions;
4. Edit a text to be shown on each image;

== Installation ==

It's easy to install.

1. Extract `nivoslider4wp.zip` and upload the folder `nivoslider4wp` to the `/wp-content/plugins/` directory;
2. Activate the plugin through the `Plugins` menu in WordPress
3. Place this `<?php if (function_exists('nivoslider4wp_show')) { nivoslider4wp_show(); } ?>` in your templates

== Frequently Asked Questions ==

Nothing here until now..

== Screenshots ==

1. Panel to add and configure the images.
2. Panel set the options Nivo Slider.
3. Nivo Slider in running.

== Changelog ==

= 0.1 =

* Plugin released, that's it.

= 0.2 =

* fixed in the images order.

= 0.3 =

* Fixed in the plugins(Nivo Slider and JPicker) images.
* Updated jQuery Nivo Slider for version v2.5.1
* Added news effects - boxRandom, boxRain, boxRainReverse, boxRainGrow, boxRainGrowReverse
* Fixed and added other small bugs and updates

= 0.3.1 =

* [IMPORTANT] problem of exclusion images in the folder "files " after the update was corrected, the folder "files" was be replaced by "nivoslider4wp_files" out of plugin folder, the folder "nivoslider4wp_files" is located in "wp-content". Copy images of folder "files" to folder "nivoslider4wp_files".

= 0.3.2 =

* Added option for choose where will be loading of javascript(head or footer)
* Added option for choose the images quality in cutting
* Added JQuery noConflict in script of Nivo Slider
* ...And fixed other small bugs and updates

= 0.3.3 =

* Added desable image option
* Enqueue jquery correctly
* Update NivoSlider JQuery Plugin
* Automatic placing the folder 'nivoslider4wp_files' in the folder 'uploads'