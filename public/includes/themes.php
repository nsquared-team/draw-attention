<?php
class DrawAttention_Themes {
	public $parent;

	function __construct( $parent ) {
		$this->parent = $parent;

		add_action( 'add_meta_boxes', array( $this, 'add_theme_pack_metabox' ) );
		add_action( 'da_register_admin_script', array( $this, 'pass_themes_to_admin_js' ) );
	}

	function add_theme_pack_metabox() {
		add_meta_box( 'da_theme_pack', __('Apply Color Scheme'), array( $this, 'display_theme_pack_metabox' ), $this->parent->cpt->post_type, 'side', 'low');
	}
	
	function display_theme_pack_metabox() {
		echo '<p>'.__( 'Quickly apply a theme (you can adjust each color afterwards).' ).'</p>'; ?>
		<select id="da-theme-pack-select">
			<option value="">Select a theme...</option>
			<?php foreach ( $this->get_themes() as $key => $theme ) : ?>
			<option value="<?php echo $theme['slug']; ?>"><?php echo $theme['name']; ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	function pass_themes_to_admin_js() {
		wp_localize_script( $this->parent->plugin_slug . '-admin-script', 'daThemes', array(
			'cfPrefix' => $this->parent->custom_fields->prefix,
			'themes' => $this->get_themes(),
		) );
	}

	function get_themes() {
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
		);

		return apply_filters( 'da_themes', $themes );
	}

}
