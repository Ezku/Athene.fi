<?php
/*
Plugin Name: Monkeyman Rewrite Analyzer
Description: Display and play with your rewrite rules
Version: 1.0
Author: Jan Fabry
Author URI: http://www.monkeyman.be
Plugin URI: http://wordpress.stackexchange.com/q/3606/8
Text Domain: monkeyman-rewrite-analyzer
Domain Path: /languages
License: GPL


Copyright 2011 Jan Fabry <jan.fabry@monkeyman.be>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, version 2.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


/*
This construct allows you to symlink this directory into your plugin directory. See the explanation at http://wordpress.stackexchange.com/questions/15202/plugins-in-symlinked-directories

To run this as a mu-plugin, place the following code in a file in the 'mu-plugin' directory:

if ( isset( $mu_plugin ) ) {
	$monkeyman_Rewrite_Analyzer_file = dirname( $mu_plugin ) . '/monkeyman-rewrite-analyzer/rewrite-analyzer.php';
	include_once( $monkeyman_Rewrite_Analyzer_file );
}
*/
if ( ! isset( $monkeyman_Rewrite_Analyzer_file ) ) {
	$monkeyman_Rewrite_Analyzer_file = __FILE__;
	if ( isset( $network_plugin ) ) {
		$monkeyman_Rewrite_Analyzer_file = $network_plugin;
	}
	if ( isset( $plugin ) ) {
		$monkeyman_Rewrite_Analyzer_file = $plugin;
	}
}

if ( is_admin() ) {
	include_once( dirname( __FILE__ ) . '/include/class-monkeyman-rewrite-analyzer.php' );
	
	add_action( 'plugins_loaded', 'monkeyman_rewrite_analyzer_load' );
}

function monkeyman_rewrite_analyzer_load()
{
	$GLOBALS['monkeyman_Rewrite_Analyzer_instance'] = new Monkeyman_Rewrite_Analyzer( $GLOBALS['monkeyman_Rewrite_Analyzer_file'] );
}