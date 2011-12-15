<?php
/**
 * @author John Godley
 * @copyright Copyright (C) John Godley
 **/

/*
============================================================================================================
This software is provided "as is" and any express or implied warranties, including, but not limited to, the
implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no event shall
the copyright owner or contributors be liable for any direct, indirect, incidental, special, exemplary, or
consequential damages (including, but not limited to, procurement of substitute goods or services; loss of
use, data, or profits; or business interruption) however caused and on any theory of liability, whether in
contract, strict liability, or tort (including negligence or otherwise) arising in any way out of the use of
this software, even if advised of the possibility of such damage.

For full license details see license.txt
============================================================================================================ */

if ( class_exists( 'WP_Widget' ) ) {
	/**
	 * Search Unleashed advanced widget
	 *
	 * @package default
	 **/
	class SU_Widget_Search extends WP_Widget {
		function SU_Widget_Search() {
			$widget_ops = array( 'classname' => 'widget_search', 'description' => __( 'An extended search form', 'search-unleashed' ) );

			$this->WP_Widget( 'searchunleashed', __( 'Search Unleashed', 'search-unleashed' ), $widget_ops );
		}

		/**
		 * Display the widget
		 *
		 * @param string $args Widget arguments
		 * @param string $instance Widget instance
		 * @return void
		 **/
		function widget( $args, $instance ) {
			extract($args);
		
			$instance = wp_parse_args( (array)$instance, array( 'title' => '', 'category' => false, 'tags' => false, 'meta' => false, 'advanced' => false ) );
			$title    = apply_filters('widget_title', $instance['title'] );
			$category = $instance['category'];
			$tags     = $instance['tags'];
			$meta     = $instance['meta'];
			$advanced = $instance['advanced'];

			echo $before_widget;
		
			if ( $title )
				echo $before_title.$title.$after_title;
	?>
		<form role="search" method="get" id="searchform" name="searchform" action="<?php echo get_option('home') ?>/" >
			<div>
				<input class="text" type="text" value="<?php echo htmlspecialchars( apply_filters('the_search_query', get_search_query() ) ) ?>" name="s" id="s" />
				<input class="button-secondary" type="submit" id="searchsubmit" value="<?php _e('Search') ?>" />
				<?php if ( $category || $tags || $meta ) : ?>
					<br/>
					<?php if ( $advanced ) : ?>
						<p style="text-align: center" class="advanced"><small><a href="#advanced" onclick="document.getElementById('advanced-search').style.display=''; return false;"><?php _e( 'Advanced', 'search-unleashed' )?></a></small></p>
						<div style="display: none; margin-top: 12px" id="advanced-search">
					<?php else : ?>
						<div style="margin-top: 12px" id="advanced-search">
					<?php endif; ?>
				
						<?php if ( $tags ) : ?>
						<p><input class="text" type="text" name="tags" value="<?php echo isset( $_GET['post_tags']) ? htmlspecialchars( $_GET['post_tags'] ) : ''; ?>" /> <?php _e( 'Tags', 'search-unleashed' ); ?></p>
						<?php endif; ?>
				
						<?php if ( $meta ) : ?>
						<p><input class="text" type="text" name="meta" value="<?php echo isset( $_GET['post_meta']) ? htmlspecialchars( $_GET['post_meta'] ) : ''; ?>" /> <?php _e( 'Meta', 'search-unleashed' ); ?></p>
						<?php endif; ?>
				
						<?php if ( $category ) {
							$cats = wp_dropdown_categories( apply_filters( 'widget_category_dropdown', array( 'echo' => false, 'hierarchical' => true, 'name' => 'cat', 'show_option_none' => __( 'None', 'search-unleashed' ) ) ) );
					
							echo '<p>'.$cats.__( 'Category', 'search-unleashed' ).'</p>';
						}
						?>
					</div>
				<?php endif; ?>
			</div>
		</form>
	<?php

			echo $after_widget;
		}

		/**
		 * Display config interface
		 *
		 * @param string $instance Widget instance
		 * @return void
		 **/
		function form( $instance ) {
			$instance = wp_parse_args( (array)$instance, array( 'title' => '', 'category' => false, 'tags' => false, 'meta' => false, 'advanced' => false ) );
			$title    = $instance['title'];
			$category = $instance['category'];
			$tags     = $instance['tags'];
			$meta     = $instance['meta'];
			$advanced = $instance['advanced'];
		
			?>
	<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
	<p><label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Show categories:', 'search-unleashed'); ?> <input id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>" type="checkbox" <?php if ( $category ) echo ' checked="checked"'; ?> /></label></p>
	<p><label for="<?php echo $this->get_field_id('tags'); ?>"><?php _e('Show tags:', 'search-unleashed'); ?> <input id="<?php echo $this->get_field_id('tags'); ?>" name="<?php echo $this->get_field_name('tags'); ?>" type="checkbox" <?php if ( $tags ) echo ' checked="checked"'; ?> /></label></p>
	<p><label for="<?php echo $this->get_field_id('meta'); ?>"><?php _e('Show meta:', 'search-unleashed'); ?> <input id="<?php echo $this->get_field_id('meta'); ?>" name="<?php echo $this->get_field_name('meta'); ?>" type="checkbox" <?php if ( $meta ) echo ' checked="checked"'; ?> /></label></p>
	<p><label for="<?php echo $this->get_field_id('advanced'); ?>"><?php _e('Advanced link:', 'search-unleashed' ); ?> <input id="<?php echo $this->get_field_id('advanced'); ?>" name="<?php echo $this->get_field_name('advanced'); ?>" type="checkbox" <?php if ( $advanced ) echo ' checked="checked"'; ?> /></label></p>
			<?php
		}

		/**
		 * Save widget data
		 *
		 * @param string $new_instance
		 * @param string $old_instance
		 * @return void
		 **/
		function update( $new_instance, $old_instance ) {
			$instance     = $old_instance;
			$new_instance = wp_parse_args( (array)$new_instance, array( 'title' => '', 'category' => false, 'tags' => false, 'meta' => false, 'advanced' => false ) );
		
			$instance['title']    = strip_tags($new_instance['title']);
			$instance['category'] = $new_instance['category'] ? true : false;
			$instance['tags']     = $new_instance['tags'] ? true : false;
			$instance['meta']     = $new_instance['meta'] ? true : false;
			$instance['advanced'] = $new_instance['advanced'] ? true : false;
		
			return $instance;
		}
	}


	/**
	 * Create the widget
	 *
	 * @param string
	 * @return void
	 **/
	function su_widgets_init() {
		register_widget('SU_Widget_Search');
	}
 
	add_action('init', 'su_widgets_init', 1);
}