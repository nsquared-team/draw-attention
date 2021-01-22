<?php
class DrawAttention_CustomFields {
	public $parent;
	public $prefix = '_da_';
	public $actions = array();

	function __construct( $parent ) {
		$this->parent = $parent;
		if ( !class_exists( 'CMB2' ) ) {
			if ( file_exists(  __DIR__ .'/lib/cmb2/init.php' ) ) {
				require_once  __DIR__ .'/lib/cmb2/init.php';
			} elseif ( file_exists(  __DIR__ .'/lib/CMB2/init.php' ) ) {
				require_once  __DIR__ .'/lib/CMB2/init.php';
			}
		}

		include_once __DIR__ . '/actions/action.php';
		include_once __DIR__ . '/actions/action-bigcommerce.php';
		$this->actions['bigcommerce'] = new DrawAttention_BigCommerce_Action();
		include_once __DIR__ . '/actions/action-url.php';
		$this->actions['url'] = new DrawAttention_URL_Action();

		add_action( 'cmb2_render_text_number', array( $this, 'cmb2_render_text_number' ), 10, 5 );
		add_filter( 'cmb2_sanitize_text_number', array( $this, 'cmb2_sanitize_text_number' ), 10, 5 );

		add_action( 'cmb2_render_opacity', array( $this, 'cmb2_render_opacity' ), 10, 5 );
		add_filter( 'cmb2_sanitize_opacity', array( $this, 'cmb2_sanitize_opacity' ) );

		// add_action( 'add_meta_boxes', array( $this, 'add_hotspot_area_details_table_metabox' ) );
		add_filter( 'cmb2_override_meta_value', array( $this, 'hotspot_area_override_title_and_content' ), 10, 4 );
		add_action( 'wp_ajax_hotspot_update_custom_fields', array( $this, 'update_hotspot_area_details' ) );

		add_filter( 'cmb2_meta_boxes', array( $this, 'highlight_styling_metabox' ), 1000 );
		add_filter( 'cmb2_meta_boxes', array( $this, 'moreinfo_metabox' ), 1000 );
		add_filter( 'cmb2_meta_boxes', array( $this, 'hotspot_area_group_details_metabox' ), 2000 );
	}

	function cmb2_render_text_number( $field_object, $escaped_value, $object_id, $object_type, $field_type_object ) {
		if ( $escaped_value == "-1" ) $escaped_value = 0;
		if ( $escaped_value === '' ) $escaped_value = 1;
		echo $field_type_object->input( array( 'class' => 'cmb2-text-small', 'type' => 'number', 'value' => $escaped_value, ) ).'px';
	}
	function cmb2_sanitize_text_number( $new, $value, $object_id, $args, $field ) {
		$new = preg_replace( "/[^0-9]/", "", $value );
		if ( empty( $new ) ) $new = -1;

		return $new;
	}

	function cmb2_render_opacity( $field_object, $escaped_value, $object_id, $object_type, $field_type_object ) {
		echo $field_type_object->input( array( 'class' => 'cmb2-text-small', 'type' => 'range', 'min' => 0.01, 'max' => 1.01, 'step' => 0.05, ) );
		echo '<span class="opacity-percentage-value">'.( ( $escaped_value - .01 ) * 100).'</span>%';
	}
	function cmb2_sanitize_opacity( $new ) {
		return $new;
	}

	public function add_hotspot_area_details_table_metabox() {
		add_meta_box( 'hotspot_area_details_table',
			__('Hotspot Areas', 'draw-attention' ),
			array( $this, 'hotspot_area_details_table_metabox_callback' ),
			$page,
			'normal',
			'default'
		) ;
	}

