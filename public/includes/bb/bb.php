<?php

define( 'FL_MODULE_DA_DIR', plugin_dir_path( __FILE__ ) );
define( 'FL_MODULE_DA_URL', plugins_url( '/', __FILE__ ) );

/* Define custom Beaver Builder modules */
function da_load_module_examples() {
	if ( class_exists( 'FLBuilder' ) ) {
		require_once 'da/da.php';
	}
}
add_action( 'init', 'da_load_module_examples' );

/* Define a custom field for select DA images */
function da_select_img( $name, $value, $field ) {
	$args = array(
		'post_type'	=> 'da_image',
		'post_status' => 'publish',
		'posts_per_page' => -1
	);
	$images = new WP_Query($args);

	if ( $images->have_posts() ) {
	echo '<select name="' . $name . '">';
		echo '<option value="">Select an image</option>';
		while ( $images->have_posts() ) { $images->the_post();
			$selected = $value == get_the_id() ? 'selected ' : '';
			echo '<option ' . $selected . 'value="' . get_the_id() . '">' . get_the_title() . '</option>';
		}
		echo '</select>';
	}
}
add_action( 'fl_builder_control_select-img', 'da_select_img', 1, 3 );

