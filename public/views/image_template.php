<div class="hotspots-image-container">
	<img
		width="<?php echo $settings['img_width']; ?>"
		height="<?php echo $settings['img_height']; ?>"
		src="<?php echo $settings['img_url']; ?>"
		alt="<?php echo esc_attr( $settings['img_alt'] ); ?>"
		class="hotspots-image skip-lazy"
		usemap="#hotspots-image-<?php echo $settings['image_id']; ?>"
		data-image-title="<?php echo get_the_title( $settings['image_id'] ); ?>"
		data-image-description="<?php echo wp_strip_all_tags( $settings['img_settings']['_da_map_more_info'][0] ); ?>"
		data-event-trigger="<?php echo $settings['event_trigger']; ?>"
		data-always-visible="<?php echo $settings['always_visible']; ?>"
		data-id="<?php echo $settings['image_id']; ?>"
		data-no-lazy="1"
		data-lazy-src=""
		data-lazy="false"
		loading="eager"
		data-skip-lazy="1"
		>
</div>