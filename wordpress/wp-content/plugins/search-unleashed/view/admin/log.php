<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
	<?php	$this->render_admin ('annoy'); ?>
	
	<?php screen_icon(); ?>
	
	<h2><?php _e ('Search Unleashed | Log', 'search-unleashed'); ?></h2>
	
	<?php $this->submenu (true); ?>
	<br/>
	<form method="get" action="">
		<input type="hidden" name="page" value="<?php echo $_GET['page'] ?>"/>
		<input type="hidden" name="curpage" value="<?php echo $pager->current_page () ?>"/>
		<input type="hidden" name="sub" value="<?php echo $_GET['sub'] ?>"/>

		<p class="search-box">
			<label for="post-search-input" class="hidden"><?php _e ('Search', 'search-unleashed') ?>:</label>

			<input type="text" class="search-input" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars ($_GET['search']) : ''?>"/>
			<?php if (isset ($_GET['search']) && $_GET['search'] != '') : ?>
				<input type="hidden" name="ss" value="<?php echo htmlspecialchars ($_GET['search']) ?>"/>
			<?php endif;?>

			<input type="submit" class="button" value="<?php _e ('Search', 'search-unleashed'); ?>"/>
		</p>
	</form>
	
	<form method="post" action="">
		<div id="pager" class="tablenav">
			<div class="alignleft actions">
				<select name="action2" id="action2_select">
					<option value="-1" selected="selected"><?php _e('Bulk Actions'); ?></option>
					<option value="delete"><?php _e('Delete'); ?></option>
				</select>
				
				<input type="submit" value="<?php _e('Apply'); ?>" name="doaction2" id="doaction2" class="button-secondary action" />

				<br class="clear" />
			</div>
		
			<div class="tablenav-pages">
				<?php echo $pager->page_links (); ?>
			</div>
		</div>

	<?php if (count ($logs) > 0) : ?>
	<table class="widefat post fixed" style="clear: both">
		<thead>
			<tr>
				<th width="16" id="cb" class="manage-column column-cb check-column">
					<input type="checkbox" />
				</th>
				<th><?php echo $pager->sortable ('phrase', __ ('Phrase', 'search-unleashed')) ?></th>
				<th><?php echo $pager->sortable ('searched_at', __ ('Time', 'search-unleashed')) ?></th>
				<th><?php echo $pager->sortable ('ip', __ ('IP', 'search-unleashed')) ?></th>
				<th><?php echo $pager->sortable ('referrer', __ ('Referrer', 'search-unleashed')) ?></th>
			</tr>
		</thead>

		<tbody>
		<?php foreach ($logs as $pos => $log): ?>
			<tr>
				<td width="16" class="item center">
					<input type="checkbox" class="check" name="checkall[]" value="<?php echo $log->id ?>"/>
				</td>
				<td><?php echo $log->phrase () ?></td>
				<td><?php echo date (get_option ('date_format'), $log->searched_at);  ?></td>
				<td><?php echo $log->ip () ?></td>
				<td><?php echo $log->referrer () ?></td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>
	</form>
	
	<?php else : ?>
		<p><?php _e ('There is nothing to display', 'search-unleashed'); ?></p>
	<?php endif; ?>
</div>

<div class="wrap">
	<h2><?php _e ('Delete Logs', 'search-unleashed'); ?></h2>
	
	<p><?php _e ('This option will delete all search logs.  Please be sure this is what you want to do.', 'search-unleashed'); ?></p>
	
	<form action="<?php echo $this->url ($_SERVER["REQUEST_URI"]) ?>" method="post" accept-charset="utf-8">
		<input class="button-primary" type="submit" name="delete" value="<?php _e ('Delete All Logs', 'search-unleashed') ?>"/>
	</form>
	
	<script type="text/javascript" charset="utf-8">
		jQuery(document).ready(function() { 
			jQuery('#cb').click (function () {
				jQuery('.check').each (function () {
			    this.checked = (this.checked ? '' : 'checked');
			  });
				return true;
			});
		});
	</script>
<br/>
<br/>
</div>