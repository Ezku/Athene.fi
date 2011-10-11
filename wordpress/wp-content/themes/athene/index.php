<?php
/**
 * @package WordPress
 * @subpackage Athene
 */

get_header(); ?>

		<div id="primary">
			<div id="content" role="main">
			  <hgroup class="container_16 clearfix">
		          <h2 class="grid_8 alpha tagline">Informaatioverkostojen kilta</h2>
		          <h4 class="grid_8 omega subheader">Aalto School of Science <small>(and Magic)</small></h4>
			  </hgroup>
			  <?php if ( is_active_sidebar( 'index-widget-wide' ) ) : ?>
		        <div class="container_16 clearfix">
      		        <div class="widget-area grid_16 alpha omega" role="complementary">
      			        <?php dynamic_sidebar( 'index-widget-wide' ); ?>
      		        </div>
		        </div>
  		    <?php endif; ?>
		        <?php
		        // Keep track of whether container is open
		        $containerOpen = false;
		        // A new container will be started for every set of 4 widgets
		        $gridContainerStart = cycle(function() use($containerOpen) {
		            $containerOpen = true;
		            return '<div class="container_16 clearfix">';
		        }, '', '', '');
		        // The container will be closed after every set of 4 widgets
		        $gridContainerEnd = cycle('', '', '', function() use($containerOpen) {
		            $containerOpen = false;
		            return '</div>';
		        });
		        // Each set of 4 widgets within containers will get these classes
		        $gridClass = cycle('grid_4 alpha', 'grid_4', 'grid_4', 'grid_4 omega');
		        ?>
		        
			    <?php for($i=1; $i<13; $i++): ?>
      			    <?php if ( is_active_sidebar( 'index-widget-'.$i ) ) : ?>
      			        <?php echo $gridContainerStart() ?>
              		        <div class="widget-area <?php echo $gridClass() ?>" role="complementary">
              			        <?php dynamic_sidebar( 'index-widget-'.$i ); ?>
              		        </div>
      			        <?php echo $gridContainerEnd() ?>
          		    <?php endif; ?>
    		    <?php endfor; ?>
    		    
    		    <?php /* Close container if it was left open */ ?>
    		    <?php if ($containerOpen) { echo '</div>'; } ?>

				<?php /* Display navigation to next/previous pages when applicable */ ?>
				<?php if ( $wp_query->max_num_pages > 1 ) : ?>
					<nav id="nav-above">
						<h1 class="section-heading"><?php _e( 'Post navigation', 'toolbox' ); ?></h1>
						<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'toolbox' ) ); ?></div>
						<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'toolbox' ) ); ?></div>
					</nav><!-- #nav-above -->
				<?php endif; ?>
				
				<?php /* Start the Loop */ ?>
				<?php /* while ( have_posts() ) : the_post(); ?>
					
					<?php get_template_part( 'content', get_post_format() ); ?>

				<?php endwhile; */ ?>
				
				<?php /* Display navigation to next/previous pages when applicable */ ?>
				<?php if (  $wp_query->max_num_pages > 1 ) : ?>
					<nav id="nav-below">
						<h1 class="section-heading"><?php _e( 'Post navigation', 'toolbox' ); ?></h1>
						<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'toolbox' ) ); ?></div>
						<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'toolbox' ) ); ?></div>
					</nav><!-- #nav-below -->
				<?php endif; ?>				

			</div><!-- #content -->
		</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>