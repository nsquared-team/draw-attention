<?php
class DrawAttention_Upsell {
	var $admin;

	function __construct( $admin ) {
		$this->admin = $admin;

		add_action( 'admin_menu', array( $this, 'admin_menu' ), 20 );

		// Add an action link pointing to the upgrade page.
		$plugin_basename = 'draw-attention/' . $this->admin->da->plugin_slug . '.php';
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
		add_action( 'admin_init', array( $this, 'redirect_pro_link' ), 9 );

		// add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
	}

	public function admin_menu() {
        add_submenu_page( 'edit.php?post_type=da_image', __( 'Upgrade to Pro', 'draw-attention' ), __( 'Upgrade to Pro', 'draw-attention' ), 'edit_others_posts', 'da_upgrade_to_pro', array( $this, 'display_plugin_admin_page' ) );
    }

	function redirect_pro_link() {
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'da_upgrade_to_pro' ) {
			wp_redirect( 'https://wpdrawattention.com?utm_source=plugin&utm_medium=ads&utm_campaign=upgrade-pro&utm_content=admin-menu' );
			exit();
		}
	}

	public function add_action_links( $links ) {

		return array_merge(
			$links,
			array(
				'upgrade' => '<strong><a href="https://wpdrawattention.com?utm_source=plugin&utm_medium=ads&utm_campaign=upgrade-pro&utm_content=plugin-list" target="_blank">' . __( 'Upgrade to Pro', 'draw-attention' ) . '</a></strong>'
			)
		);

	}
}