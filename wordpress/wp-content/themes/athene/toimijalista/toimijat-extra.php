<?php $options = get_option('toimijalistat_options'); ?>
<h3>Aiemmat vuodet</h3>
<?php if ($options['toimijat']['firstyear'] > 0) { ?>
    <div>
    <?php for($i=$options['toimijat']['year']; $i>=$options['toimijat']['firstyear']; $i--): ?>
        <p><a href="<?php echo get_permalink() ?><?php echo $i ?>"><?php echo $i ?></a></p>
    <?php endfor; ?>
    </div>
<?php } ?>