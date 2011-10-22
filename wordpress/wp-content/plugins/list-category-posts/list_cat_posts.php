<?php
/*
Plugin Name: List category posts
Plugin URI: http://picandocodigo.net/programacion/wordpress/list-category-posts-wordpress-plugin-english/
Description: List Category Posts allows you to list posts from a category into a post/page using the [catlist] shortcode. This shortcode accepts a category name or id, the order in which you want the posts to display, and the number of posts to display. You can use [catlist] as many times as needed with different arguments. Usage: [catlist argument1=value1 argument2=value2].
Version: 0.19.1
Author: Fernando Briano
Author URI: http://picandocodigo.net/
*/

/* Copyright 2008-2011  Fernando Briano  (email : fernando@picandocodigo.net)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

include 'include/ListCategoryPostsWidget.php';
require_once 'include/CatListDisplayer.php';

class ListCategoryPosts{
    /**
     * Gets the shortcode parameters and instantiate plugin objects
     * @param $atts
     * @param $content
     */
    function catlist_func($atts, $content = null) {
            $atts = shortcode_atts(array(
                            'id' => '0',
                            'name' => '',
                            'orderby' => 'date',
                            'order' => 'desc',
                            'numberposts' => '5',
                            'date' => 'no',
                            'author' => 'no',
                            'dateformat' => get_option('date_format'),
                            'template' => 'default',
                            'excerpt' => 'no',
                            'exclude' => '0',
                            'excludeposts' => '0',
                            'offset' => '0',
                            'tags' => '',
                            'content' => 'no',
                            'catlink' => 'no',
                            'comments' => 'no',
                            'thumbnail' => 'no',
                            'thumbnail_size' => 'thumbnail',
                            'post_type' => '',
                            'post_parent' => '0',
                            'class' => 'lcp_catlist',
                            'customfield_name' => '',
                            'customfield_value' =>'',
                            'customfield_display' =>''
                    ), $atts);

            
            $catlist_displayer = new CatListDisplayer($atts);
            return $catlist_displayer->display();

    }

}

add_shortcode( 'catlist', array('ListCategoryPosts', 'catlist_func') );

/**
 * TO-DO:
 * From WordPress Answers:
Add Older Posts at bottom of List Category Post page
  http://wordpress.stackexchange.com/questions/26398/add-older-posts-at-bottom-of-list-category-post-page
Include the latest post of a specific category on a matching page
  http://wordpress.stackexchange.com/questions/26224/how-to-include-the-latest-post-of-a-specific-category-on-a-matching-page
Link the thumbnail image
  http://wordpress.stackexchange.com/questions/28431/im-trying-to-link-the-thumbnail-image
- i18n
- Pagination
- Simpler template system
- Exclude child categories
 */