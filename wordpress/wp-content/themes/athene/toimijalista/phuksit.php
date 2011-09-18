<?php

// get query vars
$defaults = array('vuosi' => date('Y'), 'ryhma' => '1');
$params = array();
$params['vuosi'] = $wp_query->query_vars['vuosi'] ? $wp_query->query_vars['vuosi'] : $defaults['vuosi'];
$params['ryhma'] = $wp_query->query_vars['ryhma'] ? $wp_query->query_vars['ryhma'] : $defaults['ryhma'];

// Fetch people in this group

// Fetch ISOs for this group
$isoQ = new GetPostsQuery(); // create a new query obj - the existing one is used for phuksit
$isoQ->set_output_type(ARRAY_A);
$isoQ->limit = 100;
$isoArgs = array(
  "post_type" => 'ISO',
  "meta_key" => 'vuosi',
  "meta_value" => $params['vuosi'],
  'orderby' => 'menu_order',
  'order' => "ASC"
);
$isos = $isoQ->get_posts($isoArgs);
// endof ISOs

// fetch phuksis
$options = get_option('toimijalistat_options');

$args['meta_key'] = 'vuosi';
$args['meta_value'] = $params['vuosi']; 
$args['orderby'] = 'menu_order';
$args['order'] = "ASC";

$results = $Q->get_posts($args);
// endof phuksis
?>
<?php // show links to other groups and years ?>
<h3>Phuksiryhmät</h3>
<?php for($i=1;$i<=$options['phuksiryhmat']['groups']; $i++): ?>
    <a href="<?php echo get_permalink() ?><?php echo $options['phuksiryhmat']['year'].'/'.$i ?>"><?php echo $i ?></a> 
<?php endfor; ?>
<h3>Aiemmat vuodet</h3>
<?php 
if ($options['phuksiryhmat']['firstyear'] > 0):
    for($j=$options['phuksiryhmat']['year']-1;$j>=$options['phuksiryhmat']['firstyear']; $j--):?>
        <div class="year"> <?php echo $j ?>
        <?php for($i=1;$i<=$options['phuksiryhmat']['groups']; $i++): ?>
            <a href="<?php echo get_permalink() ?><?php echo $j.'/'.$i ?>"><?php echo $i ?></a> 
        <?php endfor; ?>
        </div>
    <?php endfor; ?>
<?php endif; ?>

<?php // show the actual content ?>
<h2>ISO-henkilöt</h2>
<?php
$count = 0;
foreach ($isos as $iso) {
  $post = get_post_complete($iso['ID']);
  if (get_custom_field('ryhma') == $params['ryhma']) { 
    $count++; ?>
  <div class="toimija iso">
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
  <div class="toimija phuksi">
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