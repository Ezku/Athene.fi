<?php $options = get_option('toimijalistat_options'); ?>
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