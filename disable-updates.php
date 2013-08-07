<?php
/**
 * The main plugin file
 *
 * @package WordPress_Plugins
 * @subpackage OS_Disable_WordPress_Updates
 */

/*
Plugin Name: Disable All WordPress Updates
Description: Disables the theme, plugin and core update checking, the related cronjobs and notification system.
Plugin URI:  http://wordpress.org/plugins/disable-wordpress-updates/
Version:     1.3.0.1
Author:      Oliver Schlöbe, originally developed by Tanja Preu&szlig;e 
Author URI:  http://www.schloebe.de/

Copyright 2013 Oliver Schlöbe (email : scripts@schloebe.de)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/



/**
 * Define the plugin version
 */
define("OSDWPUVERSION", "1.3.0.1");


/**
 * The OS_Disable_WordPress_Updates class
 *
 * @package 	WordPress_Plugins
 * @subpackage 	OS_Disable_WordPress_Updates
 * @since 		1.3
 * @author 		scripts@schloebe.de
 */
class OS_Disable_WordPress_Updates {
	/**
	 * The OS_Disable_WordPress_Updates class constructor
	 * initializing required stuff for the plugin
	 *
	 * PHP 4 Compatible Constructor
	 *
	 * @since 		1.3
	 * @author 		scripts@schloebe.de
	 */
	function OS_Disable_WordPress_Updates() {
		$this->__construct();
	}
	
	
	/**
	 * The OS_Disable_WordPress_Updates class constructor
	 * initializing required stuff for the plugin
	 *
	 * PHP 5 Constructor
	 *
	 * @since 		1.3
	 * @author 		scripts@schloebe.de
	 */
	function __construct() {
		add_action('admin_init', array(&$this, 'admin_init'));
		
		/*
		 * Disable Theme Updates
		 * 2.8 to 3.0
		 */
		add_filter( 'pre_transient_update_themes', create_function( '$a', "return null;" ) );
		/*
		 * 3.0
		 */
		add_filter( 'pre_site_transient_update_themes', create_function( '$a', "return null;" ) );
		
		/*
		 * Disable Plugin Updates
		 * 2.8 to 3.0
		 */
		add_action( 'pre_transient_update_plugins', array(&$this, create_function( '$a', "return null;" )) );
		/*
		 * 3.0
		 */
		add_filter( 'pre_site_transient_update_plugins', create_function( '$a', "return null;" ) );
		
		/*
		 * Disable Core Updates
		 * 2.8 to 3.0
		 */
		add_filter( 'pre_transient_update_core', create_function( '$a', "return null;" ) );
		/*
		 * 3.0
		 */
		add_filter( 'pre_site_transient_update_core', create_function( '$a', "return null;" ) );
	}
	
	
	/**
	 * Initialize and load the plugin stuff
	 *
	 * @since 		1.3
	 * @author 		scripts@schloebe.de
	 */
	function admin_init() {
		if ( !function_exists("remove_action") ) return;
	
		/*
		 * Disable Theme Updates
		 * 2.8 to 3.0
		 */
		remove_action( 'load-themes.php', 'wp_update_themes' );
		remove_action( 'load-update.php', 'wp_update_themes' );
		remove_action( 'admin_init', '_maybe_update_themes' );
		remove_action( 'wp_update_themes', 'wp_update_themes' );
		wp_clear_scheduled_hook( 'wp_update_themes' );
		
		/*
		 * 3.0
		 */
		remove_action( 'load-update-core.php', 'wp_update_themes' );
		wp_clear_scheduled_hook( 'wp_update_themes' );
		
		
		/*
		 * Disable Plugin Updates
		 * 2.8 to 3.0
		 */
		remove_action( 'load-plugins.php', 'wp_update_plugins' );
		remove_action( 'load-update.php', 'wp_update_plugins' );
		remove_action( 'admin_init', '_maybe_update_plugins' );
		remove_action( 'wp_update_plugins', 'wp_update_plugins' );
		wp_clear_scheduled_hook( 'wp_update_plugins' );
		
		/*
		 * 3.0
		 */
		remove_action( 'load-update-core.php', 'wp_update_plugins' );
		wp_clear_scheduled_hook( 'wp_update_plugins' );
		
		
		/*
		 * Disable Core Updates
		 * 2.8 to 3.0
		 */
		remove_action( 'wp_version_check', 'wp_version_check' );
		remove_action( 'admin_init', '_maybe_update_core' );
		wp_clear_scheduled_hook( 'wp_version_check' );
		
		
		/*
		 * 3.0
		 */
		wp_clear_scheduled_hook( 'wp_version_check' );
	}
}

if ( class_exists('OS_Disable_WordPress_Updates') && is_admin() ) {
	$OS_Disable_WordPress_Updates = new OS_Disable_WordPress_Updates();
}
?>