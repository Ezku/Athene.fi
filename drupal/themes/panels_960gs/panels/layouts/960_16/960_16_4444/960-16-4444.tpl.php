<div class="panel-display 960-16-4444 container_16" <?php if (!empty($css_id)) { print "id=\"$css_id\""; } ?>>

  <?php if (!empty($content['top'])): ?>
  <div id="panel-top" class="panel-panel panel-col-full grid_16">
    <?php print $content['top']; ?>
  </div>

  <div class="clear"></div>
  <?php endif; ?>

  <?php if (!empty($content['aboveLeft'])): ?>
  <div id="panel-aboveLeft" class="panel-panel panel-col-first grid_4">
    <?php print $content['aboveLeft']; ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($content['aboveLeftCenter'])): ?>
  <div id="panel-aboveLeftCenter" class="panel-panel panel-col grid_4">
    <?php print $content['aboveLeftCenter']; ?>
  </div>
  <?php endif; ?>
  
  <?php if (!empty($content['aboveRightCenter'])): ?>
  <div id="panel-aboveRightCenter" class="panel-panel panel-col grid_4">
    <?php print $content['aboveRightCenter']; ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($content['aboveRight'])): ?>
  <div id="panel-aboveRight" class="panel-panel panel-col-last grid_4">
    <?php print $content['aboveRight']; ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($content['aboveLeft']) && !empty($content['aboveLeftCenter']) && !empty($content['aboveRightCenter']) && !empty($content['aboveRight'])): ?>
  <div class="clear"></div>
  <?php endif; ?>

  <?php if (!empty($content['middle'])): ?>
  <div id="panel-middle" class="panel-panel panel-col-full grid_16">
    <?php print $content['middle']; ?>
  </div>

  <div class="clear"></div>
  <?php endif; ?>

  <?php if (!empty($content['belowLeft'])): ?>
  <div id="panel-belowLeft" class="panel-panel panel-col-first grid_4">
    <?php print $content['belowLeft']; ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($content['belowLeftCenter'])): ?>
  <div id="panel-belowLeftCenter" class="panel-panel panel-col grid_4">
    <?php print $content['belowLeftCenter']; ?>
  </div>
  <?php endif; ?>
  
  <?php if (!empty($content['belowRightCenter'])): ?>
  <div id="panel-belowRightCenter" class="panel-panel panel-col grid_4">
    <?php print $content['belowRightCenter']; ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($content['belowRight'])): ?>
  <div id="panel-belowRight" class="panel-panel panel-col-last grid_4">
    <?php print $content['belowRight']; ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($content['belowLeft']) && !empty($content['belowLeftCenter']) && !empty($content['belowRightCenter']) && !empty($content['belowRight'])): ?>
  <div class="clear"></div>
  <?php endif; ?>

  <?php if (!empty($content['bottom'])): ?>
  <div id="panel-bottom" class="panel-panel panel-col-full grid_16">
    <?php print $content['bottom']; ?>
  </div>
  
  <div class="clear"></div>
  <?php endif; ?>

</div>
