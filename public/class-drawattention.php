<?php
/**
 * Plugin Name.
 *
 * @package   DrawAttention
 * @author    Nathan Tyler <support@tylerdigital.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 Tyler Digital
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-drawattention-admin.php`
 *
 *
 * @package DrawAttention
 * @author  Nathan Tyler <support@tylerdigital.com>
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
		const VERSION = '1.1.1';
		const file = __FILE__;
		const name = 'Draw Attention';
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

		/**
		 * Initialize the plugin by setting localization and loading public scripts
		 * and styles.
		 *
		 * @since     1.0.0
		 */
		private function __construct() {

			// Load plugin text domain
			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

			// Activate plugin when new blog is added
			add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

			// Load public-facing style sheet and JavaScript.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// Shortcode for displaying the image map
			add_shortcode( 'drawattention', array( $this, 'shortcode' ) );

			add_action( 'add_meta_boxes', array( $this, 'add_shortcode_metabox' ) );

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

			include_once( 'includes/pro/pro.php' );
			$this->pro = new DrawAttention_Pro( $this );

			include_once( 'includes/pro/updater.php' );
			$this->pro = new DrawAttention_Updater( $this );

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

			$domain = $this->plugin_slug;
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
			wp_register_script( $this->plugin_slug . '-responsilight', plugins_url( 'assets/js/jquery.responsilight.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );
			wp_register_script( $this->plugin_slug . '-featherlight', plugins_url( 'assets/js/featherlight.min.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );
			wp_register_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( $this->plugin_slug . '-responsilight', $this->plugin_slug . '-featherlight' ), self::VERSION, true );
		}

		/**
		 * Shortcode for displaying the image map
		 *
		 * @since    1.0.0
		 */
		public function shortcode( $atts ) {
			wp_enqueue_script( $this->plugin_slug . '-plugin-script' );
			wp_enqueue_style( $this->plugin_slug . '-plugin-styles' );

			$image_args = array(
				'post_type' => $this->cpt->post_type,
				'posts_per_page' => 1,
			);
			$image = new WP_Query($image_args);
			if ($image->have_posts() ) {
				$image->the_post();
				$imageID = get_the_ID();
			}
			wp_reset_query();

			$hotspots = get_post_meta( $imageID, $this->custom_fields->prefix.'hotspots', true );
			$html = '';

			if ( empty( $hotspots ) || empty( $hotspots['0']['coordinates'] ) ) {
				_e( 'You need to define some clickable areas for your image.', 'drawattention' );
				echo ' ';
				echo edit_post_link( __( 'Edit Image', 'drawattention' ), false, false, $imageID );
			} else {
				$img_url = wp_get_attachment_url( get_post_thumbnail_id( $imageID ));
				$img_post = get_post( $imageID );

				$settings = get_metadata( 'post', $imageID, '', false );
				$layout = ( !empty( $settings[$this->custom_fields->prefix.'map_layout'][0] ) ) ? $settings[$this->custom_fields->prefix.'map_layout'][0] : 'left';


				$spot_id = 'hotspot-' . $imageID;
				$bg_color = $settings[$this->custom_fields->prefix.'map_background_color'][0];
				$text_color = $settings[$this->custom_fields->prefix.'map_text_color'][0];
				$title_color = $settings[$this->custom_fields->prefix.'map_title_color'][0];
				$custom_css = "
					#{$spot_id} .hotspots-placeholder,
					.featherlight .featherlight-content.lightbox{$imageID} {
						background: {$bg_color};
						color: {$text_color};
					}

					#{$spot_id} .hotspots-image-container {
						background: #efefef;
					}

					#{$spot_id} .hotspot-title,
					.featherlight .featherlight-content.lightbox{$imageID} .hotspot-title {
						color: {$title_color};
					}";
				wp_add_inline_style( $this->plugin_slug . '-plugin-styles', $custom_css );

				$image_html = '';
				$image_html .=    '<div class="hotspots-image-container">';
				$image_html .=      '<img src="' . $img_url . '" class="hotspots-image" usemap="#hotspots-image-' . $imageID . '" data-highlight-color="' . $settings[$this->custom_fields->prefix.'map_highlight_color'][0] . '" data-highlight-opacity="' . $settings[$this->custom_fields->prefix.'map_highlight_opacity'][0] . '" data-highlight-border-color="' . $settings[$this->custom_fields->prefix.'map_border_color'][0] . '" data-highlight-border-width="' . $settings[$this->custom_fields->prefix.'map_border_width'][0] . '" data-highlight-border-opacity="' . $settings[$this->custom_fields->prefix.'map_border_opacity'][0] . '"/>';
				$image_html .=    '</div>';

				$info_html = '';
				$info_html .=    '<div class="hotspots-placeholder" id="content-hotspot-' . $imageID . '">';
				$info_html .=      '<div class="hotspot-initial">';
				$info_html .=        '<h2 class="hotspot-title">' . get_the_title( $imageID ) . '</h2>';
				$more_info_html = ( !empty( $settings[$this->custom_fields->prefix.'map_more_info'][0]) ) ? wpautop($settings[$this->custom_fields->prefix.'map_more_info'][0]) : '';
				$info_html .=        '<div class="hostspot-content">' . $more_info_html . '</div>';
				$info_html .=      '</div>';
				$info_html .=    '</div>';


				$html .=  '<div class="hotspots-container ' . $layout . '" id="' . $spot_id . '">';
				$html .=		'<div class="hotspots-interaction">';

				$html .= $info_html;
				$html .= $image_html;

				$html .=		'</div>'; /* End of interaction div that wraps the text area and image only */

				$html .=    '<map name="hotspots-image-' . $imageID . '" class="hotspots-map">';
				foreach ($hotspots as $key => $hotspot) {
					if ( empty( $hotspot['coordinates'] ) ) { continue; }

					$coords = $hotspot['coordinates'];
					$html .= '<area shape="poly" coords="' . $coords . '" href="#hotspot-' . $key . '">';
				}

				$html .=    '</map>';

				foreach ($hotspots as $key => $hotspot) {
					$html .=  '<div class="hotspot-info" id="hotspot-' . $key . '">';
					if ( !empty( $hotspot['title'] ) ) {
						$html .=    '<h2 class="hotspot-title">' . $hotspot['title'] . '</h2>';
					}
					if ( !empty( $hotspot['detail_image'] ) ) {
						$html .=  '<div class="hotspot-thumb">';
						$html .=    '<img src="'.$hotspot['detail_image'].'" />';
						$html .=  '</div>';
					}
					$description_html = ( !empty( $hotspot['description'] ) ) ? wpautop( $hotspot['description'] ) : '';
					$html .=    '<div class="hotspot-content">' . $description_html . '</div>';
					$html .=  '</div>';
				}

				$html .=  '</div>';
			}

			return $html;


		}

		function add_shortcode_metabox() {
			add_meta_box( 'da_shortcode', __('Copy Shortcode'), array( $this, 'display_shortcode_metabox' ), $this->cpt->post_type, 'side', 'low');
		}

		function display_shortcode_metabox() {
			echo '[drawattention]';
		}

		public static function get_plugin_dir() {
			return dirname( dirname( __FILE__ ) );
		}

		public static function get_plugin_url() {
			return dirname( plugin_dir_url( __FILE__ ) );
		}

	}
}