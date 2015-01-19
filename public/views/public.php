<?php
/**
 * Represents the view for the public-facing component of the plugin.
 *
 * This typically includes any information, if any, that is rendered to the
 * frontend of the theme when the plugin is activated.
 *
 * @package   DrawAttention
 * @author    Nathan Tyler <support@tylerdigital.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 Tyler Digital
 */
?>

<!-- This file is used to markup the public facing aspect of the plugin. -->

<?php
$tax = get_term_by( 'id', $tax_id, 'hotspot_image' );
$tax_name = $tax->name;
$tax_desc = $tax->description;

$args = array(
	'post_type' => 'hotspot',
	'posts_per_page' => -1,
	'tax_query' => array(
		array(
			'taxonomy' => 'hotspot_image',
			'field' => 'id',
			'terms' => $tax_id
		)
	)
);

$hotspots = new WP_Query($args);

if ( $hotspots->have_posts() ) : ?>

	<div class="hotspots-container">
		<div class="hotspots-image-container">
			<?php $hotspot_image = get_field('image', 'hotspot_image_' . $tax_id); ?>
			<img src="<?php echo $hotspot_image['url']; ?>" alt="<?php echo $hotspot_image['alt']; ?>" class="hotspots-image" usemap="#hotspots-image-<?php echo $tax_id; ?>">
		</div>

		<div class="hotspots-placeholder">
			<div class="hotspot-initial">
				<h2 class="hotspot-title"><?php echo $tax_name; ?></h2>
				<div class="hotspot-content">
					<?php echo $tax_desc; ?>
				</div>
			</div>
		</div>

		<map name="hotspots-image-<?php echo $tax_id; ?>" class="hotspots-map">
			<?php while($hotspots->have_posts()) : $hotspots->the_post(); ?>
				<area shape="poly" coords="<?php the_field('coordinates'); ?>" href="#hotspot-<?php the_ID(); ?>" alt="<?php the_title(); ?>">
			<?php endwhile; ?>
		</map>
	</div>


	<?php while($hotspots->have_posts()) : $hotspots->the_post(); ?>
		<div class="hotspot-info" id="hotspot-<?php the_ID(); ?>">
			<?php if(has_post_thumbnail( $post_id = null )) : ?>
				<div class="hotspot-image">
					<?php the_post_thumbnail( 'medium' ); ?>
				</div>
			<?php endif; ?>
			<h2 class="hotspot-title"><?php the_title(); ?></h2>
			<div class="hotspot-content">
				<?php the_content(); ?>
			</div>
		</div>
	<?php endwhile; ?>
<?php endif; ?>
