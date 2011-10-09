<?php
/*------------------------------------------------------------------------------
Used to format a Custom Field type, e.g. "Dropdown" -- display all info about 
the given custom field type.
------------------------------------------------------------------------------*/
?>
<tr>
	<td>
		<img src="<?php print $data['icon']; ?>" height="48" width="48" alt="Icon for <?php print $data['name']; ?> Fields" />
	</td>
	<td>
		<h4 class="cctm_field_type_header"><a href="?page=cctm_fields&a=create_custom_field&type=<?php print $data['type']; ?>" class="button"><?php _e('Create',CCTM_TXTDOMAIN); ?></a> <?php print $data['name']; ?></h4>
		<p><?php print htmlentities($data['description']); ?> (<a href="<?php print $data['url']; ?>" target="_new"><?php _e('More Info', CCTM_TXTDOMAIN); ?></a>)
		</p>
	</td>
</tr>
