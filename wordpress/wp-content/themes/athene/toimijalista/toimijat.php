<?php
$options = get_option('toimijalistat_options');
$args['meta_key'] = 'vuosi';
$args['meta_value'] = empty($wp_query->query_vars['vuosi']) ? $options['toimijat']['year'] : $wp_query->query_vars['vuosi'];
$args['orderby'] = 'menu_order';
$args['order'] = "ASC";
$results = $Q->get_posts($args);
?>

<?php if ($options['toimijat']['firstyear'] > 0) { ?>
    <div>
    <?php for($i=$options['toimijat']['year']; $i>=$options['toimijat']['firstyear']; $i--): ?>
        <a href="<?php echo get_permalink() ?><?php echo $i ?>"><?php echo $i ?></a>
    <?php endfor; ?>
    </div>
<?php } ?>


<?php
$gridClass = cycle('grid_6 alpha', 'grid_6 omega');
$gridContainerStart = cycle('<div class="clearfix">', '');
$gridContainerEnd = cycle('', '</div>');
foreach($results as $entry):
  $post = get_post_complete($entry['ID']);
  echo $gridContainerStart();
  ?>
  <div class="toimija <?php echo $gridClass() ?>">
      <img src="<?php print get_custom_field('kuva'); ?>" alt="" style="width: 100px; float: left;" />
      <div class="info" style="margin-left: 110px;">
          <?php print get_custom_field('nimi'); ?><br />
          <?php if (custom_field_found('virka') && get_custom_field('virka') != "")      print get_custom_field('virka')."<br />"; ?>
          <?php if (custom_field_found('puhelin') && get_custom_field('puhelin') != "")  print get_custom_field('puhelin')."<br />"; ?>
          <?php if (custom_field_found('email') && get_custom_field('email') != "")      print get_custom_field('email')."<br />"; ?>
      </div>
  </div>
<?php
    echo $gridContainerEnd();
endforeach;
if (count($results) == 0): ?>
    <p>Ei toimihenkilöitä tälle vuodelle.</p>
<?php endif;
?>