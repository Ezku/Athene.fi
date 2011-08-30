<?php
/**
 * @package WordPress
 * @subpackage Athene
 */
?>

	</div><!-- #main -->
	<div class="clear"></div>

	<footer id="colophon" class="container_16 clearfix" role="contentinfo">

		<div class="grid_10 sitemap">
		  <?php wp_nav_menu( array( 'theme_location' => 'primary', 'depth' => 0, 'walker' => new SubMenuWalker(array(0,1,2), false) ) ); ?>
		</div><!-- .sitemap -->

		<div class="grid_6">
			<address>
				<p><strong>Informaatioverkostojen kilta Athene ry</strong></p>
				<p>PL 15400 (Konemiehentie 2)<br />
					00076 AALTO</p>
			</address>
			
			<div id="footer-links">
			  <ul>
        <?php get_linksbyname('footer', '<li>', '</li>', '', FALSE, 
        'length', FALSE); ?>
        </ul>
			</div>

			<div id="site-generator">
				<a href="http://wordpress.org/" rel="generator">Proudly powered by WordPress</a>
			</div>
		</div>

	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

<!--<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script> -->
<script type="text/javascript"> window.jQuery || document.write('<script src="js/libs/jquery-1.6.2.min.js"><\/script>')</script>
<script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/noisy/1.0/jquery.noisy.min.js"></script>
<script type="text/javascript" src="<?php bloginfo( 'template_directory' ); ?>/athene.js" type="text/javascript"></script>


</body>
</html>