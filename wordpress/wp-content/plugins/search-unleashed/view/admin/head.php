<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<?php if (!$this->is_25 ()) : ?>
<script src="<?php echo $this->url () ?>/js/jquery.pack.js" type="text/javascript" charset="utf-8"></script>
<script src="<?php echo $this->url () ?>/js/jquery.form.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
	var wp_search_base = '<?php echo $this->url () ?>/ajax.php';
	
	jQuery.noConflict();  // Saves me the trouble of supporting plugins that insert stuff into pages they shouldn't
</script>
<?php else : ?>
<script type="text/javascript" charset="utf-8">
	var wp_search_base = '<?php echo $this->url () ?>/ajax.php';
</script>
<?php endif; ?>
<script src="<?php echo $this->url () ?>/js/admin.js" type="text/javascript" charset="utf-8"></script>
<link rel="stylesheet" href="<?php echo $this->url () ?>/admin.css" type="text/css" media="screen" title="no title" charset="utf-8"/>
