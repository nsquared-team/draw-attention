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

		$imageID = $a['id'];

		wp_enqueue_style( $this->parent->plugin_slug . '-plugin-styles' );
		wp_enqueue_script( $this->parent->plugin_slug . '-plugin-script' );
		wp_enqueue_script( $this->parent->plugin_slug . '-mobile-events' );

		if ( class_exists( 'Jetpack_Photon' ) ) {
			$photon_removed = remove_filter( 'image_downsize', array( Jetpack_Photon::instance(), 'filter_image_downsize' ) );
		}


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
		$url_hotspots = array();
		$urls_only = false;
		$urls_class = '';
		$html = '';

		if ( empty( $hotspots['0']['coordinates'] ) ) {
			_e( 'You need to define some clickable areas for your image.', 'drawattention' );
			echo ' ';
			echo edit_post_link( __( 'Edit Image', 'drawattention' ), false, false, $imageID );
		} else {
			$img_src = wp_get_attachment_image_src( get_post_thumbnail_id( $imageID ), 'full' );

			$img_url = $img_src[0];
			$img_width = $img_src[1];
			$img_height = $img_src[2];
			$this->photon_excluded_images[$imageID] = $img_src[0];

			$img_post = get_post( $imageID );

			// Get Alt Text
			$img_alt = get_post_meta( get_post_thumbnail_id( $img_post), '_wp_attachment_image_alt', true );

			// If no Alt text declared, add post title as alt text
			if ( ! $img_alt ) {
				$img_alt = get_the_title( $img_post );
			}

			$settings = get_metadata( 'post', $imageID, '', false );
			if ( empty( $settings[$this->parent->custom_fields->prefix.'map_layout'][0] ) ) {
				$layout = 'left';
			} else {
				$layout = $settings[$this->parent->custom_fields->prefix.'map_layout'][0];
			}

			if ( $layout == 'lightbox' ) {
				wp_enqueue_script( $this->parent->plugin_slug . '-featherlight' );
			}

			if ( empty( $settings[$this->parent->custom_fields->prefix.'event_trigger'][0] ) ) {
				$event_trigger = 'click';
			} else {
				$event_trigger = $settings[$this->parent->custom_fields->prefix.'event_trigger'][0];
			}

			if ( $event_trigger == 'hover' || $layout == 'tooltip' ) {
				wp_enqueue_script( $this->parent->plugin_slug . '-imagesloaded' );
				wp_enqueue_script( $this->parent->plugin_slug . '-qtip' );
			}

			$spot_id = 'hotspot-' . $imageID;
			$bg_color = ( !empty( $settings[$this->parent->custom_fields->prefix.'map_background_color'][0] ) ) ? $settings[$this->parent->custom_fields->prefix.'map_background_color'][0] : '';
			$text_color = ( !empty( $settings[$this->parent->custom_fields->prefix.'map_text_color'][0] ) ) ? $settings[$this->parent->custom_fields->prefix.'map_text_color'][0] : '';
			$title_color = ( !empty( $settings[$this->parent->custom_fields->prefix.'map_title_color'][0] ) ) ? $settings[$this->parent->custom_fields->prefix.'map_title_color'][0] : '';
			$image_background_color = ( !empty( $settings[$this->parent->custom_fields->prefix.'image_background_color'][0] ) ) ? $settings[$this->parent->custom_fields->prefix.'image_background_color'][0] : '';
			if ( empty( $image_background_color ) ) {
				$image_background_color = '#efefef';
			}
			$custom_css = "
				#{$spot_id} .hotspots-image-container {
					background: {$image_background_color};
				}

				#{$spot_id} .hotspots-placeholder,
				.featherlight .featherlight-content.lightbox-{$imageID},
				.qtip.tooltip-{$imageID} {
					background: {$bg_color};
					border: 0 {$bg_color} solid;
					color: {$text_color};
				}
				.qtip.tooltip-{$imageID} .qtip-icon .ui-icon {
					color: {$title_color};
				}

				#{$spot_id} .hotspot-title,
				.featherlight .featherlight-content.lightbox-{$imageID} .hotspot-title,
				.qtip.tooltip-{$imageID} .hotspot-title {
					color: {$title_color};
				}";

			// the following can be removed if the manual $custom_style doesn't cause problems
			// wp_add_inline_style( $this->parent->plugin_slug . '-plugin-styles', $custom_css );

			$custom_style = '<style type="text/css">';
			$custom_style .= $custom_css;
			$custom_style .= '</style>';

			$image_html = '';
			$image_html .=    '<div class="hotspots-image-container">';
			$image_html .=      '<img width="' . $img_width . '" height= "' . $img_height . '" alt="'. $img_alt . '" src="' . $img_url . '" class="hotspots-image" usemap="#hotspots-image-' . $imageID . '" data-event-trigger="'. $event_trigger . '" data-highlight-color="' . $settings[$this->parent->custom_fields->prefix.'map_highlight_color'][0] . '" data-highlight-opacity="' . $settings[$this->parent->custom_fields->prefix.'map_highlight_opacity'][0] . '" data-highlight-border-color="' . $settings[$this->parent->custom_fields->prefix.'map_border_color'][0] . '" data-highlight-border-width="' . $settings[$this->parent->custom_fields->prefix.'map_border_width'][0] . '" data-highlight-border-opacity="' . $settings[$this->parent->custom_fields->prefix.'map_border_opacity'][0] . '" data-no-lazy="1" data-lazy="false" />';
			$image_html .=    '</div>';

			$info_html = '';

			$wp_embed = new WP_Embed();
			if ( $layout != 'lightbox' && $layout != 'tooltip' ) {
				$info_html .=    '<div class="hotspots-placeholder" id="content-hotspot-' . $imageID . '">';
				$info_html .=      '<div class="hotspot-initial">';
				$info_html .=        '<h2 class="hotspot-title">' . get_the_title( $imageID ) . '</h2>';
				$more_info_html = ( !empty( $settings[$this->parent->custom_fields->prefix.'map_more_info'][0]) ) ? wpautop( do_shortcode( $wp_embed->run_shortcode( $settings[$this->parent->custom_fields->prefix.'map_more_info'][0] ) ) ) : '';
				$info_html .=        '<div class="hotspot-content hostspot-content">' . $more_info_html . '</div>';
				$info_html .=      '</div>';
				$info_html .=    '</div>';
			}

			$map_html = '';
			$map_html .=    '<map name="hotspots-image-' . $imageID . '" class="hotspots-map">';
			foreach ($hotspots as $key => $hotspot) {
				$coords = $hotspot['coordinates'];
				$target = '';
				if( !empty( $hotspot[ 'action' ] ) ) {
					$target = $hotspot['action'];
				}
				$new_window = '';
				$target_window = '';
				if ( !empty( $hotspot[ 'action-url-open-in-window' ] ) ) {
					$new_window = $hotspot[ 'action-url-open-in-window' ];
					$target_window = ( $new_window == 'on' ? '_new' : '' );
				}
				$target_url = '';
				if ( !empty( $hotspot[ 'action-url-url' ] ) ) {
					$target_url = $hotspot[ 'action-url-url' ];
				}

				$area_class = ( $target == 'url' ) ? 'url-area' : 'more-info-area';

				$href = ( $target == 'url' ) ? $target_url : '#hotspot-' . $spot_id . '-' . $key;

				if ( empty( $hotspot['title'] ) ) {
					$hotspot['title'] = '';
				}
				$map_html .= '<area shape="poly" coords="' . $coords . '" href="' . $href . '" title="' . $hotspot['title'] . '" alt="' . $hotspot['title'] . '" data-action="'. $target . '" target="' . $target_window . '" class="' . $area_class . '">';


				if ( $target == 'url' ) {
					$url_hotspots[] = $hotspot;
				}
			}

			if ( count( $hotspots ) == count( $url_hotspots ) ) {
				$urls_only = true;
				$urls_class = 'links-only';
			}

			$map_html .=    '</map>';


			$html .=  '<div class="hotspots-container ' . $urls_class . ' layout-' . $layout . ' event-'. $event_trigger .'" id="' . $spot_id . '">';
			$html .=		'<div class="hotspots-interaction">';

			if ( $urls_only ) {
				$html .= $image_html;
			}
			elseif ( $layout == 'left' || $layout == 'top' ) {
				$html .= $info_html;
				$html .= $image_html;
			} else {
				$html .= $image_html;
				$html .= $info_html;
			}

			$html .=		'</div>'; /* End of interaction div that wraps the text area and image only */

			$html .= $map_html;

			if ( current_user_can( 'manage_options' ) ) :
				$html .= '<div id="error-' . $spot_id . '" class="da-error"><p>It looks like there is a JavaScript error in a plugin or theme that is causing a conflict with Draw Attention. For more information on troubleshooting this issue, please see our <a href="http://tylerdigital.com/document/troubleshooting-conflicts-themes-plugins" target="_new">help page</a>.</div>';
			endif;

			foreach ($hotspots as $key => $hotspot) {
				$html .=  '<div class="hotspot-info" id="hotspot-' . $spot_id . '-' . $key . '">';
				$html .=    apply_filters( 'drawattention_hotspot_title', '<h2 class="hotspot-title">' . $hotspot['title'] . '</h2>', $hotspot );
				if ( !empty( $hotspot['detail_image_id'] ) ) {
					$html .=  '<div class="hotspot-thumb">';
					$html .=    wp_get_attachment_image( $hotspot['detail_image_id'], apply_filters( 'da_detail_image_size', 'large', $hotspot, $img_post, $settings ) );
					$html .=  '</div>';
				}
				if ( empty( $hotspot['description'] ) ) {
					$hotspot['description'] = '';
				}
				$html .=    '<div class="hotspot-content">' . wpautop( do_shortcode ( $wp_embed->run_shortcode( $hotspot['description'] ) ) ) . '</div>';
				$html .=  '</div>';
			}

			$html .=  '</div>';
		}

		$css_and_html = $custom_style;
		$css_and_html .= $html;

		if ( class_exists( 'Jetpack_Photon' ) && $photon_removed ) {
			add_filter( 'image_downsize', array( Jetpack_Photon::instance(), 'filter_image_downsize' ), 10, 3 );
		}

		return $css_and_html;

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

		) );

		return $themes;
	}

}
