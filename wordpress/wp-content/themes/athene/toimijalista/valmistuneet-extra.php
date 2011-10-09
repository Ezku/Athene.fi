<h3>Tilastot</h3>

<table id="alumni-numbers">
<tr><th>Pääaine</th><th>Valmistuneita</th></tr>
<?php $class = cycle("odd", "even"); ?>
<?php foreach($majors as $major => $number): ?>
    <tr class="<?php echo $class(); ?>">
        <td><?php echo $major ?></td><td><?php echo $number ?></td>
    </tr>
<?php endforeach; ?>
</table>