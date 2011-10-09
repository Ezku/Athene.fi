<?php $options = get_option('toimijalistat_options'); ?>
<h3>Aiemmat vuodet</h3>
<?php if ($options['toimijat']['firstyear'] > 0) { ?>
    <div>
    <?php for($i=$options['toimijat']['year']; $i>=$options['toimijat']['firstyear']; $i--): ?>
        <?php
            if ($args['meta_value'] == $i) {
                $class = 'selected';
            } else {
                $class = '';
            }
        ?>
        <p><a class="<?php echo $class ?>" href="<?php echo $perma_url ?><?php echo $i ?>"><?php echo $i ?></a></p>
    <?php endfor; ?>
    </div>
<?php } ?>