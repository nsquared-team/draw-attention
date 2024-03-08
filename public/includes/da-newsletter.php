<?php
class DrawAttention_Newsletter {
	public $parent;
	public $plugin_directory;


	public function __construct( $parent ) {
		$this->plugin_directory = DrawAttention::get_plugin_url() . '/public/';
		$this->parent           = $parent;

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
        add_action( 'admin_footer', array( $this, 'output_modal_dialog' ) );
        add_action('add_meta_boxes', array($this, 'add_dashboard_widget'));

        // Order the meta boxes
        add_action( 'do_meta_boxes', array( $this, 'custom_meta_boxes_order' ) );
    }

    public function enqueue_admin_styles() {
        wp_enqueue_style( 'custom-meta-box-styles', $this->plugin_directory.'/assets/css/custom-meta-box-styles.css', array(), DrawAttention::VERSION );
        wp_enqueue_script( 'news-letter-js', $this->plugin_directory.'assets/js/news-letter.js', array(), DrawAttention::VERSION );
    }

    public function custom_dashboard_widget_content() {
        echo "
            <div class='news-letter-container hndle ui-sortable-handle'> 

                <div class='content-container'>
                    <div>
                        <img src='" . $this->plugin_directory . "/assets/images/news-letter.svg' alt='News Letter Image'>
                    </div>
                    <div>
                        <p> " . __( 'Stay up to date with the latest from Draw Attention', 'draw-attention' ) . "</p>
                    </div>
                    <div class='outer-content'>
                        <p>Subscribe now! Get 20% Coupon</p>
                        <button id='openModalButton' onclick='openModal()'> <span>" . __( 'SUBSCRIBE', 'draw-attention' ) . "</span> </button>
                    </div>
                    <div class='content-notice'>
                        <span>" . __( "We'll only send you awesome content. Never spam.", 'draw-attention' ) . '</span>
                    </div>
                </div>
            </div>
        ';
	}

    public function output_modal_dialog() {
        echo "
            <div id='_news_letter_modal' class='modal' role='dialog' aria-labelledby='weeklyNewsLetterHeader'>
                <div class='modal-content'>
                    <div class='news-letter-container'> 
                        <button id='closeModalButton' class='dismiss-banner' onClick='closeModal()' aria-label='Dismiss Notice'>
                            <img src='" . $this->plugin_directory . "/assets/images/close-icon.svg' alt='Dismiss Notice Icon'>
                        </button>
                        <div class='content-container modal-container'>
                            <div class='modal-content'>
                                <div class='modal-info'>
                                    <h2 id='weeklyNewsLetterHeader'> " . __( 'Get our weekly', 'draw-attention' ) . "</span></h2>
                                    <p class='headline'> " . __( 'newsletter', 'draw-attention' ) . "</p>
                                    <p class='modal-statement'> " . __( 'Get weekly updates on the newest Draw Attention updates, case studies and tips right in your mailbox.', 'draw-attention' ) . "</p>
                                    <p class='cta'> " . __( 'Subscribe now! Get 20% Coupon', 'draw-attention' ) . "</p>
                                </div>
                                <img src='" . $this->plugin_directory . "/assets/images/letter.svg' alt='News Letter Image'>
                            </div>
                            <form method='POST' action='https://drawattention.activehosted.com/proc.php' id='_form_65E1000B4D683_' class='_form _form_1 _inline-form  _dark' novalidate='' data-styles-version='5'>
                                <input type='hidden' name='u' value='65E1000B4D683' data-name='u'>
                                <input type='hidden' name='f' value='1' data-name='f'>
                                <input type='hidden' name='s' data-name='s'>
                                <input type='hidden' name='c' value='0' data-name='c'>
                                <input type='hidden' name='m' value='0' data-name='m'>
                                <input type='hidden' name='act' value='sub' data-name='act'>
                                <input type='hidden' name='v' value='2' data-name='v'>
                                <input type='hidden' name='or' value='9f41f9016dc2e4b2b014589da6eb4bad' data-name='or'>
                                <div class='_form-content'>
                                    <div class='_form_element _x45964534 _full_width input-field'>
                                        <div class='input-field-container'>
                                            <div class='_button-wrapper _full_width'><button id='_form_1_submit' class='_submit' type='submit'><span>" . __( 'SUBSCRIBE', 'draw-attention' ) . "</span> </button></div>
                                            <label for='email' class='sr-only'>Subscribe now! Get 20% Coupon</label>
                                            <input type='text' id='email' name='email' placeholder='Your Email' required='' data-name='email'>
                                        </div>

                                    </div>
                                </div>
                                <div class='_form-thank-you' style='display:none;'></div>
                            </form>

                            <div class='content-notice md-content-notice'>
                                <span>" . __( "Your email is safe with us, we don't spam.", 'draw-attention' ) . '</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        ';
	}

    public function add_dashboard_widget() {
		add_meta_box( 'DrawAttention_Newsletter', __( 'News Letter', 'draw-attention' ), array( $this, 'custom_dashboard_widget_content' ), $this->parent->cpt->post_type, 'side', 'low' );

    }

    /**
     * Modify the order of meta boxes for a specific post type.
     *
     * @param string $post_type The post type to modify meta box order for.
     */
    function custom_meta_boxes_order($post_type) {

		global $wp_meta_boxes;

		if ( 'da_image' == $post_type ) {
			$custom_meta_boxes = $wp_meta_boxes[ $post_type ]['side']['low'];

			$order = array(
				'da_shortcode',
				'DrawAttention_Newsletter',
				'da_theme_pack',
			);

			$ordered_meta_boxes = array();

			foreach ( $order as $box_id ) {
				if ( isset( $custom_meta_boxes[ $box_id ] ) ) {
					$ordered_meta_boxes[ $box_id ] = $custom_meta_boxes[ $box_id ];
					unset( $custom_meta_boxes[ $box_id ] );
				}
			}

			$wp_meta_boxes[ $post_type ]['side']['low'] = array_merge( $ordered_meta_boxes, $custom_meta_boxes );
		}
	}
}
