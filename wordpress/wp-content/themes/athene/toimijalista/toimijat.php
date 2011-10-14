<?php
$options = get_option('toimijalistat_options');
$args['meta_key'] = 'vuosi';
$args['meta_value'] = empty($wp_query->query_vars['vuosi']) ? $options['toimijat']['year'] : $wp_query->query_vars['vuosi'];
$args['orderby'] = 'menu_order';
$args['order'] = "ASC";
$results = $Q->get_posts($args);
?>


<?php
$gridClass = cycle('grid_5 alpha', 'grid_5 omega');
$gridContainerStart = cycle('<div class="clearfix">', '');
$gridContainerEnd = cycle('', '</div>');
foreach($results as $entry):
  $post = get_post_complete($entry['ID']);
  echo $gridContainerStart();
  ?>
  <div class="toimija <?php echo $gridClass() ?>">
      <?php $img = wp_get_attachment_image_src(get_custom_field('kuva'), 'medium'); ?>
      <img src="<?php print $img[0]; ?>" alt="" style="width: 100px; float: left" />
      <div class="info" style="margin-left: 110px;">
          <?php if (custom_field_found('virka')): ?> 
              <p class="field title">
                  <?php print get_custom_field('virka'); ?>
              </p>
          <?php endif; ?>
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
  </div>
<?php
    echo $gridContainerEnd();
endforeach;
if (count($results) == 0): ?>
    <p>Ei toimihenkilöitä tälle vuodelle.</p>
<?php endif;
?>