<?php
class DrawAttention_User_Style extends DrawAttention_Style {
	function get_saved_styles( $post_id = null ) {
		if ( empty( $post_id ) && !empty( $_GET['post'] ) ) {
			$post_id = esc_attr( $_GET['post'] );
		}

		if ( empty( $post_id ) ) {
			return array();
		}
		$saved_user_styles = get_post_meta( $post_id, '_da_styles', true );
		if ( empty( $saved_user_styles['0']['title'] ) ) {
			return array();
		}

		$saved_user_style_titles = wp_list_pluck( $saved_user_styles, 'title' );
		$saved_user_styles = array_combine(
			array_map('sanitize_title', $saved_user_style_titles),
			$saved_user_style_titles
		);

		return $saved_user_styles;
	}
	
	function add_style_fields( $group_details ) {
		$user_styles = $this->get_saved_styles();
		
		foreach ($user_styles as $user_key => $label) {
			$group_details['fields'][0]['fields']['style']['options'][$user_key] = __( $label, 'drawattention' );
		}

		return $group_details;
	}	
}