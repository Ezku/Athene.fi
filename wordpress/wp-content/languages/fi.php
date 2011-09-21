<?php
// Suomenkielisen version kustomointeja

$wp_default_secret_key = 'oma uniikki lauseesi';


// // Näytetään WordPress suomen blogiotsikot dashboardissa.
// function wpfi_rss_dashboard_widget_function() {
// 	$rss = fetch_feed( "http://fi.wordpress.org/feed/" );
// 
// 	if ( is_wp_error($rss) ) {
// 		if ( is_admin() || current_user_can('manage_options') ) {
// 			echo '<p>';
// 			printf(__('<strong>RSS-virhe</strong>: %s'), $rss->get_error_message());
// 			echo '</p>';
// 		}
// 		return;
// 	}
// 
// 	if ( !$rss->get_item_quantity() ) {
// 		echo '<p>Näköjään mitään ei tapahdu blogissa!</p>';
// 		$rss->__destruct();
// 		unset($rss);
// 		return;
// 	}
// 
// 	echo "<ul>\n";
// 
// 	if ( !isset($items) )
// 		$items = 3;
// 
// 	foreach ( $rss->get_items(0, $items) as $item ) {
// 		$publisher = '';
// 		$site_link = '';
// 		$link = '';
// 		$content = '';
// 		$date = '';
// 		$link = esc_url( strip_tags( $item->get_link() ) );
// 		$title = strip_tags( $item->get_title() );
// 
// 		$content = $item->get_content();
// 		$content = wp_html_excerpt($content, 250) . ' ...';
// 
// 		echo "\t<li><a href='$link'>$title</a> - $content</li>\n";
// 	}
// 
// 	echo "</ul>\n";
// 	$rss->__destruct();
// 	unset($rss);
// };
// 
// //Function to add the rss feed to the dashboard.
// function wpfi_rss_add_dashboard_widget() {
// 	wp_add_dashboard_widget('wpfi_rss_dashboard_widget', 'WordPress Suomi', 'wpfi_rss_dashboard_widget_function');
// }
// 
// //Action that calls the function that adds the widget to the dashboard.
// add_action('wp_dashboard_setup', 'wpfi_rss_add_dashboard_widget');