<?php
class DrawAttention_Pro {
	public $parent;
	public $photon_excluded_images = array();

	function __construct( $parent ) {
		$this->parent = $parent;

		add_action( 'add_meta_boxes', array( $this, 'add_shortcode_metabox' ), 15 );
		add_filter( 'cmb2_meta_boxes', array( $this, 'add_layout_metabox' ), 20 );
		add_filter( 'da_themes', array( $this, 'add_pro_themes' ) );

		remove_shortcode( 'drawattention' );
		add_shortcode( 'drawattention', array( $this, 'shortcode' ) );
		add_filter( 'da_detail_image_size', array( $this, 'optimize_detail_image_size' ), 10, 4 );
		add_filter( 'jetpack_photon_skip_image', array ($this, 'jetpack_photon_skip_image' ), 10, 3 );
	}

	function add_layout_metabox( $metaboxes ) {
		$metaboxes['da_layout'] = array(
			'id' => 'da_layout_metabox',
			'title' => __( 'Layout', 'drawattention' ),
			'object_types' => array( $this->parent->cpt->post_type, ),
			'context'       => 'side',
			'priority'      => 'low',
			'fields' => array(

				array(
					'name'    => __( '', 'drawattention' ),
					'desc'    => __( '', 'drawattention' ),
					'id'      => $this->parent->custom_fields->prefix . 'map_layout',
					'type'    => 'radio',
					'options' => array(
						'left' => __('Left', 'drawattention'),
						'right' => __('Right', 'drawattention'),
						'bottom' => __('Bottom', 'drawattention'),
						'top'		=> __('Top', 'drawattention'),
						'lightbox' => __('Lightbox', 'drawattention'),
						'tooltip' => __('Tooltip', 'drawattention'),
					),
					'default' => 'left',
				),

				array(
					'name'    => __( 'Show more info on', 'drawattention' ),
					'desc'    => __( 'Click is recommended for best results. If you use Hover, please test carefully with your image', 'drawattention' ),
					'id'      => $this->parent->custom_fields->prefix . 'event_trigger',
					'type'    => 'select',
					'options' => array(
						'click' => __('Click', 'drawattention'),
						'hover' => __('Hover', 'drawattention'),
					),
					'default' => 'click',
				),

			),
		);

		return $metaboxes;
	}

	public function optimize_detail_image_size( $size, $hotspot, $image, $settings ) {
		$layout = $settings[$this->parent->custom_fields->prefix.'map_layout'][0];

		if ( in_array( $layout, array( 'top', 'bottom', 'lightbox' ) ) ) {
			$size = 'large';
		} elseif ( in_array( $layout, array( 'left', 'right', 'tooltip' ) ) ) {
			$size = 'medium';
		}

		return $size;
	}

	public function jetpack_photon_skip_image( $val, $src, $tag ) {
		if ( in_array( $src, $this->photon_excluded_images ) ) {
			return true;
		}

		return $val;
	}

