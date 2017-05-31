<?php
class DrawAttention_Style {
	public $priority = 10;

	public function __construct() {
		add_filter( 'da_hotspot_area_group_details', array( $this, 'add_style_fields' ), $this->priority, 1 );
	}

	function add_style_fields( $group_details ) {
		throw new Exception( 'Custom DrawAttention_Style objects need to implement add_style_fields()' );

		return $group_details;
	}
}