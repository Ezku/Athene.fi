<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<ul <?php echo $class ?> <?php global $wp_version; if ($wp_version < 2.2) echo 'style="margin-top: -1.3em;"' ?>>
  <li><a <?php if ($sub == '') echo 'class="current"'; ?>href="<?php echo $url ?>"><?php _e ('Search Index', 'search-unleashed') ?></a><?php echo $trail; ?></li>
  <li><a <?php if ($sub == 'modules') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=modules"><?php _e ('Modules', 'search-unleashed') ?></a><?php echo $trail; ?></li>
  <li><a <?php if ($sub == 'options') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=options"><?php _e ('Options', 'search-unleashed') ?></a><?php echo $trail; ?></li>
  <li><a <?php if ($sub == 'filters') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=filters"><?php _e ('Filters', 'search-unleashed') ?></a><?php echo $trail; ?></li>
  <li><a <?php if ($sub == 'log') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=log"><?php _e ('Log', 'search-unleashed') ?></a><?php echo $trail; ?></li>
  <li><a <?php if ($sub == 'support') echo 'class="current"'; ?>href="<?php echo $url ?>&amp;sub=support"><?php _e ('Support', 'search-unleashed') ?></a></li>
</ul>