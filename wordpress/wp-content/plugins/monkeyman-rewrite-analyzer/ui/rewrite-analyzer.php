<div class="wrap">
	<h2><?php _e( 'Rewrite analyzer', $gettext_domain ); ?></h2>

	<?php if ( ! $rewrite_rules ) : ?>
		<div class="error"><p><?php printf( __( 'Pretty permalinks are disabled, you can change this on <a href="%s">the Permalinks settings page</a>.', 
$gettext_domain ), admin_url( 'options-permalink.php' ) ); ?></p></div>
	<?php else : ?>

	<form>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><label for="monkeyman-regex-tester"><?php _e( 'Test URL: ', $gettext_domain ); ?></label></th>
					<td><code><?php echo $url_prefix; ?></code><input id="monkeyman-regex-tester" type="text" class="regular-text code" /><input type="button" id="monkeyman-regex-tester-clear" value="<?php esc_attr_e( 'Clear', $gettext_domain ); ?>" /></td>
				</tr>
			</tbody>
		</table>
	</form>
	
	<table class="widefat fixed" cellspacing="0">
		<thead>
			<tr>
				<th><?php _e( 'Pattern', $gettext_domain ); ?></th>
				<th><?php _e( 'Substitution', $gettext_domain ); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th><?php _e( 'Pattern', $gettext_domain ); ?></th>
				<th><?php _e( 'Substitution', $gettext_domain ); ?></th>
			</tr>
		</tfoot>
		
		<tbody>
		<?php foreach ( $rewrite_rules_ui as $idx => $rewrite_rule_ui ) : ?>
			<tr id="rewrite-rule-<?php echo $idx; ?>" class="rewrite-rule-line">
				<?php if ( array_key_exists( 'error', $rewrite_rule_ui ) ) : ?>
					<td colspan="2">
						<code><?php echo $rewrite_rule_ui['pattern']; ?></code>
						<p class="error"><?php printf( __( 'Error parsing regex: %s', $gettext_domain ), $rewrite_rule_ui['error'] ) ?></p>
					</td>
				<?php else : ?>
					<td><code><?php echo $rewrite_rule_ui['print']; ?></code></td>
					<td>
						<pre><?php foreach ( $rewrite_rule_ui['substitution_parts'] as $substitution_part_ui ) {
								if ( $substitution_part_ui['is_public'] ) {
									echo '<span class="queryvar-public">';
								} else {
									echo '<span class="queryvar-unread" title="' . esc_attr( __( 'This query variable is not public and will not be saved', $gettext_domain ) ) . '">';
								}
								printf( "%' 15s: <span class='queryvalue'>%s</span>\n", $substitution_part_ui['query_var'], $substitution_part_ui['query_value_ui'] );
								echo '</span>';
							} ?></pre>
					</td>
				<?php endif; ?>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	
	<?php endif; ?>
	
	<p><?php printf( __( 'Can\'t get your rewrite rules like you want them? Ask for help on <a href="%s">the WordPress Stack Exchange</a>!', $gettext_domain ), 'http://wordpress.stackexchange.com' ); ?></p>
</div>