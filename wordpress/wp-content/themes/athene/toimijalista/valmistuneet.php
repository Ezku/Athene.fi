<?php
$args['orderby'] = 'valmistuminen';
$results = $Q->get_posts($args);
?>

<table>
<?php
foreach($results as $entry) {
  $post = get_post_complete($entry['ID']);
  ?>
  <tr>
    <td><?php print get_custom_field('nimi'); ?></td>
    <td><?php if (custom_field_found('paaaine')) print get_custom_field('paaaine'); ?></td>
    <td><?php if (custom_field_found('diplomityo')) print get_custom_field('diplomityo'); ?></td>
    <td><?php if (custom_field_found('valmistuminen')) print date("j.n.Y",strtotime(get_custom_field('valmistuminen'))); ?></td>
  </tr>
<?php
}
?>
</table>