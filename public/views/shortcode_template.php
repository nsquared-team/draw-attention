<?php

// No hotspots are defined
$has_hotspots = false;
if ( ! empty( $settings['hotspots']['0'] ) ) {
	foreach ($settings['hotspots'] as $key => $hotspot) {
		if ( ! empty( $hotspot['coordinates'] ) ) {
			$has_hotspots = true;
			break;
		}
	}
}

if ( empty( $settings['img_url'] ) ) : ?>
	<?php if ( current_user_can( 'edit_posts' ) ): ?>
		<p><em><?php _e( 'This interactive image doesn\'t have an image selected from the media library.', 'draw-attention' ); ?></em></p>
		<p><?php echo edit_post_link( 'Edit Image', '', '', $settings['image_id'] ); ?></p>
	<?php endif ?>
<?php elseif ( empty( $has_hotspots ) ) : ?>
	<?php if ( current_user_can( 'edit_posts' ) ): ?>
		<p><em><?php _e( 'You need to define some clickable areas for your image.', 'draw-attention' ); ?></em></p>
		<p><?php echo edit_post_link( __( 'Edit Image', 'draw-attention' ), false, false, $settings['image_id'] ); ?></p>
	<?php endif ?>
<?php // In page builder edit mode - just display the image ?>
<?php elseif ( !empty( $_GET['fl_builder'] ) || !empty( $_GET['elementor-preview'] ) || ( !empty( $_GET['action'] ) && $_GET['action'] == 'elementor' ) ): ?>
	<div class="hotspots-image-container">
		<img
			width="<?php echo $settings['img_width']; ?>"
			height="<?php echo $settings['img_height']; ?>"
			src="<?php echo $settings['img_url']; ?>"
			alt="<?php echo esc_attr( $settings['img_alt'] ); ?>"
			class="hotspots-image skip-lazy"
			data-id="<?php echo $settings['image_id']; ?>"
			data-no-lazy="1"
			data-lazy-src=""
			data-lazy="false"
			loading="eager"
			data-skip-lazy="1"
			>
	</div>
<?php // There are hotspots! Show the interactive image ?>
<?php else : ?>

<style>
	#<?php echo $settings['spot_id']; ?> .hotspots-image-container,
	#<?php echo $settings['spot_id']; ?> .leaflet-container {
		background: <?php echo $settings['img_bg']; ?>
	}

	#<?php echo $settings['spot_id']; ?> .hotspots-placeholder {
		background: <?php echo $settings['more_info_bg']; ?>;
		border: 0 <?php echo $settings['more_info_bg']; ?> solid;
		color: <?php echo $settings['more_info_text']; ?>;
	}

	#<?php echo $settings['spot_id']; ?> .hotspot-title {
		color: <?php echo $settings['more_info_title']; ?>;
	}

	<?php foreach ($formatted_styles as $style) : ?>
		#<?php echo $settings['spot_id']; ?> .hotspot-<?php echo $style['name']; ?> {
			stroke-width: <?php echo $style['borderWidth']; ?>;
			fill: <?php echo $style['display']['fillColor']; ?>;
			fill-opacity: <?php echo $style['display']['fillOpacity']; ?>;
			stroke: <?php echo $style['display']['borderColor']; ?>;
			stroke-opacity: <?php echo $style['display']['borderOpacity']; ?>;
		}
		#<?php echo $settings['spot_id']; ?> .hotspot-<?php echo $style['name']; ?>:hover,
		#<?php echo $settings['spot_id']; ?> .hotspot-<?php echo $style['name']; ?>:focus,
		#<?php echo $settings['spot_id']; ?> .hotspot-<?php echo $style['name']; ?>.hotspot-active {
			fill: <?php echo $style['hover']['fillColor']; ?>;
			fill-opacity: <?php echo $style['hover']['fillOpacity']; ?>;
			stroke: <?php echo $style['hover']['borderColor']; ?>;
			stroke-opacity: <?php echo $style['hover']['borderOpacity']; ?>;
		}
	<?php endforeach; ?>
	#<?php echo $settings['spot_id']; ?> .leaflet-tooltip,
	#<?php echo $settings['spot_id']; ?> .leaflet-rrose-content-wrapper {
		background: <?php echo $settings['more_info_bg']; ?>;
		border-color: <?php echo $settings['more_info_bg']; ?>;
		color: <?php echo $settings['more_info_text']; ?>;
	}

	#<?php echo $settings['spot_id']; ?> a.leaflet-rrose-close-button {
		color: <?php echo $settings['more_info_title']; ?>;
	}

	#<?php echo $settings['spot_id']; ?> .leaflet-rrose-tip {
		background: <?php echo $settings['more_info_bg']; ?>;
	}

	#<?php echo $settings['spot_id']; ?> .leaflet-popup-scrolled {
		border-bottom-color: <?php echo $settings['more_info_text']; ?>;
		border-top-color: <?php echo $settings['more_info_text']; ?>;
	}

	#<?php echo $settings['spot_id']; ?> .leaflet-tooltip-top:before {
		border-top-color: <?php echo $settings['more_info_bg']; ?>;
	}

	#<?php echo $settings['spot_id']; ?> .leaflet-tooltip-bottom:before {
		border-bottom-color: <?php echo $settings['more_info_bg']; ?>;
	}
	#<?php echo $settings['spot_id']; ?> .leaflet-tooltip-left:before {
		border-left-color: <?php echo $settings['more_info_bg']; ?>;
	}
	#<?php echo $settings['spot_id']; ?> .leaflet-tooltip-right:before {
		border-right-color: <?php echo $settings['more_info_bg']; ?>;
	}
