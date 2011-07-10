<?php
$args['meta_key'] = 'vuosi';
$args['meta_value'] = $wp_query->query_vars['vuosi'];
$results = $Q->get_posts($args);

$isoQ = new GetPostsQuery();
$isoQ->set_output_type(ARRAY_A);
$isoQ->limit = 100;
$isoArgs = array(
  "post_type" => 'ISO',
  "meta_key" => 'vuosi',
  "meta_value" => $wp_query->query_vars['vuosi']
);
$isos = $isoQ->get_posts($isoArgs);
?>
<h2>ISO-henkilöt</h2>
<?php
foreach ($isos as $iso) {
  $post = get_post_complete($iso['ID']);
  if (get_custom_field('ryhma') == $wp_query->query_vars['ryhma']) { ?>
  <div style="float: left;">
  <img src="<?php print get_custom_field('kuva'); ?>" alt="" style="width: 100px;" /><br />
  <?php print get_custom_field('nimi'); ?><br />
  <?php if (custom_field_found('email')) print get_custom_field('email')."<br />"; ?>
  <?php if (custom_field_found('puhelin')) print get_custom_field('puhelin')."<br />"; ?>
  </div>
  <?php
}
}
?>
<h2 style="clear: left;">Phuksit</h2>
<?php
foreach($results as $entry) {
  $post = get_post_complete($entry['ID']);
  if (get_custom_field('ryhma') == $wp_query->query_vars['ryhma']) { ?>
  <div style="float: left;">
  <img src="<?php print get_custom_field('kuva'); ?>" alt="" style="width: 100px;" /><br />
  <?php print get_custom_field('nimi'); ?><br />
  <?php if (custom_field_found('email')) print get_custom_field('email')."<br />"; ?>
  <?php if (custom_field_found('puhelin')) print get_custom_field('puhelin')."<br />"; ?>
  </div>
<?php
}
}
?>