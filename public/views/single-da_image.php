<?php
/**
 * The template for displaying a preview of the interactive image.
 * Copy this file into your theme to customize it for your specific project
 */

get_header(); ?>

	<div id="primary" class="site-content">
		<div id="content" role="main">

			<?php while ( have_posts() ) : the_post(); ?>
				<?php echo do_shortcode( '[drawattention ID="'.get_the_id().'"]' ); ?>
				<?php if ( current_user_can( 'edit_others_posts' ) ): ?>
					<?php edit_post_link( __( 'Edit Interactive Image', 'draw-attention' ) ); ?>
				<?php endif ?>
			<?php endwhile; // end of the loop. ?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_footer(); ?>