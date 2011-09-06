<?php
$defaults = array('vuosi' => date('Y'), 'ryhma' => '1');
$params = array();
$params['vuosi'] = $wp_query->query_vars['vuosi'] ? $wp_query->query_vars['vuosi'] : $defaults['vuosi'];
$params['ryhma'] = $wp_query->query_vars['ryhma'] ? $wp_query->query_vars['ryhma'] : $defaults['ryhma'];

$options = get_option('toimijalistat_options');

$args['meta_key'] = 'vuosi';
$args['meta_value'] = $params['vuosi']; 

$results = $Q->get_posts($args);

$isoQ = new GetPostsQuery();
$isoQ->set_output_type(ARRAY_A);
$isoQ->limit = 100;
$isoArgs = array(
  "post_type" => 'ISO',
  "meta_key" => 'vuosi',
  "meta_value" => $params['vuosi']
);
$isos = $isoQ->get_posts($isoArgs);
?>
Phuksiryhmät 
<?php for($i=1;$i<=$options['phuksiryhmat']['groups']; $i++) { ?>
  <a href="<?php echo get_permalink() ?><?php echo $options['phuksiryhmat']['year'].'/'.$i ?>"><?php echo $i ?></a> 
<?php } ?>
<h2>ISO-henkilöt</h2>
<?php
$count = 0;
foreach ($isos as $iso) {
  $post = get_post_complete($iso['ID']);
  if (get_custom_field('ryhma') == $params['ryhma']) { 
    $count++; ?>
  <div style="float: left;">
  <img src="<?php print get_custom_field('kuva'); ?>" alt="" style="width: 100px;" /><br />
  <?php print get_custom_field('nimi'); ?><br />
  <?php if (custom_field_found('email')) print get_custom_field('email')."<br />"; ?>
  <?php if (custom_field_found('puhelin')) print get_custom_field('puhelin')."<br />"; ?>
  </div>
  <?php
  }
  if ($count == 0) {
    ?> 
    <p>Ei isoja tälle ryhmälle</p>
    <?php
  }
}
?>
<h2 style="clear: left;">Phuksit</h2>
<?php
$count = 0;
foreach($results as $entry) {
  $post = get_post_complete($entry['ID']);
  if (get_custom_field('ryhma') == $params['ryhma']) { 
    $count++; ?>
  <div style="float: left;">
  <img src="<?php print get_custom_field('kuva'); ?>" alt="" style="width: 100px;" /><br />
  <?php print get_custom_field('nimi'); ?><br />
  <?php if (custom_field_found('email')) print get_custom_field('email')."<br />"; ?>
  <?php if (custom_field_found('puhelin')) print get_custom_field('puhelin')."<br />"; ?>
  </div>
<?php
}
}
if ($count == 0) {
  ?> 
  <p>Ei phukseja tässä ryhmässä</p>
  <?php
}
?>