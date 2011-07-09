<style>
	span.summarize-posts-link {
		padding: 10px;
		margin-left: 5px;
	}
	img.summarize-posts-img {
		vertical-align: middle;
		margin-right: 10px;
	}
</style>
<div class="wrap">
	<?php // screen_icon(); 
	$my_dir = WP_PLUGIN_URL.'/'. basename(dirname(dirname(__FILE__)));
	?>

	<h2><img src="<?php print $my_dir; ?>/images/summarize-posts-logo.jpg" alt="summarize-posts-logo" width="120" height="55" /> Summarize Posts Administration</h2>
	
	<?php print $msg; ?>

	<form action="" method="post" id="summarize_posts_form">
		<h3>group_concat_max_len</h3>
		<input type="text" id="group_concat_max_len" name="group_concat_max_len" value="<?php print self::$options['group_concat_max_len']; ?>" /> <label for="group_concat_max_len"><strong>group_concat_max_len</strong> <em>(number of characters, 4096 recommended)</em></label>


		<p>
		<em>group_concat_max_len</em> is a MySQL setting that affects how custom fields are retrieved by this Plugin. This Plugin will attempt to adjust this setting automatically; an error should be visible if there were any problems.<br/>
		</p>

		<h3>Output Type</h3>
		
		<select name="output_type">
			<option value="<?php print OBJECT; ?>" <?php print $object_selected; ?>>OBJECT</option>
			<option value="<?php print ARRAY_A; ?>" <?php print $array_a_selected; ?>>ARRAY_A</option>
		</select> <label for=""><strong>Output Type</strong></label>
		
		<p>The output type determines how results are returned inside of your theme files via the <code>$wpdb->get_results()</code> function.</p>
		
		<ul>
		
			<li><strong>OBJECT</strong> returns results whose attributes must be accessed via arrow notation, e.g. <code>$r->post_title</code>.</li>
			<li><strong>ARRAY_A</strong> returns results whose attributes must be accessed via array notation, e.g. <code>$r['post_title']</code>.</li>
		</ul>
		
		<p>You can override this setting per query using the <code>set_output_type</code> method, e.g. 
		<pre>
		$Q = new GetPostsQuery();
		$Q->set_output_type(OBJECT);
		$results = $Q->get_posts();
		foreach ($results as $r) {
			print $r->post_title;
		}
		</pre>
		</p>

		
		<p class="submit"><input type="submit" name="submit" value="Update" />
		<?php wp_nonce_field('summarize_posts_options_update','summarize_posts_admin_nonce'); ?>
		
	</form>
	
	<p>
		<span class="summarize-posts-link"><img class="summarize-posts-img" src="<?php print $my_dir; ?>/images/heart.png" height="32" width="34" alt="bug"/><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=U9GH7AW26UD2N">Support this Plugin</a></span>
		<span class="summarize-posts-link"><img class="summarize-posts-img" src="<?php print $my_dir; ?>/images/potion.png" height="31" width="22" alt="bug"/><a href="http://code.google.com/p/wordpress-summarize-posts/">Documentation</a></span>
		<span class="summarize-posts-link"><img class="summarize-posts-img" src="<?php print $my_dir; ?>/images/space-invader.png" height="32" width="32" alt="bug"/> <a href="http://code.google.com/p/wordpress-summarize-posts/issues/list">Report a Bug</a></span>
	</p>
</div>
