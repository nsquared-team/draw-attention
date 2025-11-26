<?php

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
				'name'        => __( 'Draw Attention', 'fl-builder' ),
				'description' => __( 'Show a Draw Attention image', 'fl-builder' ),
				'category'    => __( 'Advanced Modules', 'fl-builder' ),
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
			'title'    => __( 'General', 'fl-builder' ), // Tab title
			'sections' => array( // Tab Sections
				'general' => array( // Section
					'title'  => __( 'Section Title', 'fl-builder' ), // Section Title
					'fields' => array( // Section Fields
						'da_img' => array(
							'type'    => 'select-img',
							'label'   => __( 'Draw Attention Image', 'fl-builder' ),
							'help'    => 'Select a Draw Attention image to be displayed',
							'default' => '',
						),
					),
				),
			),
		),
	)
);
