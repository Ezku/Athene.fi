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
foreach($results as $entry):
  $post = get_post_complete($entry['ID']);
  ?>
  <div class="toimija">
  <img src="<?php print get_custom_field('kuva'); ?>" alt="" style="width: 100px;" /><br />
  <?php print get_custom_field('nimi'); ?><br />
  <?php if (custom_field_found('virka')) print get_custom_field('virka')."<br />"; ?>
  <?php if (custom_field_found('puhelin') && get_custom_field('puhelin') != "") print get_custom_field('puhelin')."<br />"; ?>
  <?php if (custom_field_found('email')) print get_custom_field('email')."<br />"; ?>
  </div>
<?php
endforeach;
if (count($results) == 0): ?>
    <p>Ei toimihenkilöitä tälle vuodelle.</p>
<?php endif;
?>