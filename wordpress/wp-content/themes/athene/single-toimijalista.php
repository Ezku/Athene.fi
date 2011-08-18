<?php
define(LAYOUT_TOIMIJAT, "Toimijat");
define(LAYOUT_PHUKSIT, "Phuksit");
define(LAYOUT_VALMISTUNEET, "Valmistuneet");

function custom_field_found($field) { // yep, bubble gum found
  return get_custom_field($field) != "The ".$field." field is not defined as a custom field.";
}

$layout = get_custom_field('layout');

get_header(); ?>

<div style="margin-right: 250px;">
<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
	

	<h1><?php the_title(); ?></h1>

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
</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>