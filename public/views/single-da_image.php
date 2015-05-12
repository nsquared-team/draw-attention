<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

get_header(); ?>

	<div id="primary" class="site-content">
		<div id="content" role="main">

			<?php while ( have_posts() ) : the_post(); ?>
				<?php echo do_shortcode( '[drawattention ID="'.get_the_id().'"]' ); ?>
				<?php if ( current_user_can( 'edit_posts' ) ): ?>
					<?php edit_post_link( __( 'Edit Interactive Image', 'drawattention' ) ); ?>
				<?php endif ?>
			<?php endwhile; // end of the loop. ?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_footer(); ?>