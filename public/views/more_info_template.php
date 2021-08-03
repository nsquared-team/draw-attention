<?php if ( $settings['layout'] != 'lightbox' && $settings['layout'] != 'tooltip') : ?>
	<div class="hotspots-placeholder" id="content-hotspot-<?php echo $settings['image_id']; ?>">
		<div class="hotspot-initial">
			<?php $the_title = get_the_title( $settings['image_id'] ); ?>
			<?php if ( ! empty( $the_title ) ): ?>
			<h2 class="hotspot-title">
				<?php echo $the_title; ?>
			</h2>
			<?php endif; ?>
			<div class="hotspot-content">
				<?php if ( !empty( $settings['img_settings'][$this->custom_fields->prefix.'map_more_info'][0] ) ) : ?>
				<?php echo apply_filters( 'da_description', do_shortcode( $wp_embed->run_shortcode( $settings['img_settings'][$this->custom_fields->prefix.'map_more_info'][0] ) ) ); ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php endif; ?>