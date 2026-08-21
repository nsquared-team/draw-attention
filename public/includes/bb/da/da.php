<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * This is an example module with only the basic
 * setup necessary to get it working.
 *
 * @class DrawAttentionModule
 */
class DrawAttentionModule extends FLBuilderModule {

	public function __construct() {
		parent::__construct(
			array(
				'name'        => __( 'Draw Attention', 'draw-attention' ),
				'description' => __( 'Show a Draw Attention image', 'draw-attention' ),
				'category'    => __( 'Advanced Modules', 'draw-attention' ),
				'dir'         => FL_MODULE_DA_DIR . 'da/',
				'url'         => FL_MODULE_DA_URL . 'da/',
			)
		);
	}
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module(
	'DrawAttentionModule',
	array(
		'general' => array( // Tab
			'title'    => __( 'General', 'draw-attention' ), // Tab title
			'sections' => array( // Tab Sections
				'general' => array( // Section
					'title'  => __( 'Image', 'draw-attention' ), // Section Title
					'fields' => array( // Section Fields
						'da_img' => array(
							'type'    => 'select-img',
							'label'   => __( 'Draw Attention Image', 'draw-attention' ),
							'help'    => __( 'Select a Draw Attention image to be displayed', 'draw-attention' ),
							'default' => '',
						),
					),
				),
			),
		),
	)
);
