<?php
class DrawAttention_BigCommerce_Action extends DrawAttention_Action {
	public static function is_active() {
		$classes = array(
			'\BigCommerce\Post_Types\Product\Product',
			'\BigCommerce\Templates\Product_Featured_Image',
			'\BigCommerce\Templates\Quick_View_Image',
		);

		foreach ( $classes as $class ) {		
			if ( ! class_exists( $class ) ) {
				return false;
			}
		}

		return true;
	}

	function add_action_fields( $group_details ) {
		if ( ! $this->is_active() ) {
			return $group_details;
		}

		$group_details['fields'][0]['fields']['action']['options']['bigcommerce'] = __( 'Display BigCommerce Product', 'draw-attention' );

		$product_options = get_transient( 'da_bc_product_options' );
		if ( empty( $product_options ) ) {
			$products = new WP_Query(array(
				'post_type' => 'bigcommerce_product',
				'posts_per_page' => -1,
			) );
			$product_options = wp_list_pluck( $products->posts, 'post_title', 'ID' );

			set_transient( 'da_bc_product_options', $product_options, 3600 );
		}
		
		$select_options = array( '' => 'Select a Product...' );
		$select_options = array_replace( $select_options, $product_options );

		$group_details['fields'][0]['fields']['action-bigcommerce-product-id'] = array(
			'name' => __('Big Commerce Product', 'draw-attention' ),
			'id'   => 'action-bigcommerce-product-id',
			'type' => 'select',
			'options' => $select_options,
			'attributes' => array(
				'data-action' => 'bigcommerce',
			),
		);

		// TODO: Add display options
		// $group_details['fields'][0]['fields']['action-bigcommerce-display-parts'] = array(
		// 	'name' => __('BigCommerce Sections to Display', 'draw-attention' ),
		// 	'id'   => 'action-bigcommerce-display-parts',
		// 	'type' => 'multicheck',
		// 	'options' => array(
		// 		'image' => __( 'Image', 'draw-attention' ),
		// 		'title' => __( 'Title', 'draw-attention' ),
		// 		'description' => __( 'Description', 'draw-attention' ),
		// 		'reviews' => __( 'Reviews', 'draw-attention' ),
		// 		'add_to_cart' => __( 'Add to Cart', 'draw-attention' ),
		// 	),
		// 	'attributes' => array(
		// 		'data-action' => 'bigcommerce',
		// 	),
		// );

		return $group_details;
	}

	public static function render_hotspot_content( $hotspot, $settings ) {
		if ( ! self::is_active() ) {
			return;
		}

		if ( empty( $hotspot['action-bigcommerce-product-id'] ) ) {
			return;
		}

		$product_post_id = (int)$hotspot['action-bigcommerce-product-id'];
		$product_post = get_post( $product_post_id );
		if ( empty( $product_post->ID ) ) {
			return;
		}

		$product = new \BigCommerce\Post_Types\Product\Product( $product_post_id );
		$image_component = \BigCommerce\Templates\Product_Featured_Image::factory( [
			\BigCommerce\Templates\Product_Featured_Image::PRODUCT => $product,
		] );
		$attributes = array();

		$quick_view_component = \BigCommerce\Templates\Quick_View_Image::factory( [
			\BigCommerce\Templates\Quick_View_Image::PRODUCT    => $product,
			\BigCommerce\Templates\Quick_View_Image::IMAGE      => $image_component->render(),
			\BigCommerce\Templates\Quick_View_Image::ATTRIBUTES => $attributes,
		] );

		$data = $quick_view_component->get_data();

		return $data['quick_view'];

	}
}