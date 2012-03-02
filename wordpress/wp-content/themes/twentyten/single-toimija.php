<?php
/**
 * Sample template for displaying single toimija posts.
 * Save this file as as single-toimija.php in your current theme.
 *
 * This sample code was based off of the Starkers Baseline theme: http://starkerstheme.com/
 */

get_header(); ?>

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
	

		<?php the_post_thumbnail(); ?>
		
		<strong>Nimi:</strong> <?php print get_custom_field('nimi'); ?><br />
		<strong>Virka:</strong> <?php print get_custom_field('tehtava'); ?><br />
		<strong>Tyyppi:</strong> <?php print get_custom_field('tyyppi'); ?><br />
		<strong>Kuva:</strong> <?php print get_custom_image('kuva'); ?><br />




<?php endwhile; // end of the loop. ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>