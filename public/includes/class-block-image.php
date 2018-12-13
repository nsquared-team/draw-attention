<?php
/**
 * Draw Attention Block Image.
 *
 * @since   1.9
 * @package Draw_Attention
 */

/**
 * Draw Attention Block Image.
 *
 * @since 1.9
 */
class DrawAttention_Block_Image {
	/**
	 * Parent plugin class.
	 *
	 * @since 1.9
	 *
	 * @var   DrawAttention
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  1.9
	 *
	 * @param  Draw_Attention $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  1.9
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'register_image_block' ) );
	}

	function register_image_block() {
		if( function_exists('register_block_type') ){
			wp_register_script(
				'drawattention-image-block-js',
				trailingslashit( $this->plugin->get_plugin_url() ) . 'admin/assets/js/draw-attention-block.js',
				array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' )
			);

			wp_register_style(
				'drawattention-image-block-css',
				trailingslashit( $this->plugin->get_plugin_url() ) . 'admin/assets/css/draw-attention-block.css'
			);

			$images = new WP_Query( array(
				'post_type' => 'da_image',
				'post_status' => 'publish',
				'posts_per_page' => -1
			) );
			$da_image_key_values = wp_list_pluck( $images->posts, 'post_title', 'ID' );
			asort( $da_image_key_values );

			wp_localize_script( 'drawattention-image-block-js', 'drawAttentionImages', $da_image_key_values );


			register_block_type( 'draw-attention/image', array(
				'editor_script' => 'drawattention-image-block-js',
				'editor_style' => 'drawattention-image-block-css',
				'keywords' => array( 'image', 'hotspot', 'map' ),
				'attributes' => array (
					'id' => array (
						'type' => 'string',
						'default' => '',
					),
				),

				'render_callback' => array( $this, 'render' ),
			) );
		}
	}

	function render( $atts ) {
		return $this->plugin->pro->shortcode( $atts );
	}

}
