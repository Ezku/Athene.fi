<div class="panel-display 960-24-5676-5613-leftSb container_24" <?php if (!empty($css_id)) { print "id=\"$css_id\""; } ?>>

  <?php if (!empty($content['alpha'])): ?>
  <div id="panel-alpha" class="panel-panel panel-col-full grid_24">
    <?php print $content['alpha']; ?>
  </div>

  <div class="clear"></div>  
  <?php endif; ?>
  
  <div class="grid_5">
    
    <?php if (!empty($content['leftSb'])): ?>
    <div id="panel-leftSb" class="panel-panel panel-col-first grid_5">
      <?php print $content['leftSb']; ?>
    </div>

    <div class="clear"></div>
    <?php endif; ?>
    
  </div>
	<div class="grid_19">
	
	  <?php if (!empty($content['top'])): ?>
	  <div id="panel-top" class="panel-panel panel-col-last grid_19">
		  <?php print $content['top']; ?>
	  </div>

	  <div class="clear"></div>
	  <?php endif; ?>

	  <?php if (!empty($content['topLeft'])): ?>
    <div id="panel-topLeft" class="panel-panel panel-col-first grid_6">
      <?php print $content['topLeft']; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($content['topCenter'])): ?>
    <div id="panel-topCenter" class="panel-panel panel-col grid_7">
      <?php print $content['topCenter']; ?>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($content['topRight'])): ?>
    <div id="panel-topRight" class="panel-panel panel-col-last grid_6">
      <?php print $content['topRight']; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($content['topLeft']) && !empty($content['topCenter']) && !empty($content['topRight'])): ?>
    <div class="clear"></div>
    <?php endif; ?>

    <?php if (!empty($content['aboveLeft'])): ?>
    <div id="panel-aboveLeft" class="panel-panel panel-col-first grid_6">
      <?php print $content['aboveLeft']; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($content['aboveRight'])): ?>
    <div id="panel-aboveRight" class="panel-panel panel-col-last grid_13">
      <?php print $content['aboveRight']; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($content['aboveLeft']) && !empty($content['aboveRight'])): ?>
    <div class="clear"></div>
    <?php endif; ?>

	  <?php if (!empty($content['middle'])): ?>
	  <div id="panel-middle" class="panel-panel panel-col-last grid_19">
		  <?php print $content['middle']; ?>
	  </div>

	  <div class="clear"></div>
	  <?php endif; ?>

	  <?php if (!empty($content['belowLeft'])): ?>
	  <div id="panel-belowLeft" class="panel-panel panel-col-first grid_13">
		  <?php print $content['belowLeft']; ?>
	  </div>
	  <?php endif; ?>

	  <?php if (!empty($content['belowRight'])): ?>
	  <div id="panel-belowRight" class="panel-panel panel-col-last grid_6">
		  <?php print $content['belowRight']; ?>
	  </div>
	  <?php endif; ?>

	  <?php if (!empty($content['belowLeft']) && !empty($content['belowRight'])): ?>
	  <div class="clear"></div>
	  <?php endif; ?>

    <?php if (!empty($content['bottomLeft'])): ?>
    <div id="panel-bottomLeft" class="panel-panel panel-col-first grid_6">
      <?php print $content['bottomLeft']; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($content['bottomCenter'])): ?>
    <div id="panel-bottomCenter" class="panel-panel panel-col grid_7">
      <?php print $content['bottomCenter']; ?>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($content['bottomRight'])): ?>
    <div id="panel-bottomRight" class="panel-panel panel-col-last grid_6">
      <?php print $content['bottomRight']; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($content['bottomLeft']) && !empty($content['bottomCenter']) && !empty($content['bottomRight'])): ?>
    <div class="clear"></div>
    <?php endif; ?>

	  <?php if (!empty($content['bottom'])): ?>
	  <div id="panel-bottom" class="panel-panel panel-col-last grid_19">
		  <?php print $content['bottom']; ?>
	  </div>
	  
	  <div class="clear"></div>
	  <?php endif; ?>
	  
	</div>
	
	<?php if (!empty($content['omega'])): ?>
  <div id="panel-omega" class="panel-panel panel-col-full grid_24">
    <?php print $content['omega']; ?>
  </div>

  <div class="clear"></div>  
  <?php endif; ?>

</div>