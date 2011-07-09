<?php
/**
 * @package Phuksiryhmat number
 * @version 1.0
 */
/*
Plugin Name: Phuksiryhmat number
Plugin URI: http://pkroger.org
Description: 
Author: Pyry Kröger
Version: 1.0
Author URI: http://pkroger.org/
*/

add_filter( 'generate_rewrite_rules', 'add_phuksiryhmat_rewrite' ); 
function add_phuksiryhmat_rewrite($wp_rewrite) {
  $new_rules = array( 
       'lista/phuksit/(.+)' => 'index.php?pagename=phuksit&ryhma=' .
         $wp_rewrite->preg_index(1) );

  //​ Add the new rewrite rule into the top of the global rules array
  $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}

add_filter('query_vars', 'phuksiryhmat_query_vars');
function phuksiryhmat_query_vars($query_vars)
{
    $query_vars[] = 'ryhma';
    $query_vars[] = 'vuosi';
    return $query_vars;
}
?>
