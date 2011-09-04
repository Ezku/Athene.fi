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
                        <nav class="container_16" id="subnavi-large" role="navigation">
    					    <?php
    					    $next = cycle(array(' alpha', '', '', ' omega'));
    					    wp_nav_menu( array(
					            'theme_location' => 'primary',
					            'depth' => 0,
					            'walker' => new SubMenuWalker(
					                array(
					                    'levels_shown' => array(1),
					                    'only_current_branch' => true
					                ),
					                array(
					                    'link' => '<h4>%s</h4>'
					                ),
					                array(
    		                            // Set level 0 items to grid
    		                            1 => cycle(array(array('grid_4', 'alpha'), 'grid_4', 'grid_4', array('grid_4', 'omega')))
					                )
					            )
					        )); ?>
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
				</article><!-- #post-<?php the_ID(); ?> -->

			</div><!-- #content -->
		</div><!-- #primary -->

<?php get_footer(); ?>