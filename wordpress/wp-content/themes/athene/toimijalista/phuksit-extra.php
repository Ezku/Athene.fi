<?php $options = get_option('toimijalistat_options'); ?>
<?php // show links to other groups and years ?>
<h3>Phuksiryhmät</h3>
<?php for($i=1;$i<=$options['phuksiryhmat']['groups']; $i++): ?>
    <?php
        if ($params['vuosi'] == $options['phuksiryhmat']['year'] && $params['ryhma'] == $i) {
            $class = 'selected';
        } else {
            $class = '';
        }
    ?>
    <p><a class="<?php echo $class ?>" href="<?php echo $perma_url ?><?php echo $options['phuksiryhmat']['year'].'/'.$i ?>">Ryhmä <?php echo $i ?></a></p> 
<?php endfor; ?>
<?php if ($options['phuksiryhmat']['firstyear'] > 0): ?>
    <h3>Aiemmat vuodet</h3>
    <?php 
    for($j=$options['phuksiryhmat']['year']-1;$j>=$options['phuksiryhmat']['firstyear']; $j--):?>
        <div class="year"> <?php echo $j ?>
        <?php for($i=1;$i<=$options['phuksiryhmat']['groups']; $i++): ?>
            <a href="<?php echo $perma_url ?><?php echo $j.'/'.$i ?>"><?php echo $i ?></a> 
        <?php endfor; ?>
        </div>
    <?php endfor; ?>
<?php endif; ?>