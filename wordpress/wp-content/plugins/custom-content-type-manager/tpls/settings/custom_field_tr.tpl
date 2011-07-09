<!-- 
Ties into built-in WP styling use for displaying plugins
Placeholders are mostly:

    [2] => Array
        (
            [label] => Product Image Thumbnail
            [name] => img_thumb
            [description] => 
            [type] => image
            [default_value] => 
            [sort_param] => 1
        )
 
-->
<tr id="cctm_custom_field_[+name+]" class="active">
	<td><span class="ui-icon ui-icon-arrowthick-2-n-s"></span></td>
	<td>[+icon+]</td>
	<td class="plugin-title">
		<strong>[+label+]</strong> ([+name+])
	</td>
	<td class="column-description desc">
		<div class="plugin-description"><p>[+description+]</p></div>
		<div class="active second plugin-version-author-uri">
			<input name="[+name+][sort_param]" type="hidden" class="store_me" />
			[+edit_field_link+] | [+delete_field_link+]</div>

	</td>
</tr>