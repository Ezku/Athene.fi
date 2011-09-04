
                        <nav class="container_16" id="subnavi-large" role="navigation">
    					    <?php
    					    $next = cycle(' alpha', '', '', ' omega');
    					    wp_nav_menu( array(
					            'theme_location' => 'primary',
					            'depth' => 0,
					            'walker' => SubMenuWalker::create(array(
					                    'levels_shown' => array(1),
					                    'only_current_branch' => true
				                    ))->setWrappers(array(
					                    'link' => '<h4>%s</h4>'
					                ))->setDepthClasses(array(
    		                            // Set level 1 items to grid
    		                            1 => cycle(array('grid_4', 'alpha'), 'grid_4', 'grid_4', array('grid_4', 'omega'))
					                ))
					        )); ?>
                        </nav>