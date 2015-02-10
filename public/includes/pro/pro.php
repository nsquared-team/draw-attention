<?php
class DrawAttention_Pro {
	public $parent;

	function __construct( $parent ) {
		$this->parent = $parent;

		add_action( 'add_meta_boxes', array( $this, 'add_shortcode_metabox' ), 15 );
		add_filter( 'cmb2_meta_boxes', array( $this, 'add_layout_metabox' ), 20 );
		add_filter( 'da_themes', array( $this, 'add_pro_themes' ) );

		remove_shortcode( 'drawattention' );
		add_shortcode( 'drawattention', array( $this, 'shortcode' ) );
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
						'left' => 'Left',
						'right' => 'Right',
						'bottom' => 'Bottom',
						'lightbox' => 'Lightbox',
					),
					'default' => 'left',
				),

			),
		);

		return $metaboxes;
	}

	public function shortcode( $atts ) {
		$a = shortcode_atts( array(
			'id' => ''
		), $atts);

		$imageID = $a['id'];

		wp_enqueue_script( $this->parent->plugin_slug . '-plugin-script' );
		wp_enqueue_style( $this->parent->plugin_slug . '-plugin-styles' );


		if ( empty($imageID ) ) {
			$image_args = array(
				'post_type' => $this->parent->cpt->post_type,
				'posts_per_page' => 1,
				'post_parent' => 0
			);
			$image = new WP_Query($image_args);
			if ($image->have_posts() ) {
				$image->the_post();
				$imageID = get_the_ID();
			}
			wp_reset_query();
		}
		$hotspots = get_post_meta( $imageID, $this->parent->custom_fields->prefix.'hotspots', true );
		$html = '';

		if ( empty( $hotspots['0']['coordinates'] ) ) {
			_e( 'You need to define some clickable areas for your image.', 'drawattention' );
			echo ' ';
			echo edit_post_link( __( 'Edit Image', 'drawattention' ), false, false, $imageID );
		} else {
			$img_url = wp_get_attachment_url( get_post_thumbnail_id( $imageID ));
			$img_post = get_post( $imageID );

			$settings = get_metadata( 'post', $imageID, '', false );
			$layout = $settings[$this->parent->custom_fields->prefix.'map_layout'][0];

			if ( $layout == 'lightbox' ) {
				wp_enqueue_script( $this->parent->plugin_slug . '-featherlight' );
			}

			$spot_id = 'hotspot-' . $imageID;
			$bg_color = $settings[$this->parent->custom_fields->prefix.'map_background_color'][0];
			$text_color = $settings[$this->parent->custom_fields->prefix.'map_text_color'][0];
			$title_color = $settings[$this->parent->custom_fields->prefix.'map_title_color'][0];
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
			wp_add_inline_style( $this->parent->plugin_slug . '-plugin-styles', $custom_css );

			$image_html = '';
			$image_html .=    '<div class="hotspots-image-container">';
			$image_html .=      '<img src="' . $img_url . '" class="hotspots-image" usemap="#hotspots-image-' . $imageID . '" data-highlight-color="' . $settings[$this->parent->custom_fields->prefix.'map_highlight_color'][0] . '" data-highlight-opacity="' . $settings[$this->parent->custom_fields->prefix.'map_highlight_opacity'][0] . '" data-highlight-border-color="' . $settings[$this->parent->custom_fields->prefix.'map_border_color'][0] . '" data-highlight-border-width="' . $settings[$this->parent->custom_fields->prefix.'map_border_width'][0] . '" data-highlight-border-opacity="' . $settings[$this->parent->custom_fields->prefix.'map_border_opacity'][0] . '"/>';
			$image_html .=    '</div>';

			$info_html = '';
			$info_html .=    '<div class="hotspots-placeholder" id="content-hotspot-' . $imageID . '">';
			$info_html .=      '<div class="hotspot-initial">';
			$info_html .=        '<h2 class="hotspot-title">' . get_the_title( $imageID ) . '</h2>';
			$info_html .=        '<div class="hostspot-content">' . wpautop($settings[$this->parent->custom_fields->prefix.'map_more_info'][0]) . '</div>';
			$info_html .=      '</div>';
			$info_html .=    '</div>';


			$html .=  '<div class="hotspots-container ' . $layout . '" id="' . $spot_id . '">';
			$html .=		'<div class="hotspots-interaction">';

			if ( $layout == 'left' ) {
				$html .= $info_html;
				$html .= $image_html;
			} else {
				$html .= $image_html;
				$html .= $info_html;
			}

			$html .=		'</div>'; /* End of interaction div that wraps the text area and image only */

			$html .=    '<map name="hotspots-image-' . $imageID . '" class="hotspots-map">';
			foreach ($hotspots as $key => $hotspot) {
				$coords = $hotspot['coordinates'];
				$html .= '<area shape="poly" coords="' . $coords . '" href="#hotspot-' . $key . '">';
			}

			$html .=    '</map>';

			foreach ($hotspots as $key => $hotspot) {
				$html .=  '<div class="hotspot-info" id="hotspot-' . $key . '">';
				$html .=    '<h2 class="hotspot-title">' . $hotspot['title'] . '</h2>';
				if ( !empty( $hotspot['detail_image'] ) ) {
					$html .=  '<div class="hotspot-thumb">';
					$html .=    '<img src="'.$hotspot['detail_image'].'" />';
					$html .=  '</div>';
				}
				$html .=    '<div class="hotspot-content">' . wpautop( $hotspot['description'] ) . '</div>';
				$html .=  '</div>';
			}

			$html .=  '</div>';
		}

		return $html;

	}

	function add_shortcode_metabox() {
		remove_meta_box( 'da_shortcode', $this->parent->cpt->post_type, 'side', 'low' );
		add_meta_box( 'da_shortcode_pro', __('Copy Shortcode'), array( $this, 'display_shortcode_metabox' ), $this->parent->cpt->post_type, 'side', 'low');
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

					'map_title_color' => '#F74553',
					'map_text_color' => '#E5DBC0',
					'map_background_color' => '#0B0E31',
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

		) );

		return $themes;
	}

}
