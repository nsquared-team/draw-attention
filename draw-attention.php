<?php
/*
@package   DrawAttention
@author    N Squared <support@wpdrawattention.com>
@license   GPL-2.0+
@link      https://wpdrawattention.com
@copyright 2022 N Squared
@wordpress-plugin
Plugin Name:       Draw Attention
Plugin URI:        https://wpdrawattention.com
Description:       Create interactive images in WordPress
Version:           1.9.30
Author:            N Squared
Author URI:        https://nsqua.red
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

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

/*
 * @TODO:
 *
 * - replace `class-drawattention.php` with the name of the plugin's class file
 *
 */
require_once( plugin_dir_path( __FILE__ ) . 'public/class-drawattention.php' );

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

/*----------------------------------------------------------------------------*
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

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-drawattention-admin.php' );
	add_action( 'plugins_loaded', array( 'DrawAttention_Admin', 'get_instance' ) );

}
