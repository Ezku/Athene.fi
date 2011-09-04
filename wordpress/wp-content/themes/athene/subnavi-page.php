<?php
/**
 * Template Name: Subnavi boxes
 * Description: 2nd level navigation gallery
 *
 * @package WordPress
 * @subpackage Athene
 */
$no_small_submenu = true;
include("header.php"); ?>

		<?php the_post(); ?>

		<div id="primary" class="subnavi-full">
			<div id="content" role="main">

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		
		            <div class="subnavi-header clearfix">
                	    <div class="container_16">
                			<header class="entry-header grid_4 alpha">
                				<h1 class="entry-title"><?php the_title(); ?></h1>
                			</header><!-- .entry-header -->

                			<div class="entry-content">
                			    <div class="grid_8">
                				    <h4 class="subheader"><?php the_content(); ?></h4>
                				    <?php edit_post_link( __( 'Edit', 'toolbox' ), '<span class="edit-link">', '</span>' ); ?>
                				</div>
                			    <div class="grid_4 omega">
                					<?php wp_link_pages( array(
                					    'before' => '<div class="page-link">' . __( 'Pages:', 'toolbox' ),
                					    'after' => '</div>' )
                				    ); ?>
                				</div>
                			</div><!-- .entry-content -->
                		</div>
            		</div><!-- .subnavi-header -->

					<div class="subnavi-content">
					    <?php include 'subnavi-content.php' ?>
					</div><!-- .subnavi-content -->
				</article><!-- #post-<?php the_ID(); ?> -->

			</div><!-- #content -->
		</div><!-- #primary -->

<?php get_footer(); ?>