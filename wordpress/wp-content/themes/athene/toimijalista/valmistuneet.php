<?php
$args['orderby'] = 'valmistuminen';
$results = $Q->get_posts($args);

$graduates = array();

$majors = array();

foreach($results as $entry) {
    $post = get_post_complete($entry['ID']);
    $post_details = array(
        'nimi' => get_field('nimi'),
        'paaaine' => get_field('paaaine'),
        'diplomityo' => get_field('diplomityo'),
        'valmistuminen' => get_field('valmistuminen')
    );
    
    $graduates[] = $post_details;
    $majors[$post_details['paaaine']]++;
}

// sort the majors by number of graduates
arsort($majors);

function get_field($name) {
    return custom_field_found($name) ? get_custom_field($name) : NULL;
}

?>

<table>
<tr><th>Pääaine</th><th>Valmistuneita</th></tr>
<?php $class = cycle("odd", "even"); ?>
<?php foreach($majors as $major => $number): ?>
    <tr class="<?php echo $class(); ?>">
        <td><?php echo $major ?></td><td><?php echo $number ?></td>
    </tr>
<?php endforeach; ?>
</table>

<table>
    <tr>
        <th>Nimi</th>
        <th>Pääaine</th>
        <th>Diplomityön aihe</th>
        <th>Päivämäärä</th>
    </tr>
<?php
$class = cycle("odd", "even");
foreach($graduates as $entry) { ?>
  <tr class="<?php echo $class(); ?>">
    <td><?php print $entry['nimi'] ?></td>
    <td><?php print $entry['paaaine'] ?></td>
    <td><?php print $entry['diplomityo'] ?></td>
    <td><?php print date("j.n.Y",strtotime($entry['valmistuminen'])) ?></td>
  </tr>
<?php
}
?>
</table>