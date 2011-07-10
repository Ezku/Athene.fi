<?php
define(LAYOUT_TOIMIJAT, "Toimijat");
define(LAYOUT_PHUKSIT, "Phuksit");
define(LAYOUT_VALMISTUNEET, "Valmistuneet");

function custom_field_found($field) { // yep, bubble gum found
  return get_custom_field($field) != "The ".$field." field is not defined as a custom field.";
}

$layout = get_custom_field('layout');

get_header(); ?>

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
/*
if ($layout == LAYOUT_PHUKSIT) {
  $Q = new GetPostsQuery();
  $Q->set_output_type(ARRAY_A);
  $args = array(
    "post_type" => 'ISO'
  );
  
  // render ISOs
}

?>

<?php 
$Q = new GetPostsQuery();
$Q->set_output_type(ARRAY_A);
$args = array(
  "post_type" => get_custom_field('tyyppi')
);

$results = $Q->get_posts($args);
if (in_array($layout,$layoutsWithTables)) {
  ?>
  <table>
  <?php
}

if ($layout == LAYOUT_PHUKSIT) {
  
}

foreach($results as $entry) {
  $post = get_post_complete($entry['ID']);
  if (!in_array($layout,$layoutsWithTables)) { // LAYOUTS USING DIVS AND WITH PICTURES ?>
    <div style="float: left;">
    <img src="<?php print get_custom_field('kuva'); ?>" alt="" style="width: 100px;" /><br />
    <?php print get_custom_field('nimi'); ?><br />
    <?php if (custom_field_found('email')) print get_custom_field('email')."<br />"; ?>
    <?php if (custom_field_found('virka')) print get_custom_field('virka')."<br />"; ?>
    <?php if (custom_field_found('ryhma')) print get_custom_field('ryhma')."<br />"; ?>
    </div>
  <?php } else { // LAYOUTS USING TABLES (LISTS) ?> 
    <tr><td><?php print get_custom_field('nimi'); ?></td><td><?php print get_custom_field('vuosi'); ?></td></tr>
  <?php }
}
if (in_array($layout,$layoutsWithTables)) {
  ?>
  </table>
  <?php
}
if (!$results || count($results) == 0) {
  print "(no results)<br />\n";
}*/
?>
<?php get_sidebar(); ?>
<?php get_footer(); ?>