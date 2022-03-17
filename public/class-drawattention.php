<?php
/**
 * Plugin Name.
 *
 * @package   DrawAttention
 * @author    Nathan Tyler <support@wpdrawattention.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2022 N Squared
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-drawattention-admin.php`
 *
 * @TODO: Rename this class to a proper name for your plugin.
 *
 * @package DrawAttention
 * @author  Nathan Tyler <support@wpdrawattention.com>
 */
if ( !class_exists( 'DrawAttention' ) ) {
	class DrawAttention {

		/**
		 * Plugin version, used for cache-busting of style and script file references.
		 *
		 * @since   1.0.0
		 *
		 * @var     string
		 */
		const VERSION = '1.9.30';
		const file = __FILE__;
		const name = 'Draw Attention';
		const slug = 'drawattention';
		/**
		 * @TODO - Rename "hotspots" to the name of your plugin
		 *
		 * Unique identifier for your plugin.
		 *
		 *
		 * The variable name is used as the text domain when internationalizing strings
		 * of text. Its value should match the Text Domain file header in the main
		 * plugin file.
		 *
		 * @since    1.0.0
		 *
		 * @var      string
		 */
		public $plugin_slug = 'drawattention';

		/**
		 * Instance of this class.
		 *
		 * @since    1.0.0
		 *
		 * @var      object
		 */
		protected static $instance = null;

		/**
		 * Instance of class to register CPT and taxonomies
		 * @var DrawAttention_CPT
		 */
		public $cpt;
		public $custom_fields;
		public $pro;
		public $themes;
		public $block_image;
		public $photon_excluded_images = array();

		/**
		 * Initialize the plugin by setting localization and loading public scripts
		 * and styles.
		 *
		 * @since     1.0.0
		 */
		private function __construct() {
			add_filter( 'da_description', 'wpautop' );

			// Load plugin text domain
			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

			// Activate plugin when new blog is added
			add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

			// Load public-facing style sheet and JavaScript.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// Shortcode for displaying the image map
			add_shortcode( 'drawattention', array( $this, 'shortcode' ) );

			add_action( 'admin_notices', array( $this, 'php_52_notice' ) );

			add_action( 'add_meta_boxes', array( $this, 'add_shortcode_metabox' ) );

			add_action( 'template_include', array( $this, 'single_template' ) );

			add_filter( 'jetpack_photon_skip_image', array ($this, 'jetpack_photon_skip_image' ), 10, 3 );

			add_filter( 'cmb2_meta_box_url', array( $this, 'cmb2_meta_box_url' ) );

		/**
		 * @TODO - Uncomment requried features
		 *
		 * Various functionality is separated into external files
		 */
			include_once( 'includes/cpt.php' );
			$this->cpt = new DrawAttention_CPT( $this );

			include_once( 'includes/custom_fields.php' );
			$this->custom_fields = new DrawAttention_CustomFields( $this );

			include_once( 'includes/themes.php' );
			$this->themes = new DrawAttention_Themes( $this );

			include_once( 'includes/class-block-image.php' );
			$this->block_image = new DrawAttention_Block_Image( $this );

			include_once( 'includes/import-export.php' );
			$this->import_export = new DrawAttention_ImportExport( $this );
		}

		/**
		 * Return the plugin slug.
		 *
		 * @since    1.0.0
		 *
		 * @return    Plugin slug variable.
		 */
		public function get_plugin_slug() {
			return $this->plugin_slug;
		}

		/**
		 * Return an instance of this class.
		 *
		 * @since     1.0.0
		 *
		 * @return    object    A single instance of this class.
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * Fired when the plugin is activated.
		 *
		 * @since    1.0.0
		 *
		 * @param    boolean    $network_wide    True if WPMU superadmin uses
		 *                                       "Network Activate" action, false if
		 *                                       WPMU is disabled or plugin is
		 *                                       activated on an individual blog.
		 */
		public static function activate( $network_wide ) {

			if ( function_exists( 'is_multisite' ) && is_multisite() ) {

				if ( $network_wide  ) {

					// Get all blog ids
					$blog_ids = self::get_blog_ids();

					foreach ( $blog_ids as $blog_id ) {

						switch_to_blog( $blog_id );
						self::single_activate();

						restore_current_blog();
					}

				} else {
					self::single_activate();
				}

			} else {
				self::single_activate();
			}

		}

		/**
		 * Fired when the plugin is deactivated.
		 *
		 * @since    1.0.0
		 *
		 * @param    boolean    $network_wide    True if WPMU superadmin uses
		 *                                       "Network Deactivate" action, false if
		 *                                       WPMU is disabled or plugin is
		 *                                       deactivated on an individual blog.
		 */
		public static function deactivate( $network_wide ) {

			if ( function_exists( 'is_multisite' ) && is_multisite() ) {

				if ( $network_wide ) {

					// Get all blog ids
					$blog_ids = self::get_blog_ids();

					foreach ( $blog_ids as $blog_id ) {

						switch_to_blog( $blog_id );
						self::single_deactivate();

						restore_current_blog();

					}

				} else {
					self::single_deactivate();
				}

			} else {
				self::single_deactivate();
			}

		}

		/**
		 * Fired when a new site is activated with a WPMU environment.
		 *
		 * @since    1.0.0
		 *
		 * @param    int    $blog_id    ID of the new blog.
		 */
		public function activate_new_site( $blog_id ) {

			if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
				return;
			}

			switch_to_blog( $blog_id );
			self::single_activate();
			restore_current_blog();

		}

		/**
		 * Get all blog ids of blogs in the current network that are:
		 * - not archived
		 * - not spam
		 * - not deleted
		 *
		 * @since    1.0.0
		 *
		 * @return   array|false    The blog ids, false if no matches.
		 */
		private static function get_blog_ids() {

			global $wpdb;

			// get an array of blog ids
			$sql = "SELECT blog_id FROM $wpdb->blogs
				WHERE archived = '0' AND spam = '0'
				AND deleted = '0'";

			return $wpdb->get_col( $sql );

		}

		/**
		 * Fired for each blog when the plugin is activated.
		 *
		 * @since    1.0.0
		 */
		private static function single_activate() {
			// @TODO: Define activation functionality here
			flush_rewrite_rules();
		}

		/**
		 * Fired for each blog when the plugin is deactivated.
		 *
		 * @since    1.0.0
		 */
		private static function single_deactivate() {
			// @TODO: Define deactivation functionality here
		}

		/**
		 * Load the plugin text domain for translation.
		 *
		 * @since    1.0.0
		 */
		public function load_plugin_textdomain() {


			$domain = 'drawattention';
			$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

			load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
			load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

			$domain = 'draw-attention';
			$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

			load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
			load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

		}

		/**
		 * Register and enqueue public-facing style sheet.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_styles() {
			wp_register_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
		}

		/**
		 * Register and enqueues public-facing JavaScript files.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_scripts() {
			wp_register_script( $this->plugin_slug . '-leaflet', plugins_url( 'assets/js/leaflet.js', __FILE__ ), array(), '1.5.1', $in_footer = true );
			wp_register_script( $this->plugin_slug . '-leaflet-responsive-popup', plugins_url( 'assets/js/leaflet.responsive.popup-min.js', __FILE__ ), array( $this->plugin_slug . '-leaflet' ), '0.6.4', $in_footer = true );
			wp_register_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( $this->plugin_slug . '-leaflet-responsive-popup', 'jquery' ), self::VERSION, true );

			wp_localize_script( $this->plugin_slug . '-plugin-script', 'drawattentionData', array(
				'isLoggedIn' => is_user_logged_in(),
				'closeLabel' => __('Close', 'draw-attention'),
				'isAdmin' => current_user_can( 'administrator' ),
			) );

			$enqueue = apply_filters( 'da_enqueue_scripts_everywhere', false );
			if ( !empty( $enqueue ) ) {
				wp_enqueue_script( $this->plugin_slug . '-plugin-script' );
			}
		}

		function php_52_notice() {
			global $pagenow;
			if ( $pagenow != 'post.php' ) return;
			if ( get_post_type() != 'da_image' ) return;

			if ( version_compare( phpversion(), '5.2.99') <= 0 ) {
				$class = "error";
				$message = "<p>
				<h3>Your server is out of date</h3>
				Draw Attention (and many other WP plugins) <strong>requires PHP version 5.3 or higher</strong>. PHP 5.2 was released back in 2006 and support was officially terminated in 2011.
				</p>
				<p>

				<h3>Please contact your hosting company and ask to be upgraded to PHP 5.3 or higher</h3>
				<p>Most hosts run PHP 5.5+, there shouldn't be any charge for this upgrade. If your host won't upgrade your PHP version, it's worth considering another host since there are
				also security implications to running outdated PHP versions. If you contact us at <a href='mailto: support@wpdrawattention.com'>support@wpdrawattention.com</a> we'll be happy to provide
				you with a list of hosts who run PHP 5.3+ and will help you migrate your site from your current hosting provider.</p>
				<h4>Additional info:</h4>
				<ul>
					<li><a href='http://w3techs.com/technologies/details/pl-php/5/all'>http://w3techs.com/technologies/details/pl-php/5/all</a></li>
					<li><a href='http://php.net/releases/'>http://php.net/releases/</a></li>
				</ul>
				</p>";
				echo"<div class=\"$class\"> <p>$message</p></div>";
			}
		}

		public function cmb2_meta_box_url( $url ) {
			if ( ! function_exists( 'get_current_screen' ) ) {
				return $url;
			}

			$screen = get_current_screen();
			if ( 'page' == $screen->post_type ) {
				return $url;
			}

			$url = self::get_plugin_url().'/public/includes/lib/CMB2/';
			return $url;
		}

		public function jetpack_photon_skip_image( $val, $src, $tag ) {
			foreach ($this->photon_excluded_images as $key => $photon_excluded_image) {
				if ( strpos( $src, $photon_excluded_image ) !== false ) {
					return true;
				}
			}

			return $val;
		}

		/**
		 * Shortcode for displaying the image map
		 *
		 * @since    1.0.0
		 */
		public function shortcode( $atts ) {
			// Begin settings array
			$settings = array(
				'has_photon' => class_exists( 'Jetpack_Photon' ),
				'url_hotspots' => array(),
				'urls_only' => false,
				'urls_class' => '',
			);

			// Get the DA image ID
			$image_args = array(
				'post_status' => 'any',
				'post_type' => $this->cpt->post_type,
				'posts_per_page' => 1,
				'order' => 'DESC',
				'orderby' => 'ID',
			);
			$image = new WP_Query($image_args);
			if ( ! empty( $image->post ) ) {
				$settings['image_id'] = $image->post->ID;
			} else {
				$latest_da = get_posts('post_type=' . $this->cpt->post_type . '&numberposts=1');
				$settings['image_id'] = $latest_da[0]->ID;
			}


			// WPML Support
			if ( function_exists ( 'icl_object_id' ) ) {
				$settings['image_id'] = icl_object_id($settings['image_id'], 'da_image', true);
			}

			// Get and set DA settings
			$settings['img_settings'] = get_metadata( 'post', $settings['image_id'], '', false );
			if ( empty( $settings['img_settings']['_da_map_more_info'] ) ) {
				$settings['img_settings']['_da_map_more_info'] = array( '' );
			}
			$settings['spot_id'] = 'hotspot-' . $settings['image_id'];

			// Add hotspots to settings
			$settings['hotspots'] = get_post_meta( $settings['image_id'], $this->custom_fields->prefix . 'hotspots', true );
			$settings['hotspots'] = apply_filters( 'da_render_hotspots', $settings['hotspots'], $settings['image_id'] );
			if ( empty( $settings['hotspots'] ) ) {
				$settings['url_hotspots'] = array();
			} else {
				$settings['url_hotspots'] = array_filter($settings['hotspots'], function($var){
					if ( empty( $var['action'] ) ) {
						return false;
					}

					return $var['action'] == 'url';
				});
				if ( count( $settings['hotspots'] ) == count( $settings['url_hotspots'] ) ) {
					$settings['urls_only'] = true;
					$settings['urls_class'] = 'links-only';
				}
			}

			// Set default values for free settings
			$settings['layout'] = 'left';
			$settings['event_trigger'] = 'click';
			$settings['always_visible'] = 'false';

			// Add styles to settings
			$settings['border_width'] = $settings['img_settings'][$this->custom_fields->prefix.'map_border_width'][0];
			$settings['border_opacity'] = $settings['img_settings'][$this->custom_fields->prefix.'map_border_opacity'][0];
			$settings['more_info_bg'] = ( !empty( $settings['img_settings'][$this->custom_fields->prefix.'map_background_color'][0] ) ) ? $settings['img_settings'][$this->custom_fields->prefix.'map_background_color'][0] : '';
			$settings['more_info_text'] = ( !empty( $settings['img_settings'][$this->custom_fields->prefix.'map_text_color'][0] ) ) ? $settings['img_settings'][$this->custom_fields->prefix.'map_text_color'][0] : '';
			$settings['more_info_title'] = ( !empty( $settings['img_settings'][$this->custom_fields->prefix.'map_title_color'][0] ) ) ? $settings['img_settings'][$this->custom_fields->prefix.'map_title_color'][0] : '';
			$settings['img_bg'] = ( !empty( $settings['img_settings'][$this->custom_fields->prefix.'image_background_color'][0] ) ) ? $settings['img_settings'][$this->custom_fields->prefix.'image_background_color'][0] : '#efefef';

			// Create hotspot style
			if ( empty( $settings['styles'] ) ) {
				$settings['styles'] = array();
			}
			$settings['styles'][] = array(
				'title' => 'default',
				'map_highlight_color' => !empty( $settings['img_settings'][$this->custom_fields->prefix.'map_highlight_color'][0] ) ? $settings['img_settings'][$this->custom_fields->prefix.'map_highlight_color'][0] : '',
				'map_highlight_opacity' => !empty( $settings['img_settings'][$this->custom_fields->prefix.'map_highlight_opacity'][0] ) ? $settings['img_settings'][$this->custom_fields->prefix.'map_highlight_opacity'][0] : '',
				'map_border_color' => !empty( $settings['img_settings'][$this->custom_fields->prefix.'map_border_color'][0] ) ? $settings['img_settings'][$this->custom_fields->prefix.'map_border_color'][0] : '',
				'_da_map_hover_color' => !empty( $settings['img_settings'][$this->custom_fields->prefix.'map_hover_color'][0] ) ? $settings['img_settings'][$this->custom_fields->prefix.'map_hover_color'][0] : '',
				'_da_map_hover_opacity' => !empty( $settings['img_settings'][$this->custom_fields->prefix.'map_hover_opacity'][0] ) ? $settings['img_settings'][$this->custom_fields->prefix.'map_hover_opacity'][0] : ''
			);

			// Create formatted array of styles
			$formatted_styles = array();
			foreach ($settings['styles'] as $key => $style) {
				if ( empty( $style['title'] ) ) {
					$style['title'] = 'Custom';
				}

				$new_style = array(
					'name' => 'default',
					'borderWidth' => $settings['border_width'],
				);

				$new_style['display'] = array(
					'fillColor' => '#ffffff',
					'fillOpacity' => 0,
					'borderColor' => '#ffffff',
					'borderOpacity' => 0,
				);
				$new_style['hover'] = array(
					'fillColor' => $style['map_highlight_color'],
					'fillOpacity' => $style['map_highlight_opacity'],
					'borderColor' => $style['map_border_color'],
					'borderOpacity' => $settings['border_opacity'],
				);
				array_push($formatted_styles, $new_style);
			}

			// Get image post, src, and meta
			$settings['img_post'] = get_post($settings['image_id']);
			$settings['img_src'] = wp_get_attachment_image_src( get_post_thumbnail_id( $settings['image_id'] ), 'full' );
			$settings['img_url'] = $settings['img_src'][0];
			$settings['img_width'] = $settings['img_src'][1];
			$settings['img_height'] = $settings['img_src'][2];
			$settings['img_alt'] = get_post_meta( get_post_thumbnail_id( $settings['img_post'] ), '_wp_attachment_image_alt', true );
			if ( empty( $settings['img_alt'] ) ) {
				$settings['img_alt'] = get_the_title( $settings['img_post'] );
			}

			// Enqueue CSS and Scripts
			wp_enqueue_style( $this->plugin_slug . '-plugin-styles' );
			wp_enqueue_script( $this->plugin_slug . '-plugin-script' );

			$this->photon_excluded_images[ $settings['image_id'] ] = $settings['img_url'];
			// Create a new embed
			$wp_embed = new WP_Embed();

			ob_start();

			require( $this->get_plugin_dir() . '/public/views/shortcode_template.php' );

			return ob_get_clean();
		}

		function add_shortcode_metabox() {
			add_meta_box( 'da_shortcode', __('Copy Shortcode', 'draw-attention' ), array( $this, 'display_shortcode_metabox' ), $this->cpt->post_type, 'side', 'low');
		}

		function display_shortcode_metabox() {
			echo '[drawattention]';
		}

		public function single_template( $template ) {
			if ( is_singular( $this->cpt->post_type ) ) {
				$template = self::locate_template( 'single-da_image.php' );
			}

			return $template;
		}

		public static function locate_template( $template_name, $template_path = '', $default_path = '' ) {
			if ( ! $template_path ) {
				$template_path = self::template_path();
			}

			if ( ! $default_path ) {
				$default_path = self::get_plugin_dir() . '/public/views/';
			}

			// Look within passed path within the theme - this is priority
			$template = locate_template(
				array(
					trailingslashit( $template_path ) . $template_name,
					$template_name
					)
				);

			// Get default template
			if ( ! $template ) {
				$template = $default_path . $template_name;
			}
			// Return what we found
			return apply_filters( self::slug.'_locate_template', $template, $template_name, $template_path );
		}

		public static function get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
			if ( $args && is_array( $args ) ) {
				extract( $args );
			}

			$located = self::locate_template( $template_name, $template_path, $default_path );

			if ( ! file_exists( $located ) ) {
				_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '2.1' );
				return;
			}

			// Allow 3rd party plugin filter template file from their plugin
			$located = apply_filters( self::slug.'get_template', $located, $template_name, $args, $template_path, $default_path );

			do_action( self::slug.'_before_template_part', $template_name, $template_path, $located, $args );

			include( $located );

			do_action( self::slug.'_after_template_part', $template_name, $template_path, $located, $args );
		}

		public static function get_template_part( $slug, $name = '' ) {
			$template = '';

			// Look in yourtheme/slug-name.php and yourtheme/drawattention/slug-name.php
			if ( $name ) {
				$template = locate_template( array( "{$slug}-{$name}.php", self::template_path() . "{$slug}-{$name}.php" ) );
			}

			// Get default slug-name.php
			if ( ! $template && $name && file_exists( self::get_plugin_dir() . "/templates/{$slug}-{$name}.php" ) ) {
				$template = self::get_plugin_dir() . "/templates/{$slug}-{$name}.php";
			}

			// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/drawattention/slug.php
			if ( ! $template ) {
				$template = locate_template( array( "{$slug}.php", self::template_path() . "{$slug}.php" ) );
			}

			// Allow 3rd party plugin filter template file from their plugin
			if ( $template ) {
				$template = apply_filters( self::slug.'_get_template_part', $template, $slug, $name );
			}

			if ( $template ) {
				load_template( $template, false );
			}
		}

		public static function template_path() {
			return self::slug . '/';
		}

		public static function get_plugin_dir() {
			return dirname( dirname( __FILE__ ) );
		}

		public static function get_plugin_url() {
			return dirname( plugin_dir_url( __FILE__ ) );
		}

	}
} elseif ( function_exists( 'da_deactivate_free_version' ) ) {
	add_action( 'init', 'da_deactivate_free_version' );
}
