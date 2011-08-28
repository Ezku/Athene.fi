<?php
/**
 * @package WordPress
 * @subpackage Athene
 */
?>

	</div><!-- #main -->
	<div class="clear"></div>

	<footer id="colophon" class="container_16" role="contentinfo">

		<div class="grid_10 sitemap">
		  <?php wp_nav_menu( array( 'theme_location' => 'primary', 'depth' => 0, 'walker' => new SubMenuWalker(array(0,1,2), false) ) ); ?>
			<!--<div class="grid_2 alpha">
			  
		      <ul>
		        <li><a href="" class="mainlink">Kilta</a></li>
		        <li><a>Säännöt</a></li>
		        <li><a>Vaalit</a></li>
		        <li><a>Hallitus &amp; Toimarit</a></li>
		        <li><a>Jäsenet</a></li>
		        <li><a>Valmistuneet</a></li>
		        <li><a>Olohuone</a></li>
		        <li><a>Byrokratiajutut</a></li>
		        <li><a>Yhteystiedot</a></li>
		        <li><a>Historia</a></li>
		      </ul>
		    </div>

		    <div class="grid_2">
		      <ul>
		        <li><a href="" class="mainlink">Toiminta</a></li>
		        <li><a>Uutiset</a></li>
		        <li><a>Tapahtumat</a></li>
		        <li><a>Urheilu</a></li>
		        <li><a>Opintoneuvoja</a></li>
		        <li><a>Edunvalvonta</a></li>
		        <li><a>Tiedotuskanavat</a></li>
		        <li><a>Kiltalehti</a></li>
		      </ul>
		    </div>

		    <div class="grid_2">
		      <ul>
		        <li><a href="" class="mainlink">Phuksit</a></li>
		        <li><a>Teekkariudesta</a></li>
		        <li><a>Phuksiopastusta</a></li>
		        <li><a>Phuksiryhmät</a></li>
		        <li><a>Phuksipisteet</a></li>
		      </ul>
		    </div>

		    <div class="grid_2">
		      <ul>
		        <li><a href="" class="mainlink">Abeille</a></li>
		      </ul>
		    </div>

		    <div class="grid_2 omega">
		      <ul>
		        <li><a href="" class="mainlink">Yrityksille</a></li>
		      </ul>
		    </div>-->
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

</body>
</html>