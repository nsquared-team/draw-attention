<?php
class DrawAttention_Newsletter {
	public $parent;
	public $plugin_directory;


	public function __construct( $parent ) {
		$this->plugin_directory = DrawAttention::get_plugin_url() . '/public/';
		$this->parent           = $parent;

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_meta_box_assets' ) );
		add_action( 'admin_footer', array( $this, 'newsletter_modal_dialog' ) );
		// add_action( 'admin_footer', array( $this, 'newsletter_modal_dialog' ), 10, 1 );

		// disable until we fix DA email issues
		add_action( 'add_meta_boxes', array( $this, 'add_newsletter_widget' ) );

		// Order the meta boxes
		add_action( 'do_meta_boxes', array( $this, 'set_meta_boxes_position' ) );
	}

	public function enqueue_meta_box_assets() {
		wp_enqueue_style( 'da-custom-meta-box-styles', $this->plugin_directory . '/assets/css/custom-meta-box-styles.css', array(), DrawAttention::VERSION );
		wp_enqueue_script( 'da-news-letter-js', $this->plugin_directory . 'assets/js/news-letter.js', array(), DrawAttention::VERSION );
	}

	public function metabox_newsletter_component() {
		echo "
            <div class='news-letter-container news-letter-metabox-container w-full hndle ui-sortable-handle'> 

                <div class='content-container'>
                    <div>
                        <img src='" . $this->plugin_directory . "/assets/images/news-letter.svg' alt='Newsletter Image'>
                    </div>
                    <div>
                        <p> " . __( 'Stay up to date with the latest from Draw Attention', 'draw-attention' ) . "</p>
                    </div>
                    <div class='outer-content'>
                        <p>Subscribe now! Get 20% Coupon</p>
                        <button id='openModalButton'> <span>" . __( 'SUBSCRIBE', 'draw-attention' ) . "</span> </button>
                    </div>
                    <div class='content-notice'>
                        <span>" . __( "We'll only send you awesome content. Never spam.", 'draw-attention' ) . '</span>
                    </div>
                </div>
            </div>
        ';
	}

	public function newsletter_modal_dialog() {
		if ( ! is_admin() ) {
			return;
		}

		$current_screen = get_current_screen();
		if ( ! $current_screen || 'post' !== $current_screen->base || 'da_image' !== $current_screen->post_type ) {
			return;
		}

		$user_email = wp_get_current_user()->user_email ?? '';

		echo "
            <div id='_news_letter_modal' class='modal' role='dialog' aria-labelledby='weeklyNewsLetterHeader'>
                <div class='modal-content modal-content-container'>
                    <div class='close-button-container'>
                        <button id='closeModalButton' class='dismiss-banner' aria-label='Dismiss Notice'>
                            <img src='" . $this->plugin_directory . "/assets/images/close-icon.svg' alt='Dismiss Notice Icon'>
                        </button>
                    </div>
                    <form  method='POST' class='news-letter-container news-letter-form-container' id='da-newsletter-form' class='_form _form_1 _inline-form  _dark' novalidate='' data-styles-version='5'>
                        <div class='content-container modal-container'>
                            <div class='modal-content'>
                                <div class='modal-info'>
                                    <h2 id='weeklyNewsLetterHeader'> " . __( 'Get our weekly', 'draw-attention' ) . "</span></h2>
                                    <p class='headline'> " . __( 'newsletter', 'draw-attention' ) . "</p>
                                    <p class='modal-statement'> " . __( 'Get weekly updates on the newest Draw Attention updates, case studies and tips right in your mailbox.', 'draw-attention' ) . "</p>
                                    <label data-hideonsuccess for='da-newsletter-email' class='cta'> " . __( 'Enter your email to get a 20% Coupon', 'draw-attention' ) . "</label>
                                </div>
                                <img class='inner-modal-image' src='" . $this->plugin_directory . "/assets/images/letter.svg' alt='Newsletter Image'>
                            </div>

                            <div data-showonreset data-hideonsuccess class='_form_element _x45964534 _full_width input-field'>
                                <div class='input-field-container'>
                                    <div class='_button-wrapper _full_width'><button id='da_newsletter_form_submit_btn' class='_submit' type='submit'><span>" . __( 'Subscribe', 'draw-attention' ) . "</span> </button></div>
                                    <input type='text' id='da-newsletter-email' name='email' placeholder='" . __( 'Your Email', 'draw-attention' ) . "' required aria-describedby='da_newsletter_msg_error' data-name='email' value='" . esc_attr( $user_email ) . "'>
                                </div>
                            </div>

                            <div data-hideonreset id='da_newsletter_msg_success' data-nodeonsuccess class='da-newsletter-message_success da-hidden'>
                                <span class='da_newsletter_msg_success__title'>" . __( 'Thank You', 'draw-attention' ) . "</span>
                                <span class='da_newsletter_msg_success__subtitle'>" . __( 'You have successfully joined the Draw Attention subscriber list. Please check your inbox for your new Coupon Code.', 'draw-attention' ) . "</span>
                            </div>
                            
                            <div id='da_newsletter_msg_error' class='error-message-container'>
                                <p data-hideonreset data-hideonsuccess id='da_newsletter_msg_error_invalid_input' class='da-newsletter-message da-error-message da-hidden'>
                                    " . __( 'Invalid Email address!', 'draw-attention' ) . "
                                </p>
                                <p data-hideonreset data-hideonsuccess id='da_newsletter_msg_error_generic' class='da-newsletter-message da-error-message da-hidden'>
                                    " . __( 'An error occurred. Please try again later.', 'draw-attention' ) . "
                                </p>
                            </div>
                            
                            <div data-showonreset data-hideonsuccess class='content-notice md-content-notice'>
                                <span>" . __( 'We keep your email safe and private, without drawing attention.', 'draw-attention' ) . '</span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        ';
	}

	public function add_newsletter_widget() {
		add_meta_box( 'DrawAttention_Newsletter', __( 'Newsletter', 'draw-attention' ), array( $this, 'metabox_newsletter_component' ), $this->parent->cpt->post_type, 'side', 'low' );
	}

	/**
	 * Modify the order of meta boxes for a specific post type.
	 *
	 * @param string $post_type The post type to modify meta box order for.
	 */
	function set_meta_boxes_position( $post_type ) {

		global $wp_meta_boxes;

		if ( 'da_image' !== $post_type ) {
			return;
		}

		if ( empty( $wp_meta_boxes['da_image']['side']['low'] ) ) {
			return;
		}

		$custom_meta_boxes = $wp_meta_boxes[ $post_type ]['side']['low'];

		$order              = array(
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
