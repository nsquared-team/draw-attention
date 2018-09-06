<?php

// No hotspots are defined
if ( empty( $settings['hotspots']['0']['coordinates'] ) ) : ?>
	<p><?php _e( 'You need to define some clickable areas for your image.', 'drawattention' ); ?></p>
	<p><?php echo edit_post_link( __( 'Edit Image', 'drawattention' ), false, false, $settings['image_id'] ); ?></p>

<?php // There are hotspots! Show the image ?>
<?php elseif ( !empty( $_GET['fl_builder'] ) || !empty( $_GET['elementor-preview'] ) || ( !empty( $_GET['action'] ) && $_GET['action'] == 'elementor' ) ): ?>
	<div class="hotspots-image-container">
		<img
			width="<?php echo $settings['img_width']; ?>"
			height="<?php echo $settings['img_height']; ?>"
			src="<?php echo $settings['img_url']; ?>"
			alt="<?php echo $settings['img_alt']; ?>"
			class="hotspots-image skip-lazy"
			data-id="<?php echo $settings['image_id']; ?>"
			data-no-lazy="1"
			data-lazy="false"
			>
	</div>
<?php else : ?>

<style>
	#<?php echo $settings['spot_id']; ?> .hotspots-image-container {
		background: <?php echo $settings['img_bg']; ?>
	}

	#<?php echo $settings['spot_id']; ?> .hotspots-placeholder,
	.featherlight .featherlight-content.lightbox-<?php echo $settings['image_id']; ?>,
	.qtip.tooltip-<?php echo $settings['image_id']; ?> {
		background: <?php echo $settings['more_info_bg']; ?>;
		border: 0 <?php echo $settings['more_info_bg']; ?> solid;
		color: <?php echo $settings['more_info_text']; ?>;
	}
	.qtip.tooltip-<?php echo $settings['image_id']; ?> .qtip-icon .ui-icon {
		color: <?php echo $settings['more_info_title']; ?>;
	}

	#<?php echo $settings['spot_id']; ?> .hotspot-title,
	.featherlight .featherlight-content.lightbox-<?php echo $settings['image_id']; ?> .hotspot-title,
	.qtip.tooltip-<?php echo $settings['image_id']; ?> .hotspot-title {
		color: <?php echo $settings['more_info_title']; ?>;
	}
</style>

<script>
	window.daStyles<?php echo $settings['image_id']; ?> = <?php echo json_encode($formatted_styles); ?>
</script>

	<div class="hotspots-container <?php echo $settings['urls_class']; ?> layout-<?php echo $settings['layout']; ?> event-<?php echo $settings['event_trigger']; ?>" id="<?php echo $settings['spot_id']; ?>">
		<div class="hotspots-interaction">
			<?php if ( $settings['urls_only'] ) {
				require( $this->parent->get_plugin_dir() . '/public/views/image_template.php' );
			} elseif ( $settings['layout'] == 'left' || $settings['layout'] == 'top' ) {
				require( $this->parent->get_plugin_dir() . '/public/views/more_info_template.php' );
				require( $this->parent->get_plugin_dir() . '/public/views/image_template.php' );
			} else {
				require( $this->parent->get_plugin_dir() . '/public/views/image_template.php' );
				require( $this->parent->get_plugin_dir() . '/public/views/more_info_template.php' );
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
					title="<?php echo $title; ?>"
					alt="<?php echo $title; ?>"
					data-action="<?php echo $target; ?>"
					data-color-scheme="<?php echo $color_scheme; ?>"
					target="<?php echo $target_window; ?>"
					class="<?php echo $area_class; ?>"
					>
			<?php endforeach; ?>
		</map>

		<?php /* Error message for admins when there's a JS error */
		if ( current_user_can( 'manage_options' ) ) : ?>
			<div id="error-<?php echo $settings['spot_id']; ?>" class="da-error">
				<p>It looks like there is a JavaScript error in a plugin or theme that is causing a conflict with Draw Attention. For more information on troubleshooting this issue, please see our <a href="http://tylerdigital.com/document/troubleshooting-conflicts-themes-plugins" target="_new">help page</a>.
			</div>
		<?php endif; ?>

		<?php /* Loop through the hotspots and output the more info content for each */
		foreach( $settings['hotspots'] as $key => $hotspot ) : ?>
			<?php if ( empty( $settings['img_settings']['_da_has_multiple_styles']['0'] ) || $settings['img_settings']['_da_has_multiple_styles']['0'] != 'on' || empty( $hotspot['style'] ) ) {
				$color_scheme_class = '';
			} else {
				$color_scheme_class = 'da-style-' . $hotspot['style'];
			} ?>
			<div class="hotspot-info <?php echo $color_scheme_class; ?>" id="hotspot-<?php echo $settings['spot_id']; ?>-<?php echo $key; ?>">
				<?php echo apply_filters( 'drawattention_hotspot_title', '<h2 class="hotspot-title">' . $hotspot['title'] . '</h2>', $hotspot ); ?>
				<?php if ( !empty($hotspot['detail_image_id'])) : ?>
					<div class="hotspot-thumb">
						<?php echo wp_get_attachment_image( $hotspot['detail_image_id'], apply_filters( 'da_detail_image_size', 'large', $hotspot, $settings['img_post'], $settings['img_settings'] ) ); ?>
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