	function highlight_styling_metabox( $metaboxes ) {
		if ( ! is_array( $metaboxes ) ) {
			$metaboxes = array();
		}
		
		$metaboxes['highlight_styling'] = array(
			'id' => 'highlight_styling_metabox',
			'title' => __( 'Highlight Styling', 'draw-attention' ),
			'object_types' => array( $this->parent->cpt->post_type, ),
			'context'       => 'normal',
			'priority'      => 'high',
			'fields' => array(

				array(
					'name'    => __( 'Highlight Color', 'draw-attention' ),
					'desc'    => '',
					'id'      => $this->prefix . 'map_highlight_color',
					'type'    => 'colorpicker',
					'default' => '#ffffff'
				),
				array(
					'name'    => __( 'Highlight Opacity', 'draw-attention' ),
					'desc'    => '',
					'id'      => $this->prefix . 'map_highlight_opacity',
					'type'    => 'opacity',
					'default' => '0.81',
					'escape_cb' => array( $this, 'cmb2_allow_0_value' ),
				),
				array(
					'name'    => __( 'Border Color', 'draw-attention' ),
					'desc'    => '',
					'id'      => $this->prefix . 'map_border_color',
					'type'    => 'colorpicker',
					'default' => '#ffffff'
				),
				array(
					'name'    => __( 'Border Opacity', 'draw-attention' ),
					'desc'    => '',
					'id'      => $this->prefix . 'map_border_opacity',
					'type'    => 'opacity',
					'default' => '0.81'
				),
				array(
					'name'    => __( 'Border Width', 'draw-attention' ),
					'desc'    => '',
					'id'      => $this->prefix . 'map_border_width',
					'type'    => 'text_number',
				),


			),
		);

		return $metaboxes;
	}

	// function cmb2_allow_0_value( $meta_value, $args, $field ) {
	// 	return $meta_value;
	// }

	function moreinfo_metabox( $metaboxes ) {
		if ( ! is_array( $metaboxes ) ) {
			$metaboxes = array();
		}
		
		$metaboxes['moreinfo'] = array(
			'id' => 'moreinfo_metabox',
			'title' => __( 'More Info Box Styling', 'draw-attention' ),
			'object_types' => array( $this->parent->cpt->post_type, ),
			'context'       => 'normal',
			'priority'      => 'high',
			'fields' => array(

				array(
					'name'    => __( 'Image Background Color', 'draw-attention' ),
					'desc'    => __( 'Set the background color of behind the image', 'draw-attention' ),
					'id'      => $this->prefix . 'image_background_color',
					'type'    => 'colorpicker',
					'default' => '#efefef'
				),

				array(
					'name'    => __( 'Title Color', 'draw-attention' ),
					'desc'    => __( 'Set the color of titles in More Info box', 'draw-attention' ),
					'id'      => $this->prefix . 'map_title_color',
					'type'    => 'colorpicker',
					'default' => '#000000'
				),

				array(
					'name'    => __( 'Text Color', 'draw-attention' ),
					'desc'    => __( 'Set the color of body text in More Info box', 'draw-attention' ),
					'id'      => $this->prefix . 'map_text_color',
					'type'    => 'colorpicker',
					'default' => '#000000'
				),

				array(
					'name'    => __( 'More Info Background Color', 'draw-attention' ),
					'desc'    => __( 'Set the background color of the More Info box', 'draw-attention' ),
					'id'      => $this->prefix . 'map_background_color',
					'type'    => 'colorpicker',
					'default' => '#ffffff'
				),

				array(
					'name'    => __( 'Default More Info', 'draw-attention' ),
					'desc'    => __( 'Set the text to show up in the more info box (when no area is selected)', 'draw-attention' ),
					'id'      => $this->prefix . 'map_more_info',
					'type'    => 'textarea_small',
				),
				
			),
		);

		return $metaboxes;
	}

	function hotspot_area_group_details_metabox( $metaboxes ) {
		if ( ! is_array( $metaboxes ) ) {
			$metaboxes = array();
		}
		
		if ( empty( $_REQUEST['post'] ) && empty( $_POST ) ) { return $metaboxes; }

		if ( !empty( $_REQUEST['post'] ) ) {
			$thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( esc_attr( $_REQUEST['post'] ) ), 'full' );
		}

