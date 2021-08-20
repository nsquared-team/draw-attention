<?php

// uncomment this line for testing
//set_site_transient( 'update_plugins', null );

class DrawAttention_ImportExport {
	public $parent;

	function __construct( $parent ) {
		$this->parent = $parent;

		add_action( 'admin_menu', array( $this, 'admin_menu' ), 15 );
	}

	public function is_action( $action ) {
		if ( !empty( $_POST['action'] ) && $_POST['action'] === $action ) {
			return true;
		}

		return false;
	}

	public function process_import() {
		if ( !$this->is_action( 'import' ) ) {
			return;
		}
		
		if ( empty( $_POST['import_code'] ) ) {
			return;
		}
		$import_code = stripslashes($_POST['import_code']);
		$import_array = json_decode( $import_code, true );
		if ( empty( $import_array['0']['post']['ID'] ) ) {
			return false;
		}

		$imported = array();
		$errors = array();

		foreach ($import_array as $key => $to_import) {
			unset($to_import['post']['ID']);
			$insert_id = wp_insert_post( $to_import['post'], $error );
			if ( !empty( $insert_id ) ) {
				$imported[] = array(
					'ID' => $insert_id,
					'post_title' => $to_import['post']['post_title'],
				);
			} else {
				$errors[] = $to_import;
			}
		}

		return array(
			'imported' => $imported,
			'errors' => $errors,
		);
	}

	public function get_export_array( $ids=array() ) {
		$response = array();
		foreach ($ids as $key => $id) {
			$post = get_post( $id );
			if ( empty( $post->post_type ) || $post->post_type !== 'da_image' ) {
				continue;
			}

			$response[$key] = array(
				'id' => $id,
				'post' => (array)$post,
			);
			$metadata = get_post_meta( $id, '', true );
			foreach ($metadata as $meta_key => $meta_value) {
				if ( strpos( $meta_key, '_da_' ) !== 0 ) {
					continue;
				}
				$response[$key]['post']['meta_input'][$meta_key] = maybe_unserialize( $meta_value[0] );
			}
		}

		return $response;
	}

	public function get_export_json( $ids=array() ) {
		$export_array = $this->get_export_array( $ids );
		return json_encode( $export_array );
	}

	public function admin_menu() {
		global $submenu;

		add_submenu_page( 'edit.php?post_type=da_image', __( 'Import / Export', 'draw-attention' ), __( 'Import / Export', 'draw-attention' ), 'edit_posts', 'import_export', array( $this, 'output_import_export_page' ) );
	}

	public function output_import_export_page() {
		?>
		<div class="import">
			<h3>Import</h3>
			<p>If you've already exported from another site, paste the export code below:</p>
			<form method="POST" name="import" action="edit.php?post_type=da_image&page=import_export">
				<input type="hidden" name="action" value="import" />
				<textarea name="import_code" cols="100" rows="5" placeholder=""></textarea><br />
				<input type="submit" value="Import" />
			</form>
			<?php $response = $this->process_import(); ?>
			<?php if ( !empty( $response ) ): ?>
				<?php foreach ($response['imported'] as $key => $value): ?>
					<h4>
						Successfully imported 
						<a href="<?php echo admin_url( 'post.php?post='.$value['ID'].'&action=edit' ); ?>">
							<?php echo $value['post_title']; ?>
						</a>
					</h4>
				<?php endforeach ?>
				<h3>Note: the image itself isn't transferred over, so you will need to reupload it. But most importantly all the colors and shapes are transferred over!
			<?php endif ?>
		</div>
		<br />
		<div class="export">
			<h3>Export</h3>
			<p>Choose images to export</p>
			<form method="POST" name="export" action="edit.php?post_type=da_image&page=import_export">
				<input type="hidden" name="action" value="export" />
				<?php
				$da_images = new WP_Query( array(
					'post_type' => 'da_image',
					'post_status' => 'any',
					'posts_per_page' => 1,
					'order' => 'DESC',
					'orderby' => 'ID',
				) );
				$export_ids = ( empty( $_POST['export_ids'] ) ) ? array() : (array)$_POST['export_ids'];
				foreach ($da_images->posts as $key => $da_image): ?>
					<input type="checkbox" name="export_ids[]" value="<?php echo $da_image->ID; ?>" id="export_id_<?php echo $da_image->ID; ?>" <?php if( in_array( $da_image->ID, $export_ids ) ) echo 'checked="checked"'; ?> /> <label for="export_id_<?php echo $da_image->ID; ?>"><?php echo $da_image->post_title; ?></label><br />
				<?php endforeach; ?>
				<input type="submit" value="Generate Export Code" />
			</form>

			<?php if ( $this->is_action( 'export' ) ): ?>
				<?php if ( empty( $_POST['export_ids'] ) ): ?>
					Please select one or more images above to export
				<?php else: ?>
					<?php
					$export_ids = $_POST['export_ids'];
					if ( !is_array( $export_ids ) ) {
						$export_ids = array();
					}
					$export_ids = array_map( 'esc_attr', $export_ids );
					$export_json = $this->get_export_json( $export_ids );
					?>
					<textarea cols="100" rows="20"><?php echo $export_json; ?></textarea>
				<?php endif ?>
			<?php endif ?>
		</div>
		<?php
	}

}