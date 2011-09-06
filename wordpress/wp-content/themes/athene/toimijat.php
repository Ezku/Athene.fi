<?php
$options = get_option('toimijalistat_options');
$args['meta_key'] = 'vuosi';
$args['meta_value'] = empty($wp_query->query_vars['vuosi']) ? $options['toimijat']['year'] : $wp_query->query_vars['vuosi'];
$results = $Q->get_posts($args);
?>

<?php
foreach($results as $entry) {
  $post = get_post_complete($entry['ID']);
  ?>
  <div style="float: left; width: 230px; text-align: center; margin: 25px 0;">
  <img src="<?php print get_custom_field('kuva'); ?>" alt="" style="width: 100px;" /><br />
  <?php print get_custom_field('nimi'); ?><br />
  <?php if (custom_field_found('virka')) print get_custom_field('virka')."<br />"; ?>
  <?php if (custom_field_found('puhelin')) print get_custom_field('puhelin')."<br />"; ?>
  <?php if (custom_field_found('email')) print get_custom_field('email')."<br />"; ?>
  </div>
<?php
}
?>