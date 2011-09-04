<div class="widget widget-ilmo">
    <header class="widget-header">
      <h2><a href="<?php echo $ilmomasiina_url; ?>"><?php echo $title; ?></a></h2>
    </header>
    <div class="widget-content">
      <ul class="ilmo">
      <?php for($i=0; $i<min($items,count($entries)); $i++) { ?>
        <?php $entry = $entries[$i]; ?>
        <li class="ilmo-entry">
          <p class="title"><a href="<?php echo $entry['url'] ?>"><?php echo $entry['name'] ?></a></p>
          <p class="state">
            <?php echo $entry['state'] ?>
            <?php if (strstr($entry['state'], 'signup-open')) { ?>
              - sulkeutuu <?php echo $this->getTimeFormat($entry['closes'], $timezone); ?>
            <?php } ?>
            <?php if (strstr($entry['state'], 'signup-not-yet-open')) { ?>
              - aukeaa <?php echo $this->getTimeFormat($entry['opens'], $timezone); ?>
            <?php } ?>
          </p>
        </li>
      <?php } ?> 
      </ul>
    </div>
</div>

