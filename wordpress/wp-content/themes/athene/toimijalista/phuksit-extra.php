<?php
function is_selected($params, $vuosi, $ryhma) {
    if ($params['vuosi'] == $vuosi && ($ryhma == NULL || $params['ryhma'] == $ryhma)) {
        return 'selected';
    } else {
        return '';
    }
}
?>

<?php $options = get_option('toimijalistat_options'); ?>
<?php // show links to other groups and years ?>
<h3>Phuksiryhmät</h3>
<div class="phuksit-details">
<?php for($i=1;$i<=$options['phuksiryhmat']['groups']; $i++): ?>
    <p><a class="<?php echo is_selected($params, $options['phuksiryhmat']['year'], $i) ?>" href="<?php echo $perma_url ?><?php echo $options['phuksiryhmat']['year'].'/'.$i ?>">Ryhmä <?php echo $i ?></a></p> 
<?php endfor; ?>
<?php if ($options['phuksiryhmat']['firstyear'] > 0): ?>
    <h3>Aiemmat vuodet</h3>
    <?php 
    for($j=$options['phuksiryhmat']['year']-1;$j>=$options['phuksiryhmat']['firstyear']; $j--):?>
        <div class="year <?php echo is_selected($params, $j, NULL) ?>"> 
            <span>
            <?php echo $j ?>
            <?php for($i=1;$i<=$options['phuksiryhmat']['groups']; $i++): ?>
                <a class="<?php echo is_selected($params, $j, $i) ?>" href="<?php echo $perma_url ?><?php echo $j.'/'.$i ?>"><?php echo $i ?></a> 
            <?php endfor; ?>
            </span>
        </div>
    <?php endfor; ?>
<?php endif; ?>
</div>