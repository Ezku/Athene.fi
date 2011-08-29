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

		<div id="primary" class="container_16 subnavi-full">
			<div id="content" class="grid_16" role="main">

				<?php the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
						<h1 class="entry-title"><?php the_title(); ?></h1>
					</header><!-- .entry-header -->

					<div class="subnavi-content alpha omega grid_16">
					  <nav class="container_16" id="subnavi-large" role="navigation">
					  <?php wp_nav_menu( array( 'theme_location' => 'primary', 'depth' => 0, 'walker' => new SubMenuWalker(array(1,2)) ) ); ?>
					  </nav>
						<?php
						/*
						$subpages = get_pages(
							'child_of='.$post->ID.
							'&parent='.$post->ID.
							'&hierarcial=0&sort_column=menu_order&sort_order=desc'
						);

						foreach($subpages as $subpage) {
							$intro = $subpage->post_content;
							$intro = apply_filters('the_content', $intro);
						?>

						<div class="grid_4 alpha subnavi-box">
							<h2><a href="<?php echo get_page_link($subpage->ID) ?>"><?php echo $subpage->post_title ?></a></h2>
							<div class="entry">
								<?php echo $intro ?>

							</div>
						</div>
						<?php } /* end foreach */ ?>

					</div><!-- .subnavi-content -->

					<div class="entry-content grid_16 alpha omega">
						<?php the_content(); ?>
						<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'toolbox' ), 'after' => '</div>' ) ); ?>
						<?php edit_post_link( __( 'Edit', 'toolbox' ), '<span class="edit-link">', '</span>' ); ?>
					</div><!-- .entry-content -->
				</article><!-- #post-<?php the_ID(); ?> -->

			</div><!-- #content -->
		</div><!-- #primary -->

<?php get_footer(); ?>