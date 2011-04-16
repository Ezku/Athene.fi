<div class="panel-display 960-16-610 container_16" <?php if (!empty($css_id)) { print "id=\"$css_id\""; } ?>>

  <?php if (!empty($content['top'])): ?>
  <div id="panel-top" class="panel-panel panel-col-full grid_16">
    <?php print $content['top']; ?>
  </div>

  <div class="clear"></div>
  <?php endif; ?>

  <?php if (!empty($content['aboveLeft'])): ?>
  <div id="panel-aboveLeft" class="panel-panel panel-col-first grid_6">
    <?php print $content['aboveLeft']; ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($content['aboveRight'])): ?>
  <div id="panel-aboveRight" class="panel-panel panel-col-last grid_10">
    <?php print $content['aboveRight']; ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($content['aboveLeft']) && !empty($content['aboveRight'])): ?>
  <div class="clear"></div>
  <?php endif; ?>

  <?php if (!empty($content['middle'])): ?>
  <div id="panel-middle" class="panel-panel panel-col-full grid_16">
    <?php print $content['middle']; ?>
  </div>

  <div class="clear"></div>
  <?php endif; ?>

  <?php if (!empty($content['belowLeft'])): ?>
  <div id="panel-belowLeft" class="panel-panel panel-col-first grid_6">
    <?php print $content['belowLeft']; ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($content['belowRight'])): ?>
  <div id="panel-belowRight" class="panel-panel panel-col-last grid_10">
    <?php print $content['belowRight']; ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($content['belowLeft']) && !empty($content['belowRight'])): ?>
  <div class="clear"></div>
  <?php endif; ?>

  <?php if (!empty($content['bottom'])): ?>
  <div id="panel-bottom" class="panel-panel panel-col-full grid_16">
    <?php print $content['bottom']; ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($content['bottom'])): ?>
  <div class="clear"></div>
  <?php endif; ?>

</div>
