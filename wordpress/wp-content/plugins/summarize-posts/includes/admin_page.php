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

	<h2><img src="<?php print $my_dir; ?>/images/summarize-posts-logo.jpg" alt="summarize-posts-logo" width="104" height="56" /> Summarize Posts <?php _e('Administration', SummarizePosts::txtdomain); ?></h2>
	
	<?php print $msg; ?>

	<form action="" method="post" id="summarize_posts_form">
		<h3>group_concat_max_len</h3>
		<input type="text" id="group_concat_max_len" name="group_concat_max_len" value="<?php print self::$options['group_concat_max_len']; ?>" /> <label for="group_concat_max_len"><strong>group_concat_max_len</strong> <em>(<?php _e('number of characters, 4096 recommended', SummarizePosts::txtdomain); ?>)</em></label>


		<p>
		<em>group_concat_max_len</em> <?php _e('A MySQL setting that affects how custom fields are retrieved by this Plugin. This Plugin will attempt to adjust this setting automatically; an error should be visible if there were any problems.', SummarizePosts::txtdomain); ?><br/>
		</p>

		<h3><?php _e('Output Type', SummarizePosts::txtdomain); ?></h3>
		
		<select name="output_type">
			<option value="<?php print OBJECT; ?>" <?php print $object_selected; ?>>OBJECT</option>
			<option value="<?php print ARRAY_A; ?>" <?php print $array_a_selected; ?>>ARRAY_A</option>
		</select> <label for=""><strong><?php _e('Output Type', SummarizePosts::txtdomain); ?></strong></label>
		
		<p><?php _e('The output type determines how results are returned inside of your theme files via the this plugin\'s retrieval functions.', SummarizePosts::txtdomain); ?></p>
		
		<ul>
		
			<li><strong>OBJECT</strong> <?php _e('returns results whose attributes must be accessed via arrow notation, e.g. <code>$r->post_title</code>.', SummarizePosts::txtdomain); ?></li>
			<li><strong>ARRAY_A</strong> <?php _e('returns results whose attributes must be accessed via array notation, e.g. <code>$r["post_title"]</code>.', SummarizePosts::txtdomain); ?></li>
		</ul>
		
		<p><?php _e('You can override this setting per query using the <code>set_output_type</code> method, for example:', SummarizePosts::txtdomain); ?> 
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
		<span class="summarize-posts-link"><img class="summarize-posts-img" src="<?php print $my_dir; ?>/images/heart.png" height="32" width="32" alt="bug"/>
			<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=UUT7SYGJYELDC" target="_blank"><?php _e('Support this Plugin', SummarizePosts::txtdomain); ?></a>
		</span>
		<span class="summarize-posts-link"><img class="summarize-posts-img" src="<?php print $my_dir; ?>/images/help.png" height="32" width="32" alt="help"/>
			<a href="http://code.google.com/p/wordpress-summarize-posts/" target="_blank"><?php _e('Documentation', SummarizePosts::txtdomain); ?></a>
		</span>
		<span class="summarize-posts-link"><img class="summarize-posts-img" src="<?php print $my_dir; ?>/images/space-invader.png" height="32" width="32" alt="bug"/> 
			<a href="http://code.google.com/p/wordpress-summarize-posts/issues/list" target="_blank"><?php _e('Report a Bug', SummarizePosts::txtdomain); ?></a>
		</span>
		<span class="summarize-posts-link"><img class="summarize-posts-img" src="<?php print $my_dir; ?>/images/forum.png" height="32" width="32" alt="forum"/> <a href="http://wordpress.org/tags/summarize-posts?forum_id=10" target="_blank"><?php _e('Forum', SummarizePosts::txtdomain); ?></a></span>
		
	</p>
</div>
