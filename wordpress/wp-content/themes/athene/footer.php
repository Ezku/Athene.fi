<?php
/**
 * @package WordPress
 * @subpackage Athene
 */
?>

	</div><!-- #main -->
	<div class="clear"></div>

	<footer id="colophon" class="clearfix" role="contentinfo">

        <div class="container_16">
    		<div class="grid_12 alpha sitemap">
    		    <h4>Sivukartta</h4>
    		    <?php
    		    wp_nav_menu( array(
    		        'theme_location' => 'primary',
    		        'depth' => 0,
    		        'walker' => new SubMenuWalker(array(
    		                'levels_shown' => array(0,1),
    		                'only_current_branch' => false
    		            ),
    		            array(),
    		            array(
    		                // Set level 0 items to grid
    		                0 => cycle(array(array('grid_3', 'alpha'), 'grid_3', 'grid_3', array('grid_3', 'omega')))
    		            )
		            )
  		        ) ); ?>
    		</div><!-- .sitemap -->

            
    		<div class="grid_4 omega">
    		    <div id="footer-links">
    		        <h4>Linkkejä</h4>
    			    <ul>
                        <?php get_linksbyname('footer', '<li>', '</li>', '', FALSE, 'length', FALSE); ?>
                    </ul>
    			</div>
    		</div>
		</div>
		
		<div class="container_16">
			<div class="grid_6 alpha" id="footer-search">
                <h4>Haku</h4>
                <?php get_search_form(); ?>
            </div>
			
			<address class="grid_6">
			    <h4>Yhteystiedot</h4>
				<p>
				    <strong>Informaatioverkostojen kilta Athene ry</strong><br />
				    PL 15400 (Konemiehentie 2)<br />
					00076 AALTO
				</p>
			</address>
			
			<div class="grid_4 omega">
			    <h4>Athene muualla</h4>
				<p>
				    #purkusessio @ IRCnet<br />
				    <a href="http://twitter.com/#!/athenekilta">athenekilta @ Twitter</a><br />
				    <a href="https://www.facebook.com/athenekilta">athenekilta @ Facebook</a><br />
				</p>
			</div>
		</div>

	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

<script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/noisy/1.0/jquery.noisy.min.js"></script>
<script type="text/javascript" src="<?php bloginfo( 'template_directory' ); ?>/athene.js" type="text/javascript"></script>


</body>
</html>