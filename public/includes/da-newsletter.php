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

        add_action( 'add_meta_boxes', array( $this, 'add_newsletter_widget' ) );

		// Order the meta boxes
		add_action( 'do_meta_boxes', array( $this, 'set_meta_boxes_position' ) );

        add_action( 'admin_head', array( $this, 'append_mailerlite_script' ) );
	}

    public function append_mailerlite_script() {
        global $pagenow, $post;
        if ( ! is_admin() ) {
            return;
        }
        if ( $pagenow !== 'post.php') {
            return;
        }
        if ( $post->post_type !== 'da_image' ) {
            return;
        }
        ?>
            <!-- MailerLite Universal -->
            <script>
                (function(w,d,e,u,f,l,n){w[f]=w[f]||function(){(w[f].q=w[f].q||[])
                .push(arguments);},l=d.createElement(e),l.async=1,l.src=u,
                n=d.getElementsByTagName(e)[0],n.parentNode.insertBefore(l,n);})
                (window,document,'script','https://assets.mailerlite.com/js/universal.js','ml');
                ml('account', '1506301');
            </script>
            <!-- End MailerLite Universal -->
        <?php
    }

	public function enqueue_meta_box_assets() {
		wp_enqueue_style( 'da-custom-meta-box-styles', $this->plugin_directory . '/assets/css/custom-meta-box-styles.css', array(), DrawAttention::VERSION );
		wp_enqueue_script( 'da-news-letter-js', $this->plugin_directory . 'assets/js/news-letter.js', array(), DrawAttention::VERSION );

        if ( is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            wp_localize_script(
                'da-news-letter-js',
                'daUserData',
                array(
                    'email' => $current_user->user_email,
                )
            );
        }
	}

	public function metabox_newsletter_component() {
		echo "
            <div class='news-letter-container w-full hndle ui-sortable-handle'> 

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

    public function plugin_directory($path) 
    {
        return $this->plugin_directory . $path;
    }

	public function newsletter_modal_dialog() {
		$current_screen = get_current_screen();

		if ( ! $current_screen || 'post' !== $current_screen->base || 'da_image' !== $current_screen->post_type ) {
			return;
		}

		?>
            <div id='_news_letter_modal' class='modal' role='dialog' aria-labelledby='weeklyNewsLetterHeader'>
                <div class='modal-content modal-content-container'>
                    <div class='close-button-container'>
                        <button id='closeModalButton' class='dismiss-banner' aria-label='Dismiss Notice'>
                            <img src="<?php echo $this->plugin_directory( "assets/images/close-icon.svg" ); ?>" alt='Dismiss Notice Icon'>
                        </button>
                    </div>
                    <div class='modal-info'>
                        <div class="da-newsletter-modal-upper-container">
                            <div>
                                <h2 id='weeklyNewsLetterHeader' class="da-newsletter-modal-upper-container-header"><?php _e( 'Get our weekly', 'draw-attention' ) ?></span></h2>
                                <p class='headline'><?php _e( 'newsletter', 'draw-attention' ) ?></p>
                                <p class='modal-statement'><?php _e( 'Get weekly updates on the newest Draw Attention updates, case studies and tips right in your mailbox.', 'draw-attention' ) ?></p>
                                <p id="da-newsletter-modal-email-input-label" class='cta'><?php _e( 'Enter your email to get a 20% Coupon', 'draw-attention' ) ?></p>
                            </div>
                            <div>
                                <img class='inner-modal-image' src='<?php echo $this->plugin_directory("assets/images/letter.svg"); ?>' alt='Newsletter Image'>
                            </div>
                        </div>
                        <div class='ml-embedded' data-form='s8jMyJ'></div>
                    </div>
                </div>
            </div>
        <?php
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
