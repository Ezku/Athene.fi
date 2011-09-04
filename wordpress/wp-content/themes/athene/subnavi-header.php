        <div class="clear"></div>
	    <nav class="container_16" role="navigation">
            <?php wp_nav_menu( array(
                'theme_location' => 'primary',
                'depth' => 0,
                'walker' => SubMenuWalker::create(array(
		                'levels_shown' => array(0,1),
		                'only_current_branch' => true
		            ))->setWrappers(array(
		                'link' => function($content, $depth) {
		                    switch ($depth) {
		                        case 0: return '<h1 class="grid_4 alpha">%s</h1>';
		                        default: return "<h5>%s</h5>";
		                    }
		                },
		                'intro' => ''
		            ))->setDepthClasses(array(
		                1 => cycle('grid_3', 'grid_3', 'grid_3', array('grid_3', 'omega'))
		            ))
            ) ); ?>
    	</nav><!-- #subnavi-small -->