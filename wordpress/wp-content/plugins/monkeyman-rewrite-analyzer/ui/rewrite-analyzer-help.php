<p><?php printf( __( 'This page displays the currently active rewrite patterns and their substitutions. You can refresh them at <a href="%s">the Permalinks settings page</a>.', $gettext_domain ),
	admin_url( 'options-permalink.php' ) ); ?></p>

<p><?php printf( __( 'On the left side you see the regular expression patterns to which the incoming URL will be matched. The first match in the list "wins". On the left side you see the query variables that this match will set. By hovering over a captured URL part you see the corresponding variable highlighted, and vice-versa. Query variables that are <a href="%s">not defined as public</a> (and will not be passed on to the query object) are <tt class="queryvar-unread">striked out</tt>.', $gettext_domain ),
	'http://adambrown.info/p/wp_hooks/hook/query_vars' ); ?></p>

<p><?php _e( 'You can test a URL by typing it in the "Test URL" textbox. Only matching rules will be displayed, with the corresponding query vars filled in. Clear the box to display all rules again.', $gettext_domain ); ?></p>

<p><a href='#regex-help-wrap' id='regex-help-link'><?php _e( 'Click to show a quick syntax reference for the most common elements', $gettext_domain ); ?></a></p>

<div id='regex-help-wrap' class='hidden'>
	<p><?php _e( 'The regular expressions follow <a href="http://php.net/pcre">the PCRE syntax</a>. A <code>^</code> is appended in front to match the start of the string, and the full regex is wrapped in <code>#</code>.', $gettext_domain ); ?></p>

	<dl>
		<dt><?php _e( '<code>()</code>', $gettext_domain ); ?></dt>
		<dd><?php _e( 'A <em>subpattern</em>. Everything between the parentheses will be saved and can be used later in the substituted URL.', $gettext_domain ); ?></dd>
		
		<dt><?php _e( '<code>|</code>', $gettext_domain ); ?></dt>
		<dd><?php _e( 'A vertical bar indicates <em>alternative</em> patterns. For example, <code>(feed|rdf|rss)</code> matches either <code>feed</code>, <code>rdf</code> or <code>rss</code>.', $gettext_domain ); ?></dd>
		
		<dt><?php _e( '<code>[]</code>', $gettext_domain ); ?></dt>
		<dd><?php _e( 'A <em>character class</em>. Any character specified in this class may appear. If the first character is a <code>^</code>, the class is inverted: none of the specified characters may appear. The characters are sometimes specified as <em>ranges</em>: for example, <code>[0-9]</code> matches any digit, <code>[a-z]</code> any lowercase character. You can combine them: <code>[0-9a-zA-Z]</code> matches any digit or character. A common pattern is <code>[^/]</code>: anything that is not a forward slash.', $gettext_domain ); ?></dd>
		
		<dt><?php _e( '<code>{n,m}</code>', $gettext_domain ); ?></dt>
		<dd><?php _e( 'A <em>repetition</em> of the previous item (character, character class, subpattern, ...). The numbers indicate the minimum and maximum number of occurrences. If you only specify one number (without a comma) it indicates an exact number of occurrences. If you specify one number before the comma it indicates the minimum number and no maximum. To help you understand the target of the repetition, it will be highlighted when you hover it. Adding a <code>?</code> after a repetition makes it <em>non-greedy</em>, meaning it will match the shortest possible string that still makes the whole pattern match.', $gettext_domain ); ?></dd>
		
		<dt><?php _e( '<code>*</code>, <code>+</code> and <code>?</code>', $gettext_domain ); ?></dt>
		<dd><?php _e( 'A shorthand for <code>{0,}</code> (zero or more), <code>{1,}</code> (one or more) and <code>{0,1}</code> (zero or one), respectively.', $gettext_domain ); ?></dd>
		
		<dt><?php _e( '<code>.</code>', $gettext_domain ); ?></dt>
		<dd><?php _e( 'A metacharacter that matches <em>any character</em>.', $gettext_domain ); ?></dd>
	</dl>
</div>