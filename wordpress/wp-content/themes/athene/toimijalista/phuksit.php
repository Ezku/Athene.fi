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

$phuksis = $Q->get_posts($args);
// endof phuksis
?>

<?php // show the actual content ?>
<p class="phuksit_group">
    Ryhmä <?php echo $params['ryhma'] ?>
    <?php if ($params['vuosi'] != $options['phuksiryhmat']['year']): ?>
        vuodelta <?php echo $params['vuosi'] ?>
    <?php endif; ?>
</p>
<h2>ISO-henkilöt</h2>
<?php
$count = 0;
$gridClass = cycle('grid_3 alpha', 'grid_3', 'grid_3 omega');
$gridContainerStart = cycle('<div class="clearfix">', '', '');
$gridContainerEnd = cycle('', '', '</div>');
foreach ($isos as $iso) {
  $post = get_post_complete($iso['ID']);
  if (get_custom_field('ryhma') == $params['ryhma']) { 
    $count++; ?>
    <?php echo $gridContainerStart() ?>
    <div class="toimija iso <?php echo $gridClass() ?>">
        <p class="field photo">
            <?php $img = wp_get_attachment_image_src(get_custom_field('kuva')); ?>
            <img src="<?php print $img[0]; ?>" alt="" style="width: 100px;" />
        </p>
        <?php if (custom_field_found('nimi')): ?> 
            <p class="field name">
                <?php print get_custom_field('nimi'); ?>
            </p>
        <?php endif; ?>
        <?php if (custom_field_found('puhelin')): ?> 
            <p class="field phone">
                <?php print get_custom_field('puhelin'); ?>
            </p>
        <?php endif; ?>
        <?php if (custom_field_found('email')): ?> 
            <p class="field email">
                <?php print get_custom_field('email'); ?>
            </p>
        <?php endif; ?>
    </div>
    <?php echo $gridContainerEnd() ?>
  <?php
  }
}
if ($count == 0) {
    ?> 
    <p>Ei isoja tälle ryhmälle</p>
    <?php
}
?>


<h2 style="clear: left;">Phuksit</h2>
<?php
$count = 0;
$gridClass = cycle('grid_3 alpha', 'grid_3', 'grid_3 omega');
$gridContainerStart = cycle('<div class="clearfix">', '', '');
$gridContainerEnd = cycle( '', '', '</div>');
foreach($phuksis as $phuksi) {
  $post = get_post_complete($phuksi['ID']);
  if (get_custom_field('ryhma') == $params['ryhma']) { 
    $count++; ?>
    <?php echo $gridContainerStart() ?>
  <div class="toimija phuksi <?php echo $gridClass() ?>">
      <p class="field photo">
          <?php $img = wp_get_attachment_image_src(get_custom_field('kuva')); ?>
          <img src="<?php print $img[0]; ?>" alt="" style="width: 100px;" />
      </p>
      <?php if (custom_field_found('nimi')): ?> 
          <p class="field name">
              <?php print get_custom_field('nimi'); ?>
          </p>
      <?php endif; ?>
      <?php if (custom_field_found('puhelin')): ?> 
          <p class="field phone">
              <?php print get_custom_field('puhelin'); ?>
          </p>
      <?php endif; ?>
      <?php if (custom_field_found('email')): ?> 
          <p class="field email">
              <?php print get_custom_field('email'); ?>
          </p>
      <?php endif; ?>
  </div>
  <?php echo $gridContainerEnd() ?>
<?php
}
}
if ($count == 0) {
  ?> 
  <p>Ei phukseja tässä ryhmässä</p>
  <?php
}
?>