	public function shortcode( $atts ) {
		$a = shortcode_atts( array(
			'id' => ''
		), $atts);

		// Enqueue CSS and Scripts
		wp_enqueue_style( $this->parent->plugin_slug . '-plugin-styles' );
		wp_enqueue_script( $this->parent->plugin_slug . '-plugin-script' );

		// Begin settings array
		$settings = array(
			'image_id' => $a['id'],
			'has_photon' => class_exists( 'Jetpack_Photon' ),
			'url_hotspots' => array(),
			'urls_only' => false,
			'urls_class' => '',
		);

		// If no ID is passed, get the most recent DA image
		if ( empty ( $settings['image_id'] ) ) {
			$latest_da = get_posts('post_type=' . $this->parent->cpt->post_type . '&numberposts=1');
			$settings['image_id'] = $latest_da[0]->ID;
		}

		// Get and set DA settings
		$settings['img_settings'] = get_metadata( 'post', $settings['image_id'], '', false );
		$settings['spot_id'] = 'hotspot-' . $settings['image_id'];

		// Add hotspots to settings
		$settings['hotspots'] = get_post_meta( $settings['image_id'], $this->parent->custom_fields->prefix . 'hotspots', true );
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

		// Set default values for missing settings
		$settings['layout'] = !empty($settings['img_settings'][$this->parent->custom_fields->prefix . 'map_layout'][0]) ? $settings['img_settings'][$this->parent->custom_fields->prefix . 'map_layout'][0] : 'left';
		$settings['event_trigger'] = !empty($settings['img_settings'][$this->parent->custom_fields->prefix.'event_trigger'][0]) ? $settings['img_settings'][$this->parent->custom_fields->prefix.'event_trigger'][0] : 'click';
		$settings['always_visible'] = !empty($settings['img_settings'][$this->parent->custom_fields->prefix . 'always_visible'][0]) ? $settings['img_settings'][$this->parent->custom_fields->prefix . 'always_visible'][0] : 'false';

		// Add styles to settings
		$settings['styles'] = get_post_meta( $settings['image_id'], $this->parent->custom_fields->prefix . 'styles', true );
		$map_style_names_to_titles = $this->parent->custom_fields->styles['user']->get_saved_styles( $settings['image_id']);
		$settings['border_width'] = $settings['img_settings'][$this->parent->custom_fields->prefix.'map_border_width'][0];
		$settings['border_opacity'] = $settings['img_settings'][$this->parent->custom_fields->prefix.'map_border_opacity'][0];
		$settings['more_info_bg'] = ( !empty( $settings['img_settings'][$this->parent->custom_fields->prefix.'map_background_color'][0] ) ) ? $settings['img_settings'][$this->parent->custom_fields->prefix.'map_background_color'][0] : '';
		$settings['more_info_text'] = ( !empty( $settings['img_settings'][$this->parent->custom_fields->prefix.'map_text_color'][0] ) ) ? $settings['img_settings'][$this->parent->custom_fields->prefix.'map_text_color'][0] : '';
		$settings['more_info_title'] = ( !empty( $settings['img_settings'][$this->parent->custom_fields->prefix.'map_title_color'][0] ) ) ? $settings['img_settings'][$this->parent->custom_fields->prefix.'map_title_color'][0] : '';
		$settings['img_bg'] = ( !empty( $settings['img_settings'][$this->parent->custom_fields->prefix.'image_background_color'][0] ) ) ? $settings['img_settings'][$this->parent->custom_fields->prefix.'image_background_color'][0] : '#efefef';

		// Create default style
		if ( empty( $settings['styles'] ) ) {
			$settings['styles'] = array();
		}
		$settings['styles'][] = array(
			'title' => 'default',
			'map_highlight_color' => $settings['img_settings'][$this->parent->custom_fields->prefix.'map_highlight_color'][0],
			'map_highlight_opacity' => $settings['img_settings'][$this->parent->custom_fields->prefix.'map_highlight_opacity'][0],
			'map_border_color' => $settings['img_settings'][$this->parent->custom_fields->prefix.'map_border_color'][0],
			'_da_map_hover_color' => $settings['img_settings'][$this->parent->custom_fields->prefix.'map_hover_color'][0],
			'_da_map_hover_opacity' => $settings['img_settings'][$this->parent->custom_fields->prefix.'map_hover_opacity'][0]
		);

		// Create formatted array of styles
		$formatted_styles = array();
		foreach ($settings['styles'] as $key => $style) {
			if ( empty( $style['title'] ) ) {
				$style['title'] = 'Custom';
			}
			
			$style_slug = array_search($style['title'], $map_style_names_to_titles);
			$new_style = array(
				'name' => $style_slug ? $style_slug : $style['title'],
				'borderWidth' => $settings['border_width'],
			);

			if ( $settings['always_visible'] && $settings['always_visible'] !== 'false' ) {
				$new_style['display'] = array(
					'fillColor' => $style['map_highlight_color'],
					'fillOpacity' => $style['map_highlight_opacity'],
					'borderColor' => $style['map_border_color'],
					'borderOpacity' => $settings['border_opacity'],
				);
				$new_style['hover'] = array(
					'fillColor' => $style['_da_map_hover_color'],
					'fillOpacity' => $style['_da_map_hover_opacity'],
					'borderColor' => $style['map_border_color'],
					'borderOpacity' => $settings['border_opacity'],
				);
			} else {
				$new_style['hover'] = array(
					'fillColor' => $style['map_highlight_color'],
					'fillOpacity' => $style['map_highlight_opacity'],
					'borderColor' => $style['map_border_color'],
					'borderOpacity' => $settings['border_opacity'],
				);
			}
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

		// Enqueue any extra needed scripts
		if ( $settings['layout'] == 'lightbox' ) {
			wp_enqueue_script( $this->parent->plugin_slug . '-featherlight' );
		}
		if ( $settings['event_trigger'] == 'hover' || $settings['layout'] == 'tooltip' || count( $settings['url_hotspots'] ) > 0 ) {
			wp_enqueue_script( $this->parent->plugin_slug . '-imagesloaded' );
			wp_enqueue_script( $this->parent->plugin_slug . '-qtip' );
		}

		// Remove Photon filter
		if ( $settings['has_photon'] ) {
			$photon_removed = remove_filter( 'image_downsize', array( Jetpack_Photon::instance(), 'filter_image_downsize' ) );
		}
		$this->photon_excluded_images[ $settings['image_id'] ] = $settings['img_url'];

		// Create a new embed
		$wp_embed = new WP_Embed();

		ob_start();

		require( $this->parent->get_plugin_dir() . '/public/views/shortcode_template.php' );

		if ( $settings['has_photon'] && $photon_removed ) {
			add_filter( 'image_downsize', array( Jetpack_Photon::instance(), 'filter_image_downsize' ), 10, 3 );
		}

		return ob_get_clean();
	}

	function add_shortcode_metabox() {
		remove_meta_box( 'da_shortcode', $this->parent->cpt->post_type, 'side', 'low' );
		add_meta_box( 'da_shortcode_pro', __('Copy Shortcode', 'drawattention'), array( $this, 'display_shortcode_metabox' ), $this->parent->cpt->post_type, 'side', 'low');
	}

	function display_shortcode_metabox() {
		echo '[drawattention ID="'.get_the_id().'"]';
	}

	function add_pro_themes( $themes ) {
		$themes = array_merge( $themes, array(
			'suzette' => array(
				'slug' => 'suzette',
				'name' => 'Suzette',
				'values' => array(
					'map_highlight_color' => '#FE59C2',
					'map_highlight_opacity' => 0.8,

					'map_border_color' => '#D82B99',
					'map_border_opacity' => 0.8,
					'map_border_width' => 3,

					'map_hover_color' => '#fe26af',
					'map_hover_opacity' => 0.9,

					'map_title_color' => '#FF80D1',
					'map_text_color' => '#FFCCEC',
					'map_background_color' => '#8B0059',
				),
			),
			'autumn' => array(
				'slug' => 'autumn',
				'name' => 'Autumn',
				'values' => array(
					'map_highlight_color' => '#9E0303',
					'map_highlight_opacity' => 0.8,

					'map_border_color' => '#210900',
					'map_border_opacity' => 0.8,
					'map_border_width' => 1,

					'map_hover_color' => '#6c0202',
					'map_hover_opacity' => 0.9,

					'map_title_color' => '#D4A600',
					'map_text_color' => '#F2EACB',
					'map_background_color' => '#590015',
				),
			),
			'spring' => array(
				'slug' => 'spring',
				'name' => 'Spring',
				'values' => array(
					'map_highlight_color' => '#DAA2BE',
					'map_highlight_opacity' => 0.8,

					'map_border_color' => '#A2BEDA',
					'map_border_opacity' => 0.8,
					'map_border_width' => 5,

					'map_hover_color' => '#c26594',
					'map_hover_opacity' => 0.9,

					'map_title_color' => '#DAA2BE',
					'map_text_color' => '#8FB46B',
					'map_background_color' => '#F1FFE4',
				),
			),
			'midnight' => array(
				'slug' => 'midnight',
				'name' => 'Midnight',
				'values' => array(
					'map_highlight_color' => '#002244',
					'map_highlight_opacity' => 0.7,

					'map_border_color' => '#000D1A',
					'map_border_opacity' => 1,
					'map_border_width' => 1,

					'map_hover_color' => '#004488',
					'map_hover_opacity' => 0.9,

					'map_title_color' => '#ffffff',
					'map_text_color' => '#A2BEDA',
					'map_background_color' => '#002244',
				),
			),
			'blacktie' => array(
				'slug' => 'blacktie',
				'name' => 'Black Tie',
				'values' => array(
					'map_highlight_color' => '#FF023D',
					'map_highlight_opacity' => 0.8,

					'map_border_color' => '#636366',
					'map_border_opacity' => 1,
					'map_border_width' => 2,

					'map_hover_color' => '#9b0024',
					'map_hover_opacity' => 0.9,

					'map_title_color' => '#FF023D',
					'map_text_color' => '#FFFFFF',
					'map_background_color' => '#050004',
				),
			),
			'crimson' => array(
				'slug' => 'crimson',
				'name' => 'Crimson',
				'values' => array(
					'map_highlight_color' => '#CC0000',
					'map_highlight_opacity' => 0.8,

					'map_border_color' => '#800000',
					'map_border_opacity' => 1,
					'map_border_width' => 2,

					'map_hover_color' => '#770000',
					'map_hover_opacity' => 0.9,

					'map_title_color' => '#F22424',
					'map_text_color' => '#FF7373',
					'map_background_color' => '#590000',
				),
			),
			'tangerine' => array(
				'slug' => 'tangerine',
				'name' => 'Tangerine',
				'values' => array(
					'map_highlight_color' => '#F28500',
					'map_highlight_opacity' => 0.8,

					'map_border_color' => '#FFC073',
					'map_border_opacity' => 1,
					'map_border_width' => 2,

					'map_hover_color' => '#ae6000',
					'map_hover_opacity' => 0.9,

					'map_title_color' => '#FFC073',
					'map_text_color' => '#7F4600',
					'map_background_color' => '#F28500',
				),
			),
			'sunnyday' => array(
				'slug' => 'sunnyday',
				'name' => 'Sunny Day',
				'values' => array(
					'map_highlight_color' => '#FFCC33',
					'map_highlight_opacity' => 0.8,

					'map_border_color' => '#5983FF',
					'map_border_opacity' => 0.8,
					'map_border_width' => 2,

					'map_hover_color' => '#9db6ff',
					'map_hover_opacity' => 0.9,

					'map_title_color' => '#FFCC33',
					'map_text_color' => '#A6BCFF',
					'map_background_color' => '#0B3ED9',
				),
			),
			'forest' => array(
				'slug' => 'forest',
				'name' => 'Forest',
				'values' => array(
					'map_highlight_color' => '#1C8C15',
					'map_highlight_opacity' => 0.8,

					'map_border_color' => '#066600',
					'map_border_opacity' => 0.8,
					'map_border_width' => 3,

					'map_hover_color' => '#2bd620',
					'map_hover_opacity' => 0.9,

					'map_title_color' => '#3DB235',
					'map_text_color' => '#68D861',
					'map_background_color' => '#044000',
				),
			),
			'blueprint' => array(
				'slug' => 'blueprint',
				'name' => 'Blueprint',
				'values' => array(
					'map_highlight_color' => '#20418D',
					'map_highlight_opacity' => 0.8,

					'map_border_color' => '#001440',
					'map_border_opacity' => 0.8,
					'map_border_width' => 3,

					'map_hover_color' => '#0d1b3a',
					'map_hover_opacity' => 0.9,

					'map_title_color' => '#20418D',
					'map_text_color' => '#001440',
					'map_background_color' => '#FAFBFF',
				),
			),
			'violet' => array(
				'slug' => 'violet',
				'name' => 'Violet',
				'values' => array(
					'map_highlight_color' => '#C523EB',
					'map_highlight_opacity' => 0.8,

					'map_border_color' => '#9F00C5',
					'map_border_opacity' => 0.8,
					'map_border_width' => 2,

					'map_hover_color' => '#590a6b',
					'map_hover_opacity' => 0.9,

					'map_title_color' => '#9a36b2',
					'map_text_color' => '#75158C',
					'map_background_color' => '#EB99FF',
				),
			),
			'america' => array(
				'slug' => 'america',
				'name' => 'America',
				'values' => array(
					'map_highlight_color' => '#E0162B',
					'map_highlight_opacity' => 0.8,

					'map_border_color' => '#0052A5',
					'map_border_opacity' => 0.8,
					'map_border_width' => 5,

					'map_hover_color' => '#0052a5',
					'map_hover_opacity' => 0.9,

					'map_title_color' => '#ffffff',
					'map_text_color' => '#ffffff',
					'map_background_color' => '#0052A5',
				),
			),
			'mintchip' => array(
				'slug' => 'mintchip',
				'name' => 'Mint Chip',
				'values' => array(
					'map_highlight_color' => '#9CBD8E',
					'map_highlight_opacity' => 0.8,

					'map_border_color' => '#3B240C',
					'map_border_opacity' => 0.8,
					'map_border_width' => 3,

					'map_hover_color' => '#6e9b5b',
					'map_hover_opacity' => 0.9,

					'map_title_color' => '#9CBD8E',
					'map_text_color' => '#F0E6BD',
					'map_background_color' => '#4A2607',
				),
			),
			'candybox' => array(
				'slug' => 'candybox',
				'name' => 'Candy Box',
				'values' => array(
					'map_highlight_color' => '#EB9F9F',
					'map_highlight_opacity' => 0.8,

					'map_border_color' => '#A79C8E',
					'map_border_opacity' => 0.8,
					'map_border_width' => 3,

					'map_hover_color' => '#f8ecc9',
					'map_hover_opacity' => 0.9,

					'map_title_color' => '#F1BBBA',
					'map_text_color' => '#F8ECC9',
					'map_background_color' => '#6B5344',
				),
			),
			'stormyseas' => array(
				'slug' => 'stormyseas',
				'name' => 'Stormy Seas',
				'values' => array(
					'map_highlight_color' => '#3E838C',
					'map_highlight_opacity' => 0.8,

					'map_border_color' => '#195E63',
					'map_border_opacity' => 0.8,
					'map_border_width' => 3,

					'map_hover_color' => '#8ebdb6',
					'map_hover_opacity' => 0.9,

					'map_title_color' => '#8EBDB6',
					'map_text_color' => '#ECE1C3',
					'map_background_color' => '#063940',
				),
			),
			'planetearth' => array(
				'slug' => 'planetearth',
				'name' => 'Planet Earth',
				'values' => array(
					'map_highlight_color' => '#036564',
					'map_highlight_opacity' => 0.8,

					'map_border_color' => '#031634',
					'map_border_opacity' => 0.8,
					'map_border_width' => 3,

					'map_hover_color' => '#cdb380',
					'map_hover_opacity' => 0.9,

					'map_title_color' => '#CDB380',
					'map_text_color' => '#E8DDCB',
					'map_background_color' => '#033649',
				),
			),
			'partyatmidnight' => array(
				'slug' => 'partyatmidnight',
				'name' => 'Party at Midnight',
				'values' => array(
					'map_highlight_color' => '#FFAB98',
					'map_highlight_opacity' => 0.8,

					'map_border_color' => '#7F9CA0',
					'map_border_opacity' => 0.8,
					'map_border_width' => 3,

					'map_hover_color' => '#7f9ca0',
					'map_hover_opacity' => 0.9,

					'map_title_color' => '#F74553',
					'map_text_color' => '#E5DBC0',
					'map_background_color' => '#0B0E31',
				),
			),

		) );

		return $themes;
	}

}
