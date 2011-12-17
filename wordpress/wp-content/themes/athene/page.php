<?php
/**
 * @package WordPress
 * @subpackage Athene
 */

if (get_post()->post_parent == 0) {
    $no_small_submenu = true;
}
include("header.php"); ?>

		<div id="primary" class="container_16">
			<div id="content" class="grid_10 alpha" role="main">
				<?php the_post(); ?>
				<?php get_template_part( 'content', 'page' ); ?>
			</div><!-- #content -->
			
            <div id="comments" class="grid_6 omega">
                <?php if (comments_open()): ?>
                    <?php comments_template( '', true ); ?>
                <?php else: ?> 
                    <?php if (is_active_sidebar( 'sidebar-1' )): ?>
        			        <?php dynamic_sidebar( 'sidebar-1' ); ?>
                    <?php endif; ?>
                    <?php if (is_active_sidebar( 'sidebar-2' )): ?>
        			        <?php dynamic_sidebar( 'sidebar-2' ); ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div><!-- #comments -->
            
			<?php get_sidebar(); ?>

		</div><!-- #primary -->

<?php get_footer(); ?>