<!-- 
Placeholders use for each list item (each item represents a post that the user can select):
====================================================================================
post_id			: id of the post, sent back to WP. THIS is the value that is stored
				when you save a media or reference in a custom field. It represents 
				a database foreign key, i.e. the post_id of the other post.
preview_html	: sent back to WP to give a preview of this selection
select_label	: label of the button/link clicked to "choose" or "select" this item
thumbnail_html	: any html used as a tiny preview of the post
post_title		: from wp_posts
show_hide_label : label of the toggler.
detail_image	: any detailed image when the selection is toggled for more info
details			: more details about this particular post
original_post_url : link view_original_label of the original post
view_original_label : label for the link back to the original post
... plus...
any other column name from wp_posts.
====================================================================================
-->
<div id="media-item-[+post_id+]">

	<div width="400px">
		<span class="button" onclick="javascript:send_back_to_wp('[+post_id+]','[+preview_html+]')">[+select_label+]</span>
		[+thumbnail_html+]		
		<span class="post_selector_title">[+post_title+]</span>
		<span class="post_selector_toggler" onclick="javascript:toggle_image_detail('media-detail-[+post_id+]');">[+show_hide_label+]</span>

	</div>
	
	<div id="media-detail-[+post_id+]" class="media_detail">
		<table class="media-detail">
			<thead class="media-item-info" id="media-head-[+post_id+]">
				<tr valign='top'>
					<td class="A1B1" id="thumbnail-head-[+post_id+]">
						<p>
							[+detail_image+]
						</p>
					</td>
					<td class="media_info">
						<p>[+details+]</p>
						<p><a href='[+original_post_url+]' target="_blank">[+view_original_label+]</a></p>
					</td>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
	
</div>
