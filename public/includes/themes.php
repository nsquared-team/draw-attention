<?php
class DrawAttention_Themes {
	public $parent;

	function __construct( $parent ) {
		$this->parent = $parent;

		add_action( 'add_meta_boxes', array( $this, 'add_theme_pack_metabox' ) );
		add_action( 'da_register_admin_script', array( $this, 'pass_themes_to_admin_js' ) );
	}

	function add_theme_pack_metabox() {
		add_meta_box( 'da_theme_pack', __( 'Apply Color Scheme', 'draw-attention' ), array( $this, 'display_theme_pack_metabox' ), $this->parent->cpt->post_type, 'side', 'low');
	}
	
	function display_theme_pack_metabox() {
		echo '<p>'.__( 'Quickly apply a theme (you can adjust each color afterwards).', 'draw-attention' ).'</p>'; ?>
		<select id="da-theme-pack-select">
			<option value=""><?php  _e( 'Select a theme...', 'draw-attention' ) ?></option>
			<?php foreach ( $this->get_themes() as $key => $theme ) : ?>
			<option value="<?php echo $theme['slug']; ?>"><?php echo $theme['name']; ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	public function pass_themes_to_admin_js() {
		wp_localize_script( $this->parent->plugin_slug . '-admin-script', 'daThemes', array(
			'cfPrefix' => $this->parent->custom_fields->prefix,
			'themes' => $this->get_themes(),
		) );
	}

	public static function apply_theme( $post_id, $theme_slug ) {
		$themes = self::get_themes();
		if ( empty( $themes[$theme_slug]['values'] ) ) { return false; }

		foreach ($themes[$theme_slug]['values'] as $key => $meta_value) {
			update_post_meta( $post_id, '_da_'.$key, $meta_value );
			// TODO: Make prefix dynamic
		}
	}

	public static function get_themes() {
		$themes = array(
			'light' => array(
				'slug' => 'light',
				'name' => 'Light',
				'values' => array(
					'map_highlight_color' => '#222222',
					'map_highlight_opacity' => 0.8,

					'map_border_color' => '#000000',
					'map_border_opacity' => 0.8,
					'map_border_width' => 1,

					'map_title_color' => '#000000',
					'map_text_color' => '#000000',
					'map_background_color' => '#ffffff',
				),
			),
			'dark' => array(
				'slug' => 'dark',
				'name' => 'Dark',
				'values' => array(
					'map_highlight_color' => '#cccccc',
					'map_highlight_opacity' => 0.8,

					'map_border_color' => '#ffffff',
					'map_border_opacity' => 0.8,
					'map_border_width' => 1,

					'map_title_color' => '#ffffff',
					'map_text_color' => '#ffffff',
					'map_background_color' => '#000000',
				),
			),
			'drawattention' => array(
				'slug' => 'drawattention',
				'name' => 'Draw Attention',
				'values' => array(
					'map_highlight_color' => '#3CA2A2',
					'map_highlight_opacity' => 0.7,

					'map_border_color' => '#235B6E',
					'map_border_opacity' => 1,
					'map_border_width' => 2,

					'map_title_color' => '#93C7A4',
					'map_text_color' => '#DFEBE5',
					'map_background_color' => '#2E2D29',
				),
			),

		);

		return apply_filters( 'da_themes', $themes );
	}

}
