<?php
define(LAYOUT_TOIMIJAT, "Toimijat");
define(LAYOUT_PHUKSIT, "Phuksit");
define(LAYOUT_VALMISTUNEET, "Valmistuneet");

function custom_field_found($field) { // yep, bubble gum found
  return get_custom_field($field) != "The ".$field." field is not defined as a custom field.";
}

$layout = get_custom_field('layout');

get_header(); ?>

<div id="primary" class="container_16">
	<div id="content" class="grid_13 alpha prefix_3" role="main">
<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
	
  <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  	<header class="entry-header">
  		<h1 class="entry-title"><?php the_title(); ?></h1>
  	</header><!-- .entry-header -->
  	<div class="entry-content">

<?php endwhile; // end of the loop. ?>

<?php 
$Q = new GetPostsQuery();
$Q->set_output_type(ARRAY_A);
$Q->limit = 10000;
$args = array(
  "post_type" => get_custom_field('tyyppi')
);
/*
print "<pre>";
print_r($_GET);
print "</pre>";
print "<pre>Ryhma: ".$wp_query->query_vars['ryhma']."</pre>";
*/
if ($layout == LAYOUT_PHUKSIT) {
  include('phuksit.php');
} else if ($layout == LAYOUT_TOIMIJAT) {
  include('toimijat.php');
} else { // $layout == LAYOUT_VALMISTUNEET
  $results = $Q->get_posts($args);
  include('valmistuneet.php');
}
?>
  <div class="clearfix" />
  </div>
  </article>
  </div> <!-- /#content -->
</div> <!-- /#primary -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>