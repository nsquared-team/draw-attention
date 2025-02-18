<?php
/*
@package   DrawAttention
@author    N Squared <support@wpdrawattention.com>
@license   GPL-2.0+
@link      https://wpdrawattention.com
@copyright 2024 NSquared
@wordpress-plugin
Plugin Name:       Draw Attention
Plugin URI:        https://wpdrawattention.com
Description:       Create interactive images in WordPress
Version:           2.0.31
Author:            NSquared
Author URI:        https://nsquared.io
Text Domain:       draw-attention
License:           GPL-2.0+
License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
Domain Path:       /languages
GitHub Plugin URI: https://github.com/tylerdigital/draw-attention
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*
----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

/*
 * @TODO:
 *
 * - replace `class-drawattention.php` with the name of the plugin's class file
 *
 */
require_once plugin_dir_path( __FILE__ ) . 'public/class-drawattention.php';

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *
 * @TODO:
 *
 * - replace DrawAttention with the name of the class defined in
 *   `class-drawattention.php`
 */
register_activation_hook( __FILE__, array( 'DrawAttention', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'DrawAttention', 'deactivate' ) );

/*
 * @TODO:
 *
 * - replace DrawAttention with the name of the class defined in
 *   `class-drawattention.php`
 */
add_action( 'plugins_loaded', array( 'DrawAttention', 'get_instance' ) );

/*
----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * @TODO:
 *
 * - replace `class-drawattention-admin.php` with the name of the plugin's admin file
 * - replace DrawAttention_Admin with the name of the class defined in
 *   `class-drawattention-admin.php`
 *
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once plugin_dir_path( __FILE__ ) . 'admin/class-drawattention-admin.php';
	add_action( 'plugins_loaded', array( 'DrawAttention_Admin', 'get_instance' ) );

}



/**
 * Responsive Image Maps plugin attempts to take over any image map on the page, which interferes with our plugin’s ability to resize and place hotspots correctly.
 * Please check your list of active plugins to see if you have the Responsive Image Maps plugin installed and activated. If so, we regretfully recommend deactivating this plugin.
 * It has been abandoned and has not been updated since 2015.
 * Here is a link to our public ticket asking the plugin author to update their plugin: https://wordpress.org/support/topic/conflict-with-draw-attention-plugin-2/
 *
 * Our final solution is to dequeue the Responsive Image Maps plugin’s script if it is active.
 * And tell WordPress to not load RIM plugin
 */
function da_dequeue_conflicting_rim_scripts() {

	if ( ! function_exists( 'pn_rim_enqueue_scripts' ) ) {
		return;
	}

	wp_dequeue_script( 'jQuery.rwd_image_maps' );
}

function da_disable_rim_plugin() {

	if ( ! function_exists( 'pn_rim_enqueue_scripts' ) ) {
		return;
	}

	remove_action( 'wp_head', 'pn_rim_header_scripts' );
}

add_action( 'init', 'da_disable_rim_plugin' );
add_action( 'wp_enqueue_scripts', 'da_dequeue_conflicting_rim_scripts', 999 );