		$metaboxes['field_group'] = apply_filters( 'da_hotspot_area_group_details', array(
			'id'           => 'field_group',
			'title'        => __( 'Hotspot Areas', 'draw-attention' ),
			'object_types' => array( $this->parent->cpt->post_type, ),
			'fields'       => array(
				array(
					'id'          => $this->prefix . 'hotspots',
					'type'        => 'group',
					'description' => __( 'Draw the clickable areas of your image', 'draw-attention' ),
					'options'     => array(
						'group_title'   => __( 'Clickable Area #{#}', 'draw-attention' ), // {#} gets replaced by row number
						'add_button'    => __( 'Add Another Area', 'draw-attention' ),
						'remove_button' => __( 'Remove Area', 'draw-attention' ),
						'sortable'      => false, // beta
				        'remove_confirm' => esc_html__( 'Are you sure you want to delete this? There is no undo.', 'draw-attention' ), // Performs confirmation before removing group
					),
					// Fields array works the same, except id's only need to be unique for this group. Prefix is not needed.
					'fields'      => array(
						'coordinates' => array(
							'name' => __( 'Coordinates', 'draw-attention' ),
							'id'   => 'coordinates',
							'type' => 'text',
							'attributes' => array(
								'data-image-url' => ( !empty( $thumbnail_src[0] ) ) ? $thumbnail_src[0] : '',
							),
						),
						'title' => array(
							'name' => __('Title', 'draw-attention' ),
							'id'   => 'title',
							'type' => 'text',
						),
						'action' => array(
							'name' => __('Action', 'draw-attention' ),
							'description' => '',
							'id'   => 'action',
							'attributes' => array(
								'class' => 'cmb2_select action',
							),
							// 'type' => 'textarea_small',
							'type' => 'select',
							'options' => array(
								'' => 'Show More Info',
							),
						),
						'description' => array(
							'name' => __('Description', 'draw-attention' ),
							'description' => '',
							'id'   => 'description',
							// 'type' => 'textarea_small',
							'type' => 'wysiwyg',
							'options' => array(
								// 'wpautop' => true, // use wpautop?
								'media_buttons' => false, // show insert/upload button(s)
								// 'textarea_name' => $editor_id, // set the textarea name to something different, square brackets [] can be used here
								'textarea_rows' => get_option('default_post_edit_rows', 7), // rows="..."
								// 'tabindex' => '',
								// 'editor_css' => '', // intended for extra styles for both visual and HTML editors buttons, needs to include the `<style>` tags, can use "scoped".
								// 'editor_class' => '', // add extra class(es) to the editor textarea
								'teeny' => true, // output the minimal editor config used in Press This
								// 'dfw' => false, // replace the default fullscreen with DFW (needs specific css)
								// 'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
								// 'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
							),
							'attributes' => array(
								'data-action' => 'more-info',
							),
						),
						'detail_image' => array(
							'name' => __( 'Detail Image', 'draw-attention' ),
							'desc' => __( 'Upload an image or enter a URL to show in the more info box', 'draw-attention' ),
							'id'   => 'detail_image',
							'type' => 'file',
							'attributes' => array(
								'data-action' => 'more-info',
							),
						),
					),
				),
			),
		) );
  
		return $metaboxes;
	}

	function hotspot_area_override_title_and_content( $value, $object_id, $args, $field ) {
		if ( $value != 'cmb2_field_no_override_val' ) return $value; // don't modify already overridden values

		if ( $args['id'] == '_title' ) {
			$post = get_post( $object_id );
			if ( !empty( $post->post_title ) ) return $post->post_title;
		}
		if ( $args['id'] == '_content' ) {
			$post = get_post( $object_id );
			if ( !empty( $post->post_content ) ) return $post->post_content;
		}

		return $value;
	}

	function update_hotspot_area_details() {
		if ( !isset( $_POST['_pid'] ) ) return;
		check_ajax_referer( 'update-hotspot_'.$_POST['_pid'], 'ajaxnonce' );

		if ( isset( $_POST['_title'] ) ) {
			$_POST['_title'] = wp_filter_nohtml_kses( $_POST['_title'] ); // also expects & returns slashes
			$title = $_POST['_title'];
			wp_update_post( array(
				'ID' => $_POST['_pid'],
				'post_title' => $_POST['_title'],
			) );
		}

		if ( isset( $_POST['_content'] ) ) {
			$_POST['_content'] = wp_filter_kses( $_POST['_content'] );
			$title = $_POST['_content'];
			wp_update_post( array(
				'ID' => $_POST['_pid'],
				'post_content' => $_POST['_content'],
			) );
		}

		$coordinates = $_POST[$this->prefix.'coordinates'];
		update_post_meta( $_POST['_pid'], $this->prefix.'coordinates', $coordinates );
	}

}
