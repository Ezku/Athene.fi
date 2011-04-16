<div class="panel-display 960-12-singularity container_12" <?php if (!empty($css_id)) { print "id=\"$css_id\""; } ?>>

  <?php if (!empty($content['single'])): ?>
  <div id="region-single" class="panel-region panel-col-full grid_12">
    <?php print $content['single']; ?>
  </div>

  <div class="clear"></div>
  <?php endif; ?>

</div>