</style>

<?php /*
<script>
	window.daStyles<?php echo $settings['image_id']; ?> = <?php echo json_encode($formatted_styles); ?>
</script>
*/ ?>

	<div class="hotspots-container <?php echo $settings['urls_class']; ?> layout-<?php echo $settings['layout']; ?> event-<?php echo $settings['event_trigger']; ?>" id="<?php echo $settings['spot_id']; ?>" data-layout="<?php echo $settings['layout']; ?>" data-trigger="<?php echo $settings['event_trigger']; ?>">
		<div class="hotspots-interaction">
			<?php if ( $settings['urls_only'] ) {
				require( $this->get_plugin_dir() . '/public/views/image_template.php' );
			} else  {
				require( $this->get_plugin_dir() . '/public/views/more_info_template.php' );
				require( $this->get_plugin_dir() . '/public/views/image_template.php' );
			} ?>
		</div>
		<map name="hotspots-image-<?php echo $settings['image_id']; ?>" class="hotspots-map">
			<?php foreach( $settings['hotspots'] as $key => $hotspot ) : ?>
				<?php
					$coords = $hotspot['coordinates'];
					$target = !empty( $hotspot['action'] ) ? $hotspot['action'] : '';
					$new_window = !empty( $hotspot['action-url-open-in-window'] ) ? $hotspot['action-url-open-in-window'] : '';
					$target_window = $new_window == 'on' ? '_new' : '';
					$target_url = !empty( $hotspot['action-url-url'] ) ? $hotspot['action-url-url'] : '';
					$rel = '';
					if ( ! empty( $hotspot['rel'] ) ) {
						$rel = $hotspot['rel'];
					}
					$area_class = $target == 'url' ? 'url-area' : 'more-info-area';
					$href = $target == 'url' ? $target_url : '#hotspot-' . $settings['spot_id'] . '-' . $key;
					$href = !empty($href) ? $href : '#';
					$title = !empty( $hotspot['title'] ) ? $hotspot['title'] : '';
					if ( empty( $hotspot['description'] ) ) {
						$hotspot['description'] = '';
					}
					if ( empty( $settings['img_settings']['_da_has_multiple_styles']['0'] ) || $settings['img_settings']['_da_has_multiple_styles']['0'] != 'on' || empty( $hotspot['style'] ) ) {
						$color_scheme = '';
					} else {
						$color_scheme = $hotspot['style'];
					}

				?>
				<area
					shape="poly"
					coords="<?php echo $coords; ?>"
					href="<?php echo $href; ?>"
					rel="<?php echo $rel; ?>"
					title="<?php echo esc_attr( $title ); ?>"
					alt="<?php echo esc_attr( $title ); ?>"
					data-action="<?php echo $target; ?>"
					data-color-scheme="<?php echo $color_scheme; ?>"
					target="<?php echo $target_window; ?>"
					class="<?php echo $area_class; ?>"
					>
			<?php endforeach; ?>
		</map>

		<?php /* Error message for admins when there's a JS error */
		if ( ! empty( $_GET['da_debug'] ) ) : ?>
			<div id="error-<?php echo $settings['spot_id']; ?>" class="da-error">
				<p>It looks like there is a JavaScript error in a plugin or theme that is causing a conflict with Draw Attention. For more information on troubleshooting this issue, please see our <a href="https://wpdrawattention.com/document/troubleshooting-conflicts-themes-plugins/" target="_new">help page</a>.
			</div>
		<?php endif; ?>

		<?php /* Loop through the hotspots and output the more info content for each */
		foreach( $settings['hotspots'] as $key => $hotspot ) : ?>
			<?php if ( ! empty( $hotspot['action'] ) && $hotspot['action'] === 'url' ) { continue; } // Skip writing out hotspots for URL only hotspots ?>

			<?php if ( empty( $settings['img_settings']['_da_has_multiple_styles']['0'] ) || $settings['img_settings']['_da_has_multiple_styles']['0'] != 'on' || empty( $hotspot['style'] ) ) {
				$color_scheme_class = '';
			} else {
				$color_scheme_class = 'da-style-' . $hotspot['style'];
			}

			if ( empty( $hotspot['title'] ) ) {
				$hotspot['title'] = '';
			}

			?>
			<div class="hotspot-info <?php echo $color_scheme_class; ?>" id="hotspot-<?php echo $settings['spot_id']; ?>-<?php echo $key; ?>">
				<?php
				if ( !empty( $hotspot['action'] ) ) {
					if ( 'bigcommerce' === $hotspot['action'] ) {
						echo DrawAttention_BigCommerce_Action::render_hotspot_content( $hotspot, $settings );
						echo '</div>';
						continue;
					}
				}
				?>

				<?php echo apply_filters( 'drawattention_hotspot_title', '<h2 class="hotspot-title">' . $hotspot['title'] . '</h2>', $hotspot ); ?>
				<?php if ( !empty($hotspot['detail_image_id'])) : ?>
					<div class="hotspot-thumb">
						<?php
						$detail_image_img_tag = wp_get_attachment_image( $hotspot['detail_image_id'], apply_filters( 'da_detail_image_size', 'large', $hotspot, $settings['img_post'], $settings['img_settings'] ) );
						if ( empty( $detail_image_img_tag ) && ! empty( $hotspot['detail_image'] ) ) {
							$detail_image_img_tag = '<img src="'.$hotspot['detail_image'].'" />';
						}
						echo $detail_image_img_tag;
						?>
					</div>
				<?php elseif( empty( $hotspot['detail_image_id'] ) && ! empty( $hotspot[ 'detail_image' ] ) ) : ?>
					<div class="hotspot-thumb">
						<img src="<?php echo $hotspot['detail_image']; ?>">
					</div>
				<?php endif; ?>
				<div class="hotspot-content">
					<?php if( !empty( $hotspot['description'] ) ) echo apply_filters( 'da_description', do_shortcode( $wp_embed->autoembed( $wp_embed->run_shortcode( $hotspot['description'] ) ) ) ); ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

<?php endif; ?>