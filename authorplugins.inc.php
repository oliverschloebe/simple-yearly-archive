<?php
/**
 * File that holds all the author plugins functions
 *
 * @package WordPress_Plugins
 * @subpackage SimpleYearlyArchive
 */


/**
 * Writes CSS and JS to the plugin page's header for displaying my other plugins
 *
 * @since 1.1.1
 * @author scripts@schloebe.de
 */
function sya_authorplugins_head() {
	wp_enqueue_script( 'os_authorplugins_script', SYA_PLUGINFULLURL . "js/os_authorplugins_script.js", array('jquery'), SYA_VERSION );
	$sya_authorplugins_style  = "\n<link rel='stylesheet' href='" . SYA_PLUGINFULLURL . "css/os_authorplugins_style.css' type='text/css' media='all' />\n";
	print( $sya_authorplugins_style );
}

/**
 * Plugin credits in WP footer
 *
 * @since 1.1.1
 * @author scripts@schloebe.de
 */
function sya_plugin_footer() {
	$plugin_data = get_plugin_data( SYA_PLUGINFULLDIR . 'simple-yearly-archive.php' );
	$plugin_data['Title'] = $plugin_data['Name'];
	if ( !empty($plugin_data['Plugin URI']) && !empty($plugin_data['Name']) )
		$plugin_data['Title'] = '<a href="' . $plugin_data['Plugin URI'] . '" title="'.__( 'Visit plugin homepage' ).'">' . $plugin_data['Name'] . '</a>';
	
	if ( basename($_SERVER['REQUEST_URI']) == 'simple-yearly-archive.php' ) {
		printf('%1$s ' . __('plugin') . ' | ' . __('Version') . ' <a href="http://www.schloebe.de/wordpress/simple-yearly-archive-plugin/" title="">%2$s</a> | ' . __('Author') . ' %3$s<br />', $plugin_data['Title'], $plugin_data['Version'], $plugin_data['Author']);
	}
}

/**
 * Initialization of author plugins stuff
 *
 * @since 1.1.1
 * @author scripts@schloebe.de
 */
function sya_authorplugins_init() {
	global $wp_version;
	if( version_compare($wp_version, '2.4.999', '>=') ) {
		add_action('in_admin_footer', 'sya_plugin_footer');
	}
}

if ( basename($_SERVER['REQUEST_URI']) == 'simple-yearly-archive.php' ) {
	add_action( "admin_print_scripts", 'sya_authorplugins_head' );
}
add_action( 'admin_init', 'sya_authorplugins_init', 1 );
?>