<?php
class DrawAttention_Action {
	public $priority = 10;

	public function __construct() {
		add_filter( 'da_hotspot_area_group_details', array( $this, 'add_action_fields' ), $this->priority, 1 );
	}

	public static function is_active() {
		return true;
	}

	function add_action_fields( $group_details ) {
		throw new Exception( 'Custom DrawAttention_Action objects need to implement add_action_fields()' );

		return $group_details;
	}